# The social preview of the GitHub repository: the name and one failure, 1280 × 640.
# Run through `make -C demo png`; https://github.com/charmbracelet/vhs

Set Shell bash
Set Width 1280
Set Height 640
Set FontSize 14
Set Theme "Catppuccin Mocha"
Set Padding 28
Set CursorBlink false

Hide
Type "cd demo/app && export PS1='$ ' && php social.php"
Enter
Sleep 6s
Show
Sleep 500ms

Screenshot demo/output/social.png
