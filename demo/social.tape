# The social preview of the GitHub repository: the name, the claim, a string check that passes and a
# check by selector that fails, 1280 × 640.
# Run through `make -C demo png`; https://github.com/charmbracelet/vhs

Set Shell bash
Set Width 1280
Set Height 640
Set FontSize 20
Set Theme "Catppuccin Mocha"
Set Padding 32
Set CursorBlink false

Hide
Type "cd demo/app && php social.php && sleep 60"
Enter
Sleep 6s
Show
Sleep 500ms

Screenshot demo/output/social.png
