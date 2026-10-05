<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

use DOMElement;
use DOMNode;
use Illuminate\Testing\TestComponent;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\TestView;
use InvalidArgumentException;
use Livewire\Features\SupportTesting\Testable;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorCount;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A page, or one region of it, with the path of regions that led there. A
 * selector matches as `querySelectorAll()` does: against the whole document,
 * and only nodes inside the region count, never the region itself.
 */
final readonly class Html
{
    /**
     * @param  list<string>  $path
     * @param  DOMElement|null  $scope  the region, null for the whole document
     */
    private function __construct(private Crawler $document, private ?DOMElement $scope, public array $path) {}

    /**
     * What `dump()` and `dd()` of Pest show.
     *
     * @return array{path: string, html: string}
     */
    public function __debugInfo(): array
    {
        return ['path' => $this->path(), 'html' => $this->excerpt()];
    }

    /**
     * A string of HTML, a Crawler, a Laravel TestResponse, TestView or
     * TestComponent, a Livewire Testable, a PSR-7 response or a response of
     * Symfony. The kinds are told apart by class, so none of their frameworks
     * is a dependency of this package.
     */
    public static function of(mixed $value): self
    {
        return match (true) {
            $value instanceof self => $value,
            $value instanceof Crawler => new self($value, null, []),
            is_string($value) => self::parse($value),
            $value instanceof TestResponse => self::parse(self::content($value)),
            $value instanceof TestView, $value instanceof TestComponent => self::parse((string) $value),
            $value instanceof Testable => self::parse($value->html()),
            $value instanceof ResponseInterface => self::parse((string) $value->getBody()),
            $value instanceof Response => self::parse((string) $value->getContent()),
            default => Assert::fail(sprintf(
                'A check of HTML reads a string, an Html, a Crawler, a TestResponse, a TestView, a TestComponent, a Livewire Testable, a PSR-7 response or a response of Symfony, not %s.',
                get_debug_type($value),
            )),
        };
    }

    /** The one node the selector finds, as a region of its own: zero or two are a failure, not an empty region. */
    public function within(string $selector): self
    {
        Assert::assertThat($this, new HasSelectorCount($selector, 1));

        return new self($this->document, $this->elements($selector)[0], [...$this->path, $selector]);
    }

    /** The document in the `srcdoc` of the one frame the selector finds. */
    public function frame(string $selector): self
    {
        $frame = $this->within($selector);
        $document = $frame->scope?->getAttribute('srcdoc');

        Assert::assertNotEmpty($document, sprintf('The frame %s has no srcdoc.', $frame->path()));

        return new self(new Crawler($document), null, [...$frame->path, 'srcdoc']);
    }

    /**
     * Each node the selector finds, as a region of its own.
     *
     * @return list<self>
     */
    public function matches(string $selector): array
    {
        $elements = $this->elements($selector);

        return array_map(
            fn (DOMElement $node, int $index): self => new self($this->document, $node, [...$this->path, sprintf('%s:nth-match(%d)', $selector, $index + 1)]),
            $elements,
            array_keys($elements),
        );
    }

    public function count(string $selector): int
    {
        return count($this->elements($selector));
    }

    /**
     * The text of each node, white space collapsed, in the order of the page.
     *
     * @return list<string>
     */
    public function texts(string $selector): array
    {
        return array_map(Text::of(...), $this->elements($selector));
    }

    /**
     * The text of each node as the page holds it, line breaks included.
     *
     * @return list<string>
     */
    public function rawTexts(string $selector): array
    {
        return array_map(Text::raw(...), $this->elements($selector));
    }

    /**
     * The value of one attribute of each node, null where a node lacks it.
     *
     * @return list<string|null>
     */
    public function attributes(string $selector, string $name): array
    {
        return array_map(
            fn (DOMElement $node): ?string => $node->hasAttribute($name) ? $node->getAttribute($name) : null,
            $this->elements($selector),
        );
    }

    /**
     * Every attribute value in the region, the region's own included: for a
     * text that must stand in no attribute, whatever its name.
     *
     * @return list<string>
     */
    public function attributeValues(): array
    {
        $values = [];

        foreach ($this->roots() as $root) {
            foreach ([$root, ...$this->elementsBelow($root)] as $element) {
                foreach ($element->attributes ?? [] as $attribute) {
                    $values[] = $attribute->value;
                }
            }
        }

        return $values;
    }

    /** The text of the whole region, white space collapsed, scripts and styles left out. */
    public function text(): string
    {
        return implode(' ', array_map(Text::of(...), $this->roots())) |> trim(...);
    }

    /**
     * The elements the selector finds inside the region, in the order of the page.
     * `:scope` alone is the element of the region: Symfony translates `:scope` by
     * its position, so `:scope.x` would also find a first child with the class.
     *
     * @return list<DOMElement>
     */
    public function elements(string $selector): array
    {
        if (str_contains($selector, ':scope')) {
            if ($selector !== ':scope' || ! $this->scope instanceof DOMElement) {
                throw new InvalidArgumentException(sprintf('%s: `:scope` stands alone and names the element of a region, not "%s".', $this->path(), $selector));
            }

            return [$this->scope];
        }

        $matches = array_values(array_filter(
            iterator_to_array($this->document->filter($selector), false),
            fn (DOMNode $node): bool => $node instanceof DOMElement,
        ));

        $scope = $this->scope;

        return $scope instanceof DOMElement ? array_values(array_filter($matches, fn (DOMElement $node): bool => $this->isBelow($node, $scope))) : $matches;
    }

    /**
     * The nodes the region stands on: its element, or the document element of a page.
     *
     * @return list<DOMElement>
     */
    public function roots(): array
    {
        if ($this->scope instanceof DOMElement) {
            return [$this->scope];
        }

        return array_values(array_filter(iterator_to_array($this->document, false), fn (DOMNode $node): bool => $node instanceof DOMElement));
    }

    public function path(): string
    {
        return implode(' > ', ['(page)', ...$this->path]);
    }

    /** The region as indented HTML, for a failure message. */
    public function excerpt(): string
    {
        return Excerpt::of($this->roots());
    }

    private static function parse(string $html): self
    {
        return new self(new Crawler($html), null, []);
    }

    /**
     * A streamed response holds its content only once it ran.
     *
     * @param  TestResponse<Response>  $response
     */
    private static function content(TestResponse $response): string
    {
        return $response->baseResponse instanceof StreamedResponse
            ? $response->streamedContent()
            : (string) $response->baseResponse->getContent();
    }

    private function isBelow(DOMNode $node, DOMElement $scope): bool
    {
        for ($parent = $node->parentNode; $parent instanceof DOMNode; $parent = $parent->parentNode) {
            if ($parent->isSameNode($scope)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<DOMElement> */
    private function elementsBelow(DOMElement $root): array
    {
        $below = [];

        foreach ($root->getElementsByTagName('*') as $element) {
            $below[] = $element;
        }

        return $below;
    }
}
