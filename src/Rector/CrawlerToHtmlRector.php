<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use Symfony\Component\DomCrawler\Crawler;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rewrites the three forms of crawler code that have a tool of their own:
 * counting the matches, reading their texts, reading one attribute of each.
 */
final class CrawlerToHtmlRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rewrites crawler code to the tools of Html', [
            new CodeSample(
                'new Crawler($html)->filter(\'li\')->each(fn (Crawler $node): string => $node->text());',
                'Html::of($html)->texts(\'li\');',
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /** @param MethodCall $node */
    public function refactor(Node $node): ?Node
    {
        if (! $node->name instanceof Identifier || ! $node->var instanceof MethodCall || $node->isFirstClassCallable()) {
            return null;
        }

        $filter = $node->var;
        $page = $this->crawledPage($filter);

        if (! $page instanceof Expr || $filter->isFirstClassCallable() || count($filter->getArgs()) !== 1) {
            return null;
        }

        $selector = $filter->getArgs()[0];
        $method = $node->name->toLowerString();

        if ($method === 'count' && $node->getArgs() === []) {
            return $this->html($page, 'count', [$selector]);
        }

        if ($method !== 'each' || count($node->getArgs()) !== 1) {
            return null;
        }

        $read = $this->readOfEach($node->getArgs()[0]->value);

        return match (true) {
            ! $read instanceof MethodCall => null,
            $read->name instanceof Identifier && $read->name->toLowerString() === 'text' && $read->getArgs() === [] => $this->html($page, 'texts', [$selector]),
            $read->name instanceof Identifier && $read->name->toLowerString() === 'attr' && count($read->getArgs()) === 1 && $read->getArgs()[0]->value instanceof String_ => $this->html($page, 'attributes', [$selector, $read->getArgs()[0]]),
            default => null,
        };
    }

    /** The page of `new Crawler($page)->filter($selector)`, if the call has that form and the page is a string. */
    private function crawledPage(MethodCall $filter): ?Expr
    {
        if (! $filter->name instanceof Identifier || $filter->name->toLowerString() !== 'filter') {
            return null;
        }

        $crawler = $filter->var;

        if (! $crawler instanceof New_ || ! $crawler->class instanceof Name || ! $this->isName($crawler->class, Crawler::class)) {
            return null;
        }

        $arguments = $crawler->getArgs();

        return count($arguments) === 1 && $this->getType($arguments[0]->value)->isString()->yes() ? $arguments[0]->value : null;
    }

    /** The call in `fn (Crawler $node): … => $node->call()`, made on the node itself. */
    private function readOfEach(Expr $callback): ?MethodCall
    {
        if (! $callback instanceof ArrowFunction || count($callback->params) !== 1) {
            return null;
        }

        $node = $callback->params[0]->var;
        $read = $callback->expr;

        return $read instanceof MethodCall && $read->var instanceof Variable && $node instanceof Variable && $read->var->name === $node->name
            ? $read
            : null;
    }

    /** @param list<Arg> $arguments */
    private function html(Expr $page, string $method, array $arguments): MethodCall
    {
        return new MethodCall(new StaticCall(new FullyQualified(Html::class), 'of', [new Arg($page)]), $method, $arguments);
    }
}
