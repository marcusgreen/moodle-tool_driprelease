## Version 1.3.0 of the Moodle driprelease tool May 2025

Ran automated tests to confirm Moodle 5.0 compatibility
Filter out question banks from activities as they do not support setting availability

## Version 1.2.0 of the Moodle driprelease tool Jan 2025

Refactored most lib code into a driprlease class. Added a new unit test and other linting issues.
Thanks to Dan Marsden for reporting this as part of the review for the plugins databsae
https://github.com/marcusgreen/moodle-tool_driprelease/issues/2

## Version 1.1.0 of the Moodle driprelease tool Sept 2024

Added support for any valid course module. This introduces a new
Activity dropdown with refresh button.

## Version 1.0.1 of the Moodle driprelease tool Oct 2023

Fixed errors in the form code that showed when Debug was set higher than Normal.
Removed redundant variables (purely for code clarity)
Clarified some of the help strings in the form, which had references to when sessions
were measured in weeks not days. Setting in days means it can deal with a "one quiz a day" scenario, which is bound to be required at some point.

## Version 1.0.0 of the Moodle driprelease tool Oct 2023

Added course group option to create restriction sets to limit access both
between dates and by course group membership.

Changed date incrementation so it starts from the first selected checkbox, rather than the first item from the list of activities.

