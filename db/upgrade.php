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

/**
 * Capability definitions for the driprelease
 *
 * @package    tool_driprelease
 * @copyright  2022 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for  tool_driprelease.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_tool_driprelease_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2023100300) {
        // Define field coursegroup to be added to tool_driprelease.
        $table = new xmldb_table('tool_driprelease');
        $field = new xmldb_field('coursegroup', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'sessionlength');

        // Conditionally launch add field coursegroup.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Driprelease savepoint reached.
        upgrade_plugin_savepoint(true, 2023100300, 'tool', 'driprelease');
    }

    return true;
}
