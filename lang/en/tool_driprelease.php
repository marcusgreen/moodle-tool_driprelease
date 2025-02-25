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
 * @copyright   2024 Marcus Green
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['activitiespersession'] = 'Activities per session';
$string['activitiespersession_help'] = 'How many items per session, for example if Activities per session is set to 5 and "Session length" is set to 7 (days), students would see 5 activities per week';
$string['activitiespersession_text'] = 'Set the number of activitie that are available in each session, e.g. if a session is one week 5 will give you one per day';
$string['activitiespersessionerror'] = 'Activities per session is {$a->activitiespersession} but the course only has {$a->modulecount} activities';
$string['assignment'] = 'Assignment';
$string['activity'] = 'Activity';
$string['activitytype'] = 'Activity type';
$string['coursegroups'] = 'Course groups';
$string['coursesettingnogroups'] = 'This course is set to no groups';
$string['courshasnogroups'] = 'This course has no groups';
$string['displaydisabled'] = 'Display disabled';
$string['displaydisabled_help'] = 'Unavailable items are displayed but users cannot click into them';
$string['driprelease:view'] = 'View driprelease for the course';
$string['dripreleaseforcourse'] = 'Driprelease for course';
$string['from'] = 'From';
$string['hideunselected'] = 'Hide unselected';
$string['hideunselected_help'] = 'Any item not selected will be hidden from students, including in the gradebook';
$string['hideunselected_text'] = 'Any unselected course modules will be hidden';
$string['nomodulesincourse'] = 'No modules in course';
$string['noselections'] = 'No items selected, no availability updated';
$string['pluginname'] = 'Drip release';
$string['privacy:null_reason'] = 'The Dripelease admin tool does not effect or store any data itself.';
$string['questioncount'] = 'Question count';
$string['refresh'] = 'Refresh';
$string['resetunselected'] = 'Reset unselected';
$string['resetunselected_help'] = 'Clear the availability settings for any unselected items';
$string['schedulefinish'] = 'Finish';
$string['schedulestart'] = 'Start';
$string['schedulestart_help'] = 'The time periods/intervals that activities are available are set here. First at the
"Start:" section, the day, month and year that the activity cycle will start is set. The
blue calendar will open a pop-up calendar to select dates as an option.';
$string['session'] = 'Session';
$string['session_help'] =
 'Decides how long the intervals are. e.g. a new
 set of activities will be available every 7 days and the currently available ones
 will then become unavailable.
can be set. Depending on the start and finish dates, the length of these
sessions will be automatically evenly distributed.
E.g. Say the number of weeks between the start date and the finish date was
16 weeks. If a the number of sessions was set at 8, then the period of one
session would be 2 weeks, 16 weeks / 8 sessions. However, if the sessions was
set at 4, then the period would be 4 weeks, 16 weeks/4 sessions. Finally if the
sessions was 16, it would be a cycle every week.';
$string['sessionlength'] = 'Session length (days)';
$string['sessionlength_text'] = 'Length in days of each session';
$string['sessionlengtherror'] = 'Session length must be more than zero';
$string['sessionlengthislonger'] = 'Session length is longer than Start to Finish, shorten the session or set a later finish';
$string['starttofinishmustbe'] = 'Start to finish must be at least one day';
$string['stayavailable'] = 'Stay available after session finish';
$string['stayavailable_help'] = 'Items  will stay available at the session end. The equivalent of not setting the Until field in the restrictions setting';
$string['stayavailable_text'] = 'Stay available after session finish, set no end date';
$string['to'] = 'To';
$string['toggleselection'] = 'xToggle selection of all items';
$string['updatedforcourse'] = 'updated driprelease for course';
