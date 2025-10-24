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
 * @package     tool_driprelease
 * @copyright   2022 Marcus Green
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/lib.php');

use tool_driprelease\event\driprelease_updated;
use tool_driprelease\event\driprelease_viewed;
use tool_driprelease\driprelease;
$driprelease = new driprelease();

// Course module id.
$courseid = optional_param('courseid', 0, PARAM_INT);
$modtype = optional_param('modtype', 'quiz', PARAM_TEXT);

if (!$courseid) {
    redirect(new moodle_url('/'));
}
$context = context_course::instance($courseid);

if (!has_capability('moodle/course:update', $context)) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}
$cancel = optional_param('cancel', null, PARAM_TEXT);
if (isset($cancel)) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}
require_login($courseid);

require_once($CFG->dirroot . '/admin/tool/driprelease/driprelease_form.php');

$PAGE->set_context($context);

$PAGE->set_url('/admin/tool/driprelease/view.php', ['courseid' => $courseid]);

if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new moodle_exception('invalid course id');
}

$PAGE->set_course($course);
global $DB, $USER;

$dripdata = $DB->get_record('tool_driprelease', ['courseid' => $courseid], '*', IGNORE_MISSING);
if (!$dripdata) {
    $config = get_config('tool_driprelease');
    $dripdata = (object)[
        'courseid' => $courseid,
        'modtype' => $modtype,
        'activitiespersession' => $config->activitiespersession ?? 0,
        'schedulestart' => time(),
        'coursegroup' => '',
        'sessionlength' => $config->sessionlength ?? 0,
        'mydtype' => '',
    ];
} else {
    $dripdata->modtype = $modtype;
}

if (!$dripdata) {
    $dripdata = (object) [
        'courseid' => $courseid,
        'modtype' => '',
    ];
}

$mform = new tool_driprelease_form(null, ['driprelease' => $dripdata]);

navigation_node::override_active_url(new moodle_url('admin/tool/driprelease/view.php', ['courseid' => $courseid]));
$eventdata = [
    'context' => context_course::instance($courseid),
    'other' => [
        'username' => $USER->username,
        'course' => $course->shortname,
    ],
];

if ($fromform = $mform->get_data()) {
    if (isset($fromform->submitbutton) || isset($fromform->submitbutton2) || isset($fromform->refresh)) {
        $dripdata->schedulestart = $fromform->schedulestart;
        $dripdata->stayavailable = $fromform->stayavailable;
        $dripdata->hideunselected = $fromform->hideunselected;
        $dripdata->coursegroup = $fromform->coursegroup;
        $dripdata->moduletype = $fromform->modtype;
        $dripdata->refresh = true;
        [$selections, $dripdata] = $driprelease->update($fromform, $courseid);
        if (count($selections) == 0 && !isset($fromform->refresh)) {
            $msg = get_string('noselections', 'tool_driprelease');
            \core\notification::add($msg, \core\notification::WARNING);
        }

        $tabledata = $driprelease->get_table_data($dripdata);
        $driprelease->update_availability($tabledata, $dripdata);

        $event = driprelease_updated::create($eventdata);
        $event->trigger();
    }
    if (isset($fromform->submitbutton2)) {
        redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
    }
}

$tabledata = $driprelease->get_table_data($dripdata);

$templates = [];
$iterator = new DirectoryIterator(__DIR__ . '/templates');
foreach ($iterator as $item) {
    if ($item->isDot()) {
        continue;
    }
    $templates[] = strtok($item->getFilename(), ".");
}

$templatefile = $modtype;
if (!in_array($modtype, $templates)) {
    $templatefile = 'genericmod';
}

$out = $OUTPUT->render_from_template(
    'tool_driprelease/' . $templatefile,
    ['tabledata' => $tabledata, 'modtype' => get_string("pluginname", $modtype)]
);

$mform->set_data($dripdata);

$event = driprelease_viewed::create($eventdata);
$event->trigger();

echo $OUTPUT->header();
$mform->display();
echo $out;
echo $OUTPUT->footer();
