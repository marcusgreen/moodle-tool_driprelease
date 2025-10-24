<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_driprelease;
use core_availability\tree;
/**
 * Class driprelease
 *
 * @package    tool_driprelease
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class driprelease {
    /**
     * Adds/updates an instance of driprelease and its related
     *
     * @param \stdClass $fromform
     * @param int $courseid
     * @return array
     */
    public function update(\stdClass $fromform, int $courseid): array {
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
            'displaydisabled' => $fromform->displaydisabled,
            ];
            $DB->update_record('tool_driprelease', $driprelease);
            $this->manage_selections($fromform, $dripreleaseid);
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
            'displaydisabled' => $fromform->displaydisabled,
            ];
            $dripreleaseid = $DB->insert_record('tool_driprelease', $driprelease);
            $driprelease->id = $dripreleaseid;
        }
        $this->manage_selections($fromform, $dripreleaseid);
        $selections = $DB->get_records_menu('tool_driprelease_cmids', ['driprelease' => $dripreleaseid], null, 'id,coursemoduleid');
        return [$selections, $driprelease];
    }
    /**
     * Get the rows to be displayed in the schedule of dripped out modules
     *
     * @param \stdClass $dripdata
     * @return array
     */
    public function get_table_data(\stdClass $dripdata): array {
        global $DB;
        $modules = $this->get_modules($dripdata);
        $contentcounter = 0;
        $sessioncounter = 0;
        $selections = [];
        if (isset($dripdata->id)) {
            $selections = $DB->get_records_menu(
                'tool_driprelease_cmids',
                ['driprelease' => $dripdata->id],
                null,
                'id,coursemoduleid'
            );
        }
        foreach ($modules as $cm) {
            $row['selected'] = in_array($cm->id, $selections) ? 'checked' : "";

            if ($contentcounter % ($dripdata->activitiespersession) == 0) {
                if ($row['selected'] > "") {
                    $row['calculatedavailability'] = $this->calculate_availability($dripdata, $sessioncounter);
                    $sessioncounter++;
                }
                $data[] = $this->add_header($row);
            }
            $contentcounter++;
            $row['modtype'] = $dripdata->modtype;
            $data[] = $this->row_fill($row, $cm);
        }
        return $data ?? [];
    }
    /**
     * Write the availability back to the course_modules table
     * See https://moodledev.io/docs/apis/subsystems/availability/
     * @param array $tabledata
     * @param \stdClass $dripdata
     * @return void
     */
    public function update_availability(array $tabledata, \stdClass $dripdata) {
        global $DB, $COURSE;
        $updatecount = 0;
        foreach ($tabledata as $module) {
            if (!$module['isheader']) {
                if (!$module['selected'] == "checked") {
                    $this->process_unselected($module, $dripdata);
                    continue;
                }
                if (!array_key_exists('calculatedavailability', $module)) {
                    continue;
                }
                // Don't write any availability restrictions after the end of the schedule.
                if ($module['calculatedavailability']['start'] > $dripdata->schedulefinish) {
                    continue;
                }
                $cm = $module['cm'];
                set_coursemodule_visible($cm->id, true, true);
                \core\event\course_module_updated::create_from_cm($cm)->trigger();

                $availability = $module['calculatedavailability'];
                $restrictions = [];
                if ($dripdata->coursegroup) {
                    $restrictions[] = \availability_group\condition::get_json($dripdata->coursegroup);
                }
                $restrictions[] = \availability_date\condition::get_json(">=", $availability['start']);
                if (!$dripdata->stayavailable) {
                    $restrictions[] = \availability_date\condition::get_json("<", $availability['end']);
                }
                $showvalue = false;
                if ($dripdata->displaydisabled) {
                    $showvalue = true;
                }

                $showc = array_fill(0, count($restrictions), $showvalue);
                $restrictionsclass = tree::get_root_json($restrictions, tree::OP_AND, $showc);

                $DB->set_field(
                    'course_modules',
                    'availability',
                    json_encode($restrictionsclass),
                    ['id' => $module['cm']->id]
                );
                $updatecount++;
            }
        }
        $modulenameplural = get_string('modulenameplural', $dripdata->modtype);
        $msg = get_string('updated', 'moodle', $updatecount) . " " . $modulenameplural;
        $refresh = optional_param('refresh', 0, PARAM_INT);
        if (! $refresh) {
            \core\notification::add($msg, \core\notification::SUCCESS);
            rebuild_course_cache($COURSE->id);
        }
    }
    /**
     * Where thee checkbox is not selected
     * @param array  $module
     * @param \stdClass $dripdata
     * @return void
     */
    public function process_unselected(array $module, \stdClass $dripdata): void {
        global $DB;
        $cm = $module['cm'];
        if ($dripdata->hideunselected) {
            set_coursemodule_visible($cm->id, false, false);
            \core\event\course_module_updated::create_from_cm($cm)->trigger();
        } else {
            set_coursemodule_visible($cm->id, true, true);
        }

        if ($dripdata->resetunselected) {
            $DB->set_field(
                'course_modules',
                'availability',
                '',
                ['id' => $module['cm']->id]
            );
        }
    }
    /**
     * Simplify get_table_data
     *
     * @param array $row
     * @param \cm_info $cm
     * @return array
     */
    private function row_fill(array $row, \cm_info $cm): array {
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
        $row['moduleavailability'] = $this->get_availability($cm->availability);
        return $row;
    }
    /**
     * Get names of modules on course for showing
     * in the select element on the form.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_course_module_types(int $courseid): array {
        $modinfo = get_fast_modinfo($courseid);
        $modtypes = [];
        foreach ($modinfo->cms as $cm) {
            // Qbank is not a "real" activity and cannot have availability set.
            if ($cm->modname == 'qbank') {
                continue;
            }
            $modtypes[$cm->modname] = get_string('pluginname', $cm->modname);
        }
        return $modtypes;
    }
    /**
     * Add another session header row in the display/preview of scheduled modules
     *
     * @param array $row
     * @return array
     */
    public function add_header(array $row): array {
        $header = $row;
        $header['isheader'] = true;
        $header['cm'] = (object) ['id' => -1];
        $header['name'] = 'Session';
        return $header;
    }
    /**
     * Take an optional JSON string as input and return an array containing
     * the date-related availability information.
     * *
     * If the input JSON is empty, the function returns an empty array.
     *
     * Loop through each restriction in the JSON object and checks
     * if the restriction type is "date". If true, it extracts the operator and
     * timestamp from the restriction object.
     *
     * Based on the operator, the function updates the availability array with
     * the formatted date string using userdate() function.
     *
     * The function returns the availability array containing the 'from' and 'to'
     * dates in a human-readable format.
     *
     * @param ?string $json The optional JSON input string containing the availability restrictions
     * @return array The availability array containing the 'from' and 'to' dates
     */
    public function get_availability(?string $json): array {
        $availability = [];
        if (empty($json)) {
            return $availability;
        }
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
        return $availability;
    }

    /**
     * Calculate the dripping out of availability and format the dates for the session labels
     *
     * @param \stdClass $driprelease
     * @param int $sessioncounter
     * @return array
     */
    public function calculate_availability(\stdClass $driprelease, int $sessioncounter): array {
        $row = [];
        $daysrepeat = $sessioncounter * $driprelease->sessionlength;
        $daysoffset = " + $daysrepeat day";
        $start = strtotime(' + ' . $daysrepeat . ' day', $driprelease->schedulestart);
        $daysoffset = " + " . (($daysrepeat - 1) + $driprelease->sessionlength) . " day ";
        $end = strtotime($daysoffset, $driprelease->schedulestart);

        $midnight = strtotime('today midnight', $driprelease->schedulefinish);
        $endminutes = $driprelease->schedulefinish - $midnight;
        $end = strtotime('today midnight', $end);
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
    public function get_sequence(\stdClass $data): array {
        global $DB;
        $sql = 'SELECT sequence FROM {course_sections}
                WHERE course = :course
                AND sequence > ""
                ORDER BY section';
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
        /**
         * Process the checkbox selections and upsert the database records
         *
         * @param \stdClass $fromform
         * @param int $dripreleaseid
         * @return int $insertedcount // For future testing purposes.
         */
    public function manage_selections(\stdClass $fromform, int $dripreleaseid): int {
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
            [$insql, $inparams] = $DB->get_in_or_equal($todelete);
            $DB->delete_records_select("tool_driprelease_cmids", "coursemoduleid $insql", $inparams);
        }
        $toinsert = array_diff($moduleids, $selections);
        $insertedcount = 0;
        foreach ($toinsert as $moduleid) {
            $dataobject = (object) [
            'driprelease' => $dripreleaseid,
            'coursemoduleid' => $moduleid,
            ];
            $DB->insert_record('tool_driprelease_cmids', $dataobject);
            $insertedcount++;
        }
        return $insertedcount;
    }
    /**
     * Get course modules given an instance of driprelease
     *
     * @param \stdClass $dripdata
     * @return array
     */
    public static function get_modules(\stdClass $dripdata): array {
        global $DB;
        $course = $DB->get_record('course', ['id' => $dripdata->courseid]);
        $modinfo = get_fast_modinfo($course);
        if (!$modinfo->instances || (!array_key_exists($dripdata->modtype, $modinfo->instances))) {
            return [];
        };
        $modules = [];
        foreach ($modinfo->instances[$dripdata->modtype] as $cm) {
            $modules[$cm->id] = $cm;
        }
        return $modules;
    }
}
