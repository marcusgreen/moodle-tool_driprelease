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
 * The main tool_driprelease configuration form.
 *
 * @package     tool_driprelease
 * @copyright   2022 Marcus Green
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Module instance settings form.
 *
 * @package     tool_driprelease
 * @copyright   2022 Marcus Greebn
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_driprelease_form extends moodleform {
    /** @var array options to be used with date_time_selector fields in the quiz. */
    public static $datefieldoptions = array('optional' => false);
    /** @var array modules on the course */
    public $modules;
    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $DB, $PAGE;
        require_once($CFG->dirroot . '/course/externallib.php');

        $mform = $this->_form;

        $driprelease = (object) $this->_customdata['driprelease'];

        $PAGE->requires->js_call_amd('tool_driprelease/modform', 'init');

        $courseid = optional_param('courseid', '', PARAM_INT);
        $course = $DB->get_record('course', ['id' => $courseid]);

        $mform->addElement('hidden', 'courseid', $courseid);

        $mform->addElement('header', 'driprelease', get_string('dripreleaseforcourse', 'tool_driprelease')." ".$course->shortname);
        $mform->setExpanded('driprelease');

        $mform->setType('courseid', PARAM_INT);
//        $mform->addElement('html', "<div id=hidemodtypes style='display:none;'>");
        $modtypes = ['quiz' => 'Quiz', 'assign' => 'Assign'];
        $mform->addElement('select', 'modtype', 'Module type', $modtypes);
        $mform->setDefault('modtype', 'quiz');
//        $mform->addElement('html', "</div>");

        $mform->setType('modtype', PARAM_TEXT);
        $this->modules = $this->get_modules($course, $driprelease->modtype);
        if ($this->modules) {
            foreach ($this->modules as $module) {
                    $activitycbx[] = $mform->createElement('advcheckbox', 'activity_'.$module->id, null, null);
            }
            $mform->addGroup( $activitycbx, 'activitygroup');
        }
        $driprelease->schedulestart = $driprelease->schedulestart ?? time();
        // Start dates.
        $mform->addElement(
            'date_time_selector',
            'schedulestart',
            get_string('schedulestart', 'tool_driprelease'),
            $driprelease->schedulestart
        );
        $mform->setDefault('schedulestart', ($driprelease->schedulestart ?? ''));
        $mform->addHelpButton('schedulestart', 'schedulestart', 'tool_driprelease');

        $driprelease->schedulefinish = $driprelease->schedulefinish ?? (time() + DAYSECS);

        // Finish dates.
        $mform->addElement(
            'date_time_selector',
            'schedulefinish',
            get_string('schedulefinish', 'tool_driprelease'),
            $driprelease->schedulefinish
        );
        $mform->setDefault('schedulefinish', ($driprelease->schedulefinish ?? ''));
        $mform->setType('schedulefinish', PARAM_INT);

        $driprelease->sessionlength = $driprelease->sessionlength ?? get_config('tool_driprelease', 'tool_driprelease');
        $group[] = $mform->createElement('text', 'sessionlength', get_string('sessionlength', 'tool_driprelease'),
                ['value' => $driprelease->sessionlength, 'size' => 3]);
        $group[] = $mform->createElement('html', get_string('days', 'tool_driprelease'). '&nbsp;&nbsp;&nbsp;');

        $mform->addElement('advcheckbox', 'stayavailable', get_string('stayavailable', 'tool_driprelease'));
        $mform->addHelpButton('stayavailable', 'stayavailable', 'tool_driprelease');
        $mform->setDefault('stayavailable', $driprelease->stayavailable ?? get_config('stayavailable', 'tool_driprelease'));

        $mform->addElement('advcheckbox', 'hideunselected', get_string('hideunselected', 'tool_driprelease'));
        $mform->addHelpButton('hideunselected', 'hideunselected', 'tool_driprelease');
        $mform->setDefault('hideunselected', $driprelease->hideunselected ?? get_config('hideunselected', 'tool_driprelease'));

        $mform->addGroup($group, 'sessiongroup', get_string('sessionlength', 'tool_driprelease') . '&nbsp;&nbsp;', '', ' ', false);
        $mform->addRule('sessiongroup', null, 'required', null, 'client');

        $mform->setType('sessiongroup', PARAM_RAW);
        $mform->addHelpButton('sessiongroup', 'session', 'tool_driprelease');

        $mform->addElement('text', 'activitiespersession', get_string('activitiespersession', 'tool_driprelease'), ['size' => '3']);
        $mform->addRule('activitiespersession', null, 'required', null, 'client');
        $mform->setType('activitiespersession', PARAM_INT);
        $mform->setDefault('activitiespersession', $driprelease->activitiespersession ?? get_config('activitiespersession',
             'tool_driprelease'));
        $mform->addHelpButton('activitiespersession', 'activitiespersession', 'tool_driprelease');

        $this->add_action_buttons();
    }

    /**
     * Check for errors in the form data
     *
     * @param array $fromform
     * @param array $tabledata
     * @return boolean
     */
    public function validation($fromform, $tabledata) {
        parent::validation($fromform, $tabledata);
        $errors = [];
        $duration = $fromform['schedulefinish'] - $fromform['schedulestart'];
        if ($duration < DAYSECS) {
            $errors['schedulefinish'] = get_string('starttofinishmustbe', 'tool_driprelease');
        }
        if ($fromform['activitiespersession'] < 1) {
            $errors['activitiespersession'] = get_string('activitiespersessionerror', 'tool_driprelease');
        }
        $sessionlength = $fromform['sessiongroup']['sessionlength'];

        if ($sessionlength < 1) {
            $errors['sessiongroup'] = get_string('sessionlengtherror', 'tool_driprelease');
        }
        if (($sessionlength * DAYSECS) > $duration) {
            $errors['sessiongroup'] = get_string('sessionlengthislonger', 'tool_driprelease');
        }

        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

    /**
     * Get an array of the modules of a specified type within a courseß
     *
     * @param \stdClass $course
     * @param string $modtype
     * @return array
     */
    public function get_modules(\stdClass $course, string $modtype) : array {

        $modinfo = get_fast_modinfo($course);
        if (!isset($modinfo->instances[$modtype])) {
            return [];
        }
        $modules = [];
        foreach ($modinfo->instances[$modtype] as $cm) {
            $modules[$cm->id] = $cm;
        }
        return $modules;
    }
    /**
     * Set form default values
     *
     * @param \stdClass $driprelease
     * @return void
     */
    public function set_data($driprelease) {
        if (!isset($driprelease->id)) {
            return;
        }
        if (empty($this->modules)) {
            return;
        }
        global $DB;
        $mform = $this->_form;
        $activitygroup = $mform->getElement('activitygroup');
        $checkboxes = $activitygroup->getElements();
        $dbselections = $DB->get_records_menu('tool_driprelease_cmids',
                ['driprelease' => $driprelease->id], null, 'id,coursemoduleid');

        foreach ($checkboxes as $checkbox) {
            $name = $checkbox->getAttributes()['name'];
            $id = explode('_', $name)[1];
            if (in_array($id, $dbselections)) {
                 $checkbox->setValue(true);
            }
        }
        return $dbselections;
    }
    /**
     * Duplicate the functionality of the mod forms action buttons
     *
     * @param boolean $cancel
     * @param string $submitlabel
     * @param string $submit2label
     * @return void
     */
    public function add_action_buttons($cancel=true, $submitlabel=null, $submit2label=null) {
        if (is_null($submitlabel)) {
            $submitlabel = get_string('savechangesanddisplay');
        }

        if (is_null($submit2label)) {
            $submit2label = get_string('savechangesandreturntocourse');
        }

        $mform = $this->_form;

        // Elements in a row need a group.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', $submit2label);

        if ($submitlabel !== false) {
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        }

        if ($cancel) {
            $buttonarray[] = &$mform->createElement('cancel');
        }

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
    }
}
