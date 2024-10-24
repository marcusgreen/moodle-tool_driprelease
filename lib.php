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
use core_availability\tree;
use tool_driprelease\lib;
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
        $url = new moodle_url('/admin/tool/driprelease/view.php', ['courseid' => $course->id]);
        $name = get_string('pluginname', 'tool_driprelease');
        $navigation->add($name, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('icon', 'Driprelease',
                     'tool_driprelease'));
    }
}
