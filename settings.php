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
 * Data to control defaults when creating an instance of dripreleaswe
 *
 * @package    tool_driprelease
 * @copyright  2022Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
        $settings = new admin_settingpage('tool_driprelease_settings', new lang_string('pluginname', 'tool_driprelease'));

        $settings->add(new admin_setting_configtext(
                'tool_driprelease/sessionlength',
                get_string('sessionlength', 'tool_driprelease'),
                get_string('sessionlength_text', 'tool_driprelease'),
                '7',
                PARAM_ALPHANUMEXT,
                3
        ));

        $settings->add(new admin_setting_configtext(
                'tool_driprelease/activitiespersession',
                get_string('activitiespersession', 'tool_driprelease'),
                get_string('activitiespersession_text', 'tool_driprelease'),
                '5',
                PARAM_ALPHANUMEXT,
                3
        ));
        $settings->add(new admin_setting_configcheckbox(
                'tool_driprelease/stayavailable',
                get_string('stayavailable', 'tool_driprelease'),
                get_string('stayavailable_text', 'tool_driprelease'),
                0,
                PARAM_ALPHANUMEXT,
                0
        ));

        $settings->add(new admin_setting_configcheckbox(
                'tool_driprelease/hideunselected',
                get_string('hideunselected', 'tool_driprelease'),
                get_string('hideunselected_text', 'tool_driprelease'),
                0,
                PARAM_ALPHANUMEXT,
                0
        ));


        $ADMIN->add('tools', $settings);

}
