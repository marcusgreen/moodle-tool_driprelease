# Moodle Driprelease by Marcus Green #

Make quiz activities available on a timed release basis. For example a set of 5 quizzes can become available, then unavailable one week at a time.

This project was funded by Sojo University Japan and initiated by Rob Hirschel and Chris Tempest.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/admin/tool/driprelease

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

All code available under the GPL License https://www.gnu.org/licenses/gpl-3.0.en.html
