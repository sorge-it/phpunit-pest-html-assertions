# The animated demo in the README: the total on the cart page is empty. A
# string check stays green, PHPStan reports it, and the check by selector
# fails and shows the region.
# Run through `make -C demo gif`; https://github.com/charmbracelet/vhs

Output demo/output/demo.gif

Set Shell bash
Set Width 1280
Set Height 940
Set FontSize 16
Set Theme "Catppuccin Mocha"
Set Padding 24
Set Framerate 24
Set TypingSpeed 20ms
Set CursorBlink false

# A first run fills the cache of PHPStan, so the recorded run shows its result at once.
Hide
Type "cd demo/app && export PS1='$ ' && vendor/bin/phpstan analyse --no-progress > /dev/null; clear"
Enter
Wait+Line@120s /^\$$/
Show

Sleep 800ms
Type "cat tests/StringTest.php"
Enter
Sleep 1.5s
Type "vendor/bin/pest tests/StringTest.php"
Enter
Wait+Screen@60s /Duration/
Sleep 3s
Type "grep data-total cart.php"
Enter
Sleep 1.5s
Type "vendor/bin/phpstan analyse --no-progress"
Enter
Wait+Screen@60s /Found 1 error/
Sleep 5s

Type "clear"
Enter
Type "cat tests/CartTest.php"
Enter
Sleep 1.5s
Type "vendor/bin/pest tests/CartTest.php"
Enter
Wait+Screen@60s /CartTest.php:8/
Sleep 8s
