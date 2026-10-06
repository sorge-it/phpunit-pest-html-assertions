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
Set TypingSpeed 35ms
Set CursorBlink false

Hide
Type "cd demo/app && export PS1='$ ' && clear"
Enter
Show

Sleep 800ms
Type "cat tests/StringTest.php"
Enter
Sleep 2s
Type "vendor/bin/pest tests/StringTest.php"
Enter
Sleep 4s
Type "grep data-total cart.php"
Enter
Sleep 2.5s
Type "vendor/bin/phpstan analyse --no-progress"
Enter
Sleep 6s

Type "clear"
Enter
Type "cat tests/CartTest.php"
Enter
Sleep 2s
Type "vendor/bin/pest tests/CartTest.php"
Enter
Sleep 9s
