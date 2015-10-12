# DMI2PNG
Unpacking software, which unpacks a byond DMI file into its component PNGs and GIFs for animated sprites.

## What this does

This software unpacks a list of provided DMI files into individual images - one for each icon state. 

Non-animated sprites are saved as PNG images,

Animated sprites are saved as animated GIF images as well as PNG images for each frame.

## Instructions

This is made in PHP - arguably not the best language for this purpose. But hey, that's what I made it in. What this means is you'll need a web server to run it. (or a PHP interpreter - guide not provided here) 

These instructions are for Windows, for Linux / Mac instructions search for "Installing Apache on <OS>" and skip to step 5 here).

### First time setup (done only once)
1. Install XAMPP to C:/xampp (installing elsewhere can cause problems - https://www.apachefriends.org/)
2. Start XAMPP control panel
3. Hit "Start" button next to Apache (if it fails to start, shut down Skype, that uses the same ports)
4. Go to C:/xampp/htdocs and create a folder called "dmi2png"
5. Download this project (through git or the "download zip" button on the right of this page)
6. Copy the project contents to the dmi2png folder.
7. Open your browser and go to http://localhost/dmi2png a grey screen webpage open up

### Converting images (done every time)
8. Ensure Apache is started (steps 2 and 3)
9. Put any DMI files you want to unpack into the "in" folder (C:/xampp/htdocs/dmi2png/in)
10. Refresh your browser. By default this does 1000 icons per refresh to prevent problems, so as icons get done, examine whether all converted properly and refresh until you are left with a blank grey page.
11. The result can be found in the "out" folder (C:/xampp/htdocs/dmi2png/out). Completed DMI files get moved from "in" into the "done" folder.

The "out" folder is organized as: 
* out/dmi_file_name/icon_state.png (for non-animated icon states)
* out/dmi_file_name/icon_state.gif (for animated icon states)
* out/dmi_file_name/icon_state/n.png (for animated icon state frames)

Bug: The DMI file names and icon state names are sanitized before being saved. This means that if a dmi file contains both these icon states: "overlay:a" and "overlay$a", they would both get saved as "overlaya", thus potentially overwriting each-other. To get around this, rename problematic icon states before unpacking. 

Please note: files with the same name will overwrite existing ones. so if you run multiple versions of "overlays.dmi" through the script, icon states will overwrite previous ones. To get around this either clear the "out" folder every time you change the content of the "in" folder or ensure all DMI files have unique names.

## Credits
- Repository of this software: https://github.com/balohmatevz/DMI2PNG
- PNGMetadataExtractor class, part of MediaWiki software (https://doc.wikimedia.org/mediawiki-core/master/php/PNGMetadataExtractor_8php_source.html)
- GifCreator, developed by Github user Sybio (https://github.com/Sybio/GifCreator)

## License

Shared under GPL, because it uses a class from MediaWiki software.
See the LICENSE file for details