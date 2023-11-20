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
 * Library of interface functions and constants.
 *
 * @package     tool_driprelease
 * @copyright   2022 Marcus Green
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \core_availability\tree;

/**
 * A core callback to make ther plugin appear in the "more" dropdown of courses
 *
 * @param navigation_node $navigation
 * @param stdClass $course
 * @param context_course $context
 * @return void
 */
function tool_driprelease_extend_navigation_course(navigation_node $navigation, stdClass $course, context_course $context) {
    if (has_capability('moodle/course:update', $context)) {
        $url = new moodle_url('/admin/tool/driprelease/view.php', array('courseid' => $course->id));
        $name = get_string('pluginname', 'tool_driprelease');
        $navigation->add($name, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('icon', 'Driprelease',
                     'tool_driprelease'));
    }
}

/**
 * Adds/updates an instance of driprelease and its related
 *
 * @param \stdClass $fromform
 * @param int $courseid
 * @return array
 */
function driprelease_update(\stdClass $fromform , int $courseid) : array {
    global $DB;

    $dripreleaseid = $DB->get_field('tool_driprelease', 'id', ['courseid' => $courseid]);

    if ($dripreleaseid) {
        $driprelease = (object) [
            'id' => $dripreleaseid,
            'modtype' => $fromform->modtype,
            'courseid' => $courseid,
            'activitiespersession' => $fromform->activitiespersession,
            'sessionlength' => $fromform->sessiongroup['sessionlength'] ?? $fromform->sessionlength,
            'schedulestart' => $fromform->schedulestart,
            'schedulefinish' => $fromform->schedulefinish,
            'coursegroup' => $fromform->coursegroup,
            'stayavailable' => $fromform->stayavailable,
            'hideunselected' => $fromform->hideunselected,
            'resetunselected' => $fromform->resetunselected,
            'displaydisabled' => $fromform->displaydisabled
        ];
        $DB->update_record('tool_driprelease', $driprelease);
        manage_selections($fromform, $dripreleaseid);
    } else {
        $driprelease = (object) [
            'courseid' => $courseid,
            'modtype' => $fromform->modtype,
            'activitiespersession' => $fromform->activitiespersession,
            'sessionlength' => $fromform->sessionlength,
            'schedulestart' => $fromform->schedulestart,
            'schedulefinish' => $fromform->schedulefinish,
            'coursegroup' => $fromform->coursegroup,
            'stayavailable' => $fromform->stayavailable,
            'hideunselected' => $fromform->hideunselected,
            'resetunselected' => $fromform->resetunselected,
            'displaydisabled' => $fromform->displaydisabled
        ];
        $dripreleaseid = $DB->insert_record('tool_driprelease', $driprelease);
        $driprelease->id = $dripreleaseid;
    }
    manage_selections($fromform, $dripreleaseid);
    $selections = $DB->get_records_menu('tool_driprelease_cmids', ['driprelease' => $dripreleaseid], null, 'id,coursemoduleid');
    return [$selections, $driprelease];
}

/**
 * Process the checkbox selections and upsert the database records
 *
 * @param \stdClass $fromform
 * @param int $dripreleaseid
 * @return int $insertedcount // For future testing purposes.
 */
function manage_selections(\stdClass $fromform, int $dripreleaseid) : int {
    global $DB;
    if (!isset($fromform->activitygroup)) {
        return 0;
    }
    $moduleids = [];
    foreach ($fromform->activitygroup as $key => $value) {
        if ($value == 1) {
            $moduleids[] = explode('_', $key)[1];
        }
    }
    $selections = $DB->get_records_menu('tool_driprelease_cmids', ['driprelease' => $dripreleaseid], null, 'id,coursemoduleid');

    $todelete = array_diff($selections, $moduleids);
    if ($todelete) {
        list($insql, $inparams) = $DB->get_in_or_equal($todelete);
        $DB->delete_records_select("tool_driprelease_cmids", "coursemoduleid $insql", $inparams);
    }
    $toinsert = array_diff($moduleids, $selections);
    $insertedcount = 0;
    foreach ($toinsert as $moduleid) {
        $dataobject = (object) [
            'driprelease' => $dripreleaseid,
            'coursemoduleid' => $moduleid
        ];
        $DB->insert_record('tool_driprelease_cmids', $dataobject);
        $insertedcount ++;
    }
    return $insertedcount;
}

/**
 * Get course modules given an instance of driprelease
 *
 * @param \stdClass $driprelease
 * @return array
 */
function get_modules(\stdClass $driprelease) : array {
    global $DB;
    $course = $DB->get_record('course', ['id' => $driprelease->courseid]);

    $modinfo = get_fast_modinfo($course);
    if (!isset($modinfo->instances[$driprelease->modtype])) {
        return [];
    }
    $modules = [];
    foreach ($modinfo->instances[$driprelease->modtype] as $cm) {
        $modules[$cm->id] = $cm;
    }
    return $modules;
}
/**
 * Add another session header row in the display/preview of scheduled modules
 *
 * @param array $row
 * @return array
 */
function add_header(array $row) :array {
    $header = $row;
    $header['isheader'] = true;
    $header['cm'] = (object) ['id' => $header['sessioncounter']];
    $header['name'] = 'Session';
    return $header;
}
/**
 * Get the rows to be displayed in the schedule of dripped out modules
 *
 * @param \stdClass $driprelease
 * @return array
 */
function get_table_data(\stdClass $driprelease) : array {
    global $DB;
    $modules = get_modules($driprelease);
    $contentcounter = 0;
    $sessioncounter = 0;
    $selections = [];
    $flag = false;
    if (isset($driprelease->id)) {
        $selections = $DB->get_records_menu('tool_driprelease_cmids', ['driprelease' => $driprelease->id],
            null, 'id,coursemoduleid');
    }
    foreach ($modules as $cm) {
        $row['selected'] = in_array($cm->id, $selections) ? 'checked' : "";

        if ($contentcounter % ($driprelease->activitiespersession) == 0) {
            if ($row['selected'] > "") {
                $row['calculatedavailability'] = driprelease_calculate_availability($driprelease, $sessioncounter);
                $sessioncounter++;
            } else {
                 //$sessioncounter++;
                 $record = $DB->get_record(
                    'course_modules',
                    ['id' => $cm->id],
                    'availability'
                );
                $fromto = get_from_to($record->availability);
                $row['calculatedavailability']['start'] = $fromto['from'] ?? '';
                $row['calculatedavailability']['end'] = $fromto['to'] ?? '';
            }
            $row['sessioncounter'] = $sessioncounter;
            $data[] = add_header($row);
        }
        $contentcounter++;
        $row['modtype'] = $driprelease->modtype;
        $data[] = row_fill($row, $cm);
    }
    return $data ?? [];
}
/**
 * Get from and to dates as unixtimestamps
 * Used if availability is in database
 * but matching checkbox item is not selected.
 *
 * @param string|null $availability // In json format.
 * @return array // Two elements from and in unixtime format
 */
function get_from_to(?string $availability) :array {
    if (!$availability) {
        return [];
    }
    $ob = json_decode($availability);
    $fromto['from'] = $ob->c[1]->t ?? "";
    $fromto['to'] = $ob->c[2]->t ?? "";
    return $fromto;
}
/**
 * Simplify get_table_data
 *
 * @param array $row
 * @param cm_info $cm
 * @return array
 */
function row_fill(array $row, cm_info $cm) : array {
    global $DB;

    $details = $DB->get_record($row['modtype'], ['id' => $cm->instance]);
    $row['cm'] = $cm;
    $row['intro'] = strip_tags($details->intro);
    if ($cm->modname == 'quiz') {
        $questions = $DB->get_records('quiz_slots', ['quizid' => $cm->instance]);
        $row['questioncount'] = count($questions);
    }
    $row['isheader'] = false;
    $row['id'] = $cm->id;
    $row['name'] = $cm->name;
    $row['moduleavailability'] = get_availability($cm->availability);
    return $row;
}

/**
 * Write the availability back to the course_modules table
 * See https://moodledev.io/docs/apis/subsystems/availability/
 * @param array $tabledata
 * @param \stdClass $driprelease
 * @return void
 */
