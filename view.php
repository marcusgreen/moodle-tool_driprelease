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
 * Prints an instance of mod_driprelease.
 *
 * @package     mod_driprelease
 * @copyright   2022 Marcus Green
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once(__DIR__.'/lib.php');

use \tool_driprelease\event\driprelease_updated;
use \tool_driprelease\event\driprelease_viewed;

// Course module id.
$courseid = optional_param('courseid', 0, PARAM_INT);

$modtype = 'quiz';

if (!$courseid) {
    redirect(new moodle_url('/'));
}
$cancel = optional_param('cancel', null, PARAM_TEXT);
if (isset($cancel)) {
    redirect (new moodle_url('/course/view.php', ['id' => $courseid]));
}
require_login($courseid);

require_once($CFG->dirroot . '/admin/tool/driprelease/driprelease_form.php');

$PAGE->set_context(context_course::instance($courseid));

$PAGE->set_url('/tool/admin/driprelease/view.php', ['courseid' => $courseid]);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new moodle_exception('invalid course id');
}

$PAGE->set_course($course);
global $DB, $USER;

$driprelease = $DB->get_record('tool_driprelease' , ['courseid' => $courseid], '*', IGNORE_MISSING);
if (!$driprelease) {
    $config = get_config('tool_driprelease');
    $driprelease = (object)[
        'courseid' => $courseid,
        'modtype' => $modtype,
        'activitiespersession' => $config->activitiespersession,
        'schedulestart' => time(),
        'repeatcount' => $config->repeatcount
    ];
} else {
    $driprelease->modtype = $modtype;
}

if (!$driprelease) {
    $driprelease = (object) ['courseid' => $courseid];
}

$mform = new tool_driprelease_form(null, ['driprelease' => $driprelease]);

navigation_node::override_active_url(new moodle_url('admin/tool/driprelease/view.php', ['courseid' => $courseid]));
$eventdata = [
    'context' => context_course::instance($courseid),
    'other' => [
        'username' => $USER->username,
        'course' => $course->shortname
    ]
];

if ($fromform = $mform->get_data()) {
    if (isset($fromform->submitbutton) || isset($fromform->submitbutton2)) {
        $driprelease->schedulestart = $fromform->schedulestart;
        list($selections, $driprelease) = driprelease_update($fromform, $courseid);
        $tabledata = get_table_data($driprelease, 'quiz');
        update_availability($tabledata);

        $event = driprelease_updated::create($eventdata);
        $event->trigger();
    }
    if (isset($fromform->submitbutton2)) {
        redirect (new moodle_url('/course/view.php', ['id' => $courseid]));
    }
}

$tabledata = get_table_data($driprelease);

$out = $OUTPUT->render_from_template('tool_driprelease/'.$modtype, ['tabledata' => $tabledata]);

$mform->set_data($driprelease);

$event = driprelease_viewed::create($eventdata);
$event->trigger();

echo $OUTPUT->header();
$mform->display();
echo $out;
echo $OUTPUT->footer();

