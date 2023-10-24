Version 1.0.0 of the Moodle driprelease tool Oct 2023

Added course group option to create restriction sets to limit access both
between dates and by course group membership.

Changed date incrementation so it starts from the first selected checkbox, rather than the first item from the list of activities.

Version 1.0.1 of the Moodle driprelease tool Oct 2023
Fixed errors in the form code that showed when Debug was set higher than Normal.
Removed redundant variables (purely for code clarity)
Clarified some of the help strings in the form, which had references to when sessions
were measured in weeks not days. Setting in days means it can deal with a "one quiz a day" scenario, which is bound to be required at some point.