function update_availability(array $tabledata, \stdClass $driprelease) {
    global $DB, $COURSE;
    $updatecount = 0;

    foreach ($tabledata as $module) {
        if (!$module['isheader']) {
            if (!$module['selected'] == "checked") {
                $cm = $module['cm'];
                if ($driprelease->hideunselected) {
                    set_coursemodule_visible($cm->id, false, false);
                    \core\event\course_module_updated::create_from_cm($cm)->trigger();
                } else {
                    set_coursemodule_visible($cm->id, true, true);
                }
                if ($driprelease->resetunselected) {
                    $DB->set_field(
                        'course_modules',
                        'availability',
                        '',
                        array('id' => $module['cm']->id)
                    );
                }
                continue;
            }
            if (!array_key_exists('calculatedavailability', $module)) {
                continue;
            }
            // Don't write any availability restrictions after the end of the schedule.
            if ($module['calculatedavailability']['start'] > $driprelease->schedulefinish) {
                    continue;
            }
            $cm = $module['cm'];
            set_coursemodule_visible($cm->id, true, true);
            \core\event\course_module_updated::create_from_cm($cm)->trigger();

            $availability = $module['calculatedavailability'];
            $restrictions = [];
            if ($driprelease->coursegroup) {
                $restrictions[] = \availability_group\condition::get_json($driprelease->coursegroup);
            }
            $restrictions[] = \availability_date\condition::get_json(">=", $availability['start']);
            if (!$driprelease->stayavailable) {
                $restrictions[] = \availability_date\condition::get_json("<", $availability['end']);
            }
            $showvalue = false;
            if ($driprelease->displaydisabled) {
                $showvalue = true;
            }

            $showc = array_fill(0, count($restrictions), $showvalue);
            $restrictionsclass = tree::get_root_json($restrictions, tree::OP_AND, $showc);

            $DB->set_field(
                'course_modules',
                'availability',
                json_encode($restrictionsclass),
                array('id' => $module['cm']->id)
            );
            $updatecount++;
        }
    }
    $modulenameplural = get_string('modulenameplural', $driprelease->modtype);
    $msg = get_string('updated', 'moodle', $updatecount). " ".$modulenameplural;
    \core\notification::add($msg, \core\notification::SUCCESS);
    rebuild_course_cache($COURSE->id);
}


/**
 * Get the date related availability for an activity
 *
 * @param string $json
 * @return array
 */
function get_availability(?string $json) : array {
    $availability = [];
    if ($json > "") {
        $decoded = json_decode($json);
        foreach ($decoded->c as $restriction) {
            if (property_exists($restriction, 'type') && $restriction->type == "date") {
                $operator = $restriction->d;
                if ($operator == ">=") {
                    $datetime = $restriction->t;
                    $availability['from'] = userdate($datetime, '%a %d %b %Y %H:%M');
                } else {
                    $datetime = $restriction->t;
                    $availability['to'] = userdate($datetime, '%a %d %b %Y %H:%M');
                }
            }
        }
    }
    return $availability;
}

  /**
   * Calculate the dripping out of availability and format the dates for the session labels
   *
   * @param \stdClass $driprelease
   * @param int $sessioncounter
   * @return array
   */
function driprelease_calculate_availability(\stdClass $driprelease, int $sessioncounter) : array {
    $row = [];
    $daysrepeat = $sessioncounter * $driprelease->sessionlength;
    $daysoffset = " + $daysrepeat day";
    $start = strtotime(' + ' . $daysrepeat . ' day', $driprelease->schedulestart);
    $daysoffset = " + " . (($daysrepeat - 1) + $driprelease->sessionlength) . " day ";
    $end = strtotime($daysoffset, $driprelease->schedulestart);

    $midnight = strtotime('today midnight', $driprelease->schedulefinish);
    $endminutes = $driprelease->schedulefinish - $midnight;
    $end = strtotime('today midnight', $end );
    $end += $endminutes;

    $row['sessioncounter'] = $sessioncounter + 1;
    $row['start'] = $start;
    $row['end'] = $end;
    return $row;
}

/**
 * This is designed to return the coursemods in the order they are displayed on the course
 * It is currently not used and may be deleted at some point, or the need for it may be obscured
 * by the way test data means course activities are always in the order they were created.
 *
 * @param \stdClass $data
 * @return array
 */
function get_sequence(\stdClass $data) : array {
    global $DB;
    $sql = 'SELECT sequence FROM {course_sections} WHERE course = :course AND sequence > "" ORDER BY section';
    $coursesequence = $DB->get_records_sql($sql, ['course' => $data->course]);
    $activitiesordered = [];
    $i = 0;
    foreach ($coursesequence as $item) {
        $temp = explode(',', $item->sequence);
        foreach ($temp as $t) {
            if (array_key_exists($t, $data->activities)) {
                $activitiesordered[$i] = $t;
                $i++;
            }
        }
    }
    return $activitiesordered;
}
