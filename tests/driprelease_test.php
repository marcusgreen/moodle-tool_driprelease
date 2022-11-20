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
 * Unit tests for tool_driprelease.
 *
 * @package    tool_driprelease
 * @copyright  2022 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_driprelease;

defined('MOODLE_INTERNAL') || die();

global $CFG;

use mod_driprelease_mod_form;

require_once($CFG->dirroot . '/admin/tool/driprelease/lib.php');

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
/**
 * Unit tests for admin_tool_driprelease
 *
 * @copyright  2022 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class driprelease_test extends \advanced_testcase {

    /**
     * Test course
     *
     * @var \stdClass
     */
    public $course1;

    /**
     * Array of modules
     *
     * @var array
     */
    public $modules;

    /**
     * instance of driprelease
     *
     * @var \stdClass
     */
    public $driprelease;

    /**
     * data from form submission
     *
     * @var \stdClass
     */
    public $fromform;

    public function setUp() : void {
        global $CFG, $DB;
         // Create course with availability enabled.
        $CFG->enableavailability = true;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $this->course1 = $course;

        $quizgenerator = $generator->get_plugin_generator('mod_quiz');

        $this->modules[] = $quizgenerator->create_instance(array('course' => $course->id,
                'grademethod' => QUIZ_GRADEHIGHEST, 'grade' => 100.0, 'sumgrades' => 10.0,
                'attempts' => 10));

        $this->modules[] = $quizgenerator->create_instance(array('course' => $course->id,
                'grademethod' => QUIZ_GRADEHIGHEST, 'grade' => 100.0, 'sumgrades' => 10.0,
                'attempts' => 10));

        $this->modules[] = $quizgenerator->create_instance(array('course' => $course->id,
                'grademethod' => QUIZ_GRADEHIGHEST, 'grade' => 100.0, 'sumgrades' => 10.0,
                'attempts' => 10));

        foreach ($this->modules as $module) {
            $activitygroup['activity_'.$module->id] = 0;
        }

        $this->fromform = (object) [
            'modtype' => 'quiz',
            'activitiespersession' => 3,
            'sessiongroup' => ['sessionlength' => 1],
            'activitygroup' => $activitygroup,
            'schedulestart' => time(),
            'schedulefinish' => time()
        ];
        list($selections, $driprelease) = driprelease_update($this->fromform , $this->course1->id);
        $this->assertCount(0, $selections);
        $this->driprelease = $DB->get_record('tool_driprelease', ['id' => $driprelease->id]);
    }

    /**
     * Confirm modules/quizzes in a course are returned
     * as expected
     *
     *
     * @covers ::get_course_modules()
     */
    public function test_get_course_modules() {
        $this->resetAfterTest();
        $modules = get_modules($this->driprelease);
        $modulecount = count($modules);
        $this->assertEquals(count($this->modules), $modulecount);
    }
    /**
     * Check that update doesn't fall over and
     * selections are set as expected
     *
     * @covers ::driprelease_update()
     */
    public function test_update_instance() {
        $this->resetAfterTest();
        global $DB;
        $activitygroup = [];

        foreach ($this->modules as $module) {
            $activitygroup['activity_'.$module->id] = 1;
        }
        $this->fromform->activitygroup = $activitygroup;
        list($selections, $driprelease) = driprelease_update($this->fromform , $this->course1->id);
        $this->assertCount(3, $selections);
        $this->assertEquals($driprelease->id, $this->driprelease->id);
    }
}
