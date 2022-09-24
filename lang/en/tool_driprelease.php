<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     tool_driprelease
 * @category    string
 * @copyright   2022 Marcus Green
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Drip release';
$string['modulenameplural'] = 'Drip release';
$string['pluginname'] = 'Drip release';
$string['name'] = 'Name';
$string['defaultname'] = 'Default name';
$string['defaultname_text'] = 'Default name appearing in form when editing form opens';
$string['timing'] = 'Timing';
$string['nomodulesincourse'] = 'No modules in course';
$string['qcount'] = 'Q Count';
$string['activitiesincourse'] = 'Activities in course';
$string['dripreleaseforcourse'] = 'Driprelease for course';
$string['dripreleasesettings'] = 'Settings';
$string['dripreleasefieldset'] = 'Fieldset';

$string['driprelease_header'] = 'Driprelease help';
$string['driprelease_header_help'] = 'A description of What Driprelease does';

$string['schedulestart'] = 'Start';
$string['schedulestart_help'] = 'The time periods/intervals that activities are available are set here. First at the
"Start:" section, the day, month and year that the activity cycle will start is set. The
blue calendar will open a pop-up calendar to select dates as an option.';
$string['schedulefinish'] = 'Finish';
$string['schedulefinish_help'] = 'The date that all activities/final session will close. The time will remain
this time to finish a session before the next one begins.';
$string['repeatcount'] = 'Repeat count';
$string['repeatcount_text'] = 'Help for repeatcount in settings';
$string['repeat'] = 'Repeat frequency';
$string['repeat_text'] = 'Repeat settings help';
$string['repeatcounterror'] = 'Repeat frequency must be more than zero';
$string['activitiespersessionerror'] = 'Activities per session must be more than zero';
$string['repeat_help'] =
 'Decides how long the intervals are. e.g. a new
 set of activities will be available every 2 weeks and the currently available ones
 will then become unavailable.';
$string['session'] = 'Session';
$string['sessioncount'] = 'Week count';
$string['sessioncount_text'] = 'Week count help description';
$string['weeks'] = 'week(s)';
$string['repeatenable'] = 'Enable';
$string['sessioncount_text'] = 'Session count help for settings';
$string['sessioncount'] = 'Number of sessions';
$string['sessioncount_help'] = 'The total number of sessions/cycles
can be set. Depending on the start and finish dates, the length of these
sessions will be automatically evenly distributed.
E.g. Say the number of weeks between the start date and the finish date was
16 weeks. If a the number of sessions was set at 8, then the period of one
session would be 2 weeks, 16 weeks / 8 sessions. However, if the sessions was
set at 4, then the period would be 4 weeks, 16 weeks/4 sessions. Finally if the
sessions was 16, it would be a cycle every week.';
$string['sessioncountenable'] = 'Enable';
$string['sessionscounterror'] = 'Number of sessions must be more than zero';
$string['activitiespersession'] = 'Activities per session';
$string['activitiespersession_text'] = 'Set the number of activitie that are available in each session, e.g. if a session is one week 5 will give you one per day';
$string['activitiespersession_help'] = 'How many items per session, for example if set to 10 and "Repeat every" is set to 2 (weeks), students would see 5 activities per week';
$string['pluginadministration'] = 'Driprelease administration';
$string['name'] = 'Name';
$string['descriptions'] = 'Description';
$string['questions'] = 'Question';
$string['from'] = 'From';
$string['to'] = 'To';
$string['dateformat'] = 'Date format';
$string['dateformat_text'] = 'Date format explanation xxxxx';
$string['dripreleasename_help'] = 'Content scheduler help information';
$string['privacy:null_reason'] = 'The Dripelease admin tool does not effect or store any data itself.';
