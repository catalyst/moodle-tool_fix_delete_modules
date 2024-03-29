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

namespace tool_fix_delete_modules;

use core\task\adhoc_task;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . "/../classes/diagnosis.php");
require_once(__DIR__ . "/../classes/delete_module.php");
require_once(__DIR__ . "/../classes/delete_task_list.php");

/**
 * The fix_course_delete_module_test base test class.
 *
 * Tests the setup of course/modules/tasks for other tests.
 *
 * @package     tool_fix_delete_modules
 * @category    test
 * @author      Brad Pasley <brad.pasley@catalyst-au.net>
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fix_course_delete_module_test extends \advanced_testcase {

    /** @var $user moodle user object*/
    public $user;
    /** @var $course moodle course object*/
    public $course;
    /** @var $page moodle module object*/
    public $page;
    /** @var $pagecm moodle course module object*/
    public $pagecm;
    /** @var int course module contextid*/
    public $pagecontextid;
    /** @var $url moodle module object*/
    public $url;
    /** @var $urlcm moodle course module object*/
    public $urlcm;
    /** @var int course module contextid*/
    public $urlcontextid;
    /** @var $book moodle module object*/
    public $book;
    /** @var $bookcm moodle course module object*/
    public $bookcm;
    /** @var int course module contextid*/
    public $bookcontextid;
    /** @var $assign moodle module object*/
    public $assign;
    /** @var $assigncm moodle course module object*/
    public $assigncm;
    /** @var int course module contextid*/
    public $assigncontextid;
    /** @var $quiz moodle module object*/
    public $quiz;
    /** @var quizcm moodle course module object*/
    public $quizcm;
    /** @var int course module contextid*/
    public $quizcontextid;
    /** @var $label moodle module object*/
    public $label;
    /** @var $labelcm moodle course module object*/
    public $labelcm;
    /** @var int course module contextid*/
    public $labelcontextid;
    /** @var adhoc_task object */
    public $removaltaskassign;
    /** @var adhoc_task object */
    public $removaltaskmulti;
    /** @var adhoc_task object */
    public $removaltaskpage;
    /** @var adhoc_task object */
    public $removaltaskurl;
    /** @var adhoc_task object */
    public $removaltaskbook;
    /** @var adhoc_task object */
    public $removaltasklabel;

    /**
     * Setup test.
     */
    public function setUp(): void {
        global $DB;
        $this->resetAfterTest();

        // Ensure all adhoc tasks/cache are cleared.
        if (isset(\core\task\manager::$miniqueue)) {
            \core\task\manager::$miniqueue = [];
        } // Clear the cached queue.
        $DB->delete_records('task_adhoc');

        // Setup a course with a page, a url, a book, and an assignment and a quiz module.
        $this->user     = $this->getDataGenerator()->create_user();
        $this->course   = $this->getDataGenerator()->create_course();
        $this->page     = $this->getDataGenerator()->create_module('page', array('course' => $this->course->id));
        $this->pagecm   = get_coursemodule_from_id('page', $this->page->cmid);
        $this->url      = $this->getDataGenerator()->create_module('url', array('course' => $this->course->id));
        $this->urlcm    = get_coursemodule_from_id('url', $this->url->cmid);
        $this->book     = $this->getDataGenerator()->create_module('book', array('course' => $this->course->id));
        $this->bookcm   = get_coursemodule_from_id('book', $this->book->cmid);
        $this->assign   = $this->getDataGenerator()->create_module('assign', array('course' => $this->course->id));
        $this->assigncm = get_coursemodule_from_id('assign', $this->assign->cmid);
        $this->quiz     = $this->getDataGenerator()->create_module('quiz', array('course' => $this->course->id));
        $this->quizcm   = get_coursemodule_from_id('quiz', $this->quiz->cmid);
        $this->label    = $this->getDataGenerator()->create_module('label', array('course' => $this->course->id));
        $this->labelcm  = get_coursemodule_from_id('label', $this->label->cmid);
        $this->pagecontextid   = (\context_module::instance($this->page->cmid))->id;
        $this->urlcontextid    = (\context_module::instance($this->url->cmid))->id;
        $this->assigncontextid = (\context_module::instance($this->assign->cmid))->id;
        $this->quizcontextid   = (\context_module::instance($this->quiz->cmid))->id;
        $this->labelcontextid  = (\context_module::instance($this->label->cmid))->id;

        // Delete page & quiz table record to replicate failed course_module_delete adhoc tasks.
        $DB->delete_records('page');
        $DB->delete_records('quiz');

        // Delete the url mod's course_module record to replicate a failed course_module_delete adhoc task.
        $DB->delete_records('course_modules', array('id' => $this->url->cmid));

        // Remove cmid from sequence for label.
        $sql = "SELECT * FROM {course_sections} WHERE course = ? AND sequence LIKE ?";
        $section = $DB->get_record_sql($sql, [$this->course->id, '%' . $this->label->cmid . '%']);
        $sequences = explode(',', $section->sequence);
        $newsequence = [];
        foreach ($sequences as $sequence) {
            if ($sequence != $this->label->cmid) {
                $newsequence[] = $sequence;
            }
        }
        $section->sequence = implode(',', $newsequence);
        $DB->update_record('course_sections', $section);

        // Setup Adhoc tasks, but don't queue them.
        // Setup delete assign adhoc task.
        $this->removaltaskassign = new \core_course\task\course_delete_modules();
        $assigndata = [
            'cms' => [$this->assigncm],
            'userid' => $this->user->id,
            'realuserid' => $this->user->id
        ];
        $this->removaltaskassign->set_custom_data($assigndata);

        // Setup delete mutli-module adhoc task.
        $this->removaltaskmulti = new \core_course\task\course_delete_modules();
        // When MDL-80930 is integrated, the adhoc task course_delete_modules only stores failed cmids.
        // Hence, we have 2 failed cmids, page & quiz here, so the multi task checks still be valid.
        $cmsarray = array((string) $this->assigncm->id => array('id' => $this->assigncm->id),
                          (string) $this->pagecm->id => array('id' => $this->pagecm->id),
                          (string) $this->quizcm->id   => array('id' => $this->quizcm->id));
        $multidata = [
            'cms' => $cmsarray,
            'userid' => $this->user->id,
            'realuserid' => $this->user->id
        ];
        $this->removaltaskmulti->set_custom_data($multidata);

        // Setup delete mutli-module adhoc task.
        $this->removaltaskpage = new \core_course\task\course_delete_modules();
        $pagedata = [
            'cms' => [$this->pagecm],
            'userid' => $this->user->id,
            'realuserid' => $this->user->id
        ];
        $this->removaltaskpage->set_custom_data($pagedata);

        // Setup adhoc task for url module deletion.
        $this->removaltaskurl = new \core_course\task\course_delete_modules();
        $urldata = [
            'cms' => [$this->urlcm],
            'userid' => $this->user->id,
            'realuserid' => $this->user->id
        ];
        $this->removaltaskurl->set_custom_data($urldata);

        // Setup adhoc task for book module deletion.
        $this->removaltaskbook = new \core_course\task\course_delete_modules();
        $bookdata = [
            'cms' => [$this->bookcm],
            'userid' => $this->user->id,
            'realuserid' => $this->user->id
        ];
        $this->removaltaskbook->set_custom_data($bookdata);

        // Setup adhoc task for label module deletion.
        $this->removaltasklabel = new \core_course\task\course_delete_modules();
        $labeldata = [
            'cms' => [$this->labelcm],
            'userid' => $this->user->id,
            'realuserid' => $this->user->id
        ];
        $this->removaltasklabel->set_custom_data($labeldata);
    }

    /**
     * Test for setting up course, modules and course module adhoc tasks.
     *
     * @coversNothing
     */
    public function test_delete_task_setup() {
        global $DB;
        $this->resetAfterTest(true);

        // The assign & book module exists in the course modules table & other tables.
        $this->assertTrue($DB->record_exists('course_modules', array('id' => $this->assign->cmid)));
        $this->assertTrue($DB->record_exists('assign', array('id' => $this->assigncm->instance)));
        $this->assertTrue($DB->record_exists('course_modules', array('id' => $this->book->cmid)));
        $this->assertTrue($DB->record_exists('book', array('id' => $this->bookcm->instance)));
        $this->assertTrue($DB->record_exists('course_modules', array('id' => $this->label->cmid)));
        $this->assertTrue($DB->record_exists('label', array('id' => $this->labelcm->instance)));

        // Check page & quiz table records deleted.
        $this->assertFalse($DB->record_exists('page', array('id' => $this->pagecm->instance)));
        $this->assertFalse($DB->record_exists('quiz', array('id' => $this->quizcm->instance)));
        $this->assertTrue($DB->record_exists('course_modules', array('id' => $this->page->cmid)));
        $this->assertTrue($DB->record_exists('course_modules', array('id' => $this->quiz->cmid)));

        // Delete the url mod's course_module record to replicate a failed course_module_delete adhoc task.
        $this->assertFalse($DB->record_exists('course_modules', array('id' => $this->url->cmid)));
        $this->assertTrue($DB->record_exists('url', array('id' => $this->urlcm->instance)));
    }

    /**
     * Utility function to find the adhoc task's id from the database table.
     *
     * @param \core\task\adhoc_task $task the adhoc_task object from which to find the taskid.
     * @return int taskid.
     **/
    public function find_taskid(\core\task\adhoc_task $task) {
        global $DB;

        $dbtasks = $DB->get_records('task_adhoc', array('classname' => '\core_course\task\course_delete_modules'));
        $taskid = 0;
        foreach ($dbtasks as $dbtaskid => $dbtask) {
            if ($dbtask->customdata === $task->get_custom_data_as_string()) {
                $taskid = $dbtaskid;
            }
        }
        return $taskid;
    }

    /**
     * Common set up for diagnoser, reporter, surgeon tests
     *
     */
    public function setup_test(): array {
        global $DB;

        // Queue adhoc task for a multi-module delete (both quiz and assign).
        \core\task\manager::queue_adhoc_task($this->removaltaskmulti);

        // Execute task (assign cm should complete, quiz cm should fail).
        // This will fail due to the quiz record already being deleted.
        $now = time();
        $this->removaltaskmulti = \core\task\manager::get_next_adhoc_task($now);
        $adhoctaskprecount = count($DB->get_records('task_adhoc'));
        // Exception expected to be thrown, but tested at end to allow rest of code to run.
        $exceptionthrown = false;
        try {
            $this->removaltaskmulti->execute();
        } catch (\moodle_exception $exception) {
            // Replicate failed task.
            $this->assertCount($adhoctaskprecount, $DB->get_records('task_adhoc'));
            \core\task\manager::adhoc_task_failed($this->removaltaskmulti);
            $this->assertCount($adhoctaskprecount, $DB->get_records('task_adhoc'));
            $exceptionthrown = $exception; // Run exeception case at end of function.
        }

        // Queue adhoc task for page module deletion.
        \core\task\manager::queue_adhoc_task($this->removaltaskpage);

        // Queue adhoc task for url module deletion.
        \core\task\manager::queue_adhoc_task($this->removaltaskurl);

        // Queue adhoc task for label module deletion.
        \core\task\manager::queue_adhoc_task($this->removaltasklabel);

        // DON'T Queue adhoc task for book module deletion.
        // This will be used to test a task which is absent from the task_adhoc table.

        // The assign & url module have been deleted from the course.
        // ... quiz are still thought to be present.
        // ... page are still thought to be present.
        // ... url has an orphaned record.
        // ... book remains undeleted.
        // ... label doesn't exist in the section record.
        $this->assertFalse($DB->record_exists('course_modules', ['id' => $this->assigncm->id]));
        $this->assertFalse($DB->record_exists('course_modules', ['id' => $this->urlcm->id]));
        $this->assertTrue($DB->record_exists('course_modules', ['id' => $this->pagecm->id]));
        $this->assertTrue($DB->record_exists('course_modules', ['id' => $this->quizcm->id]));
        $this->assertTrue($DB->record_exists('course_modules', ['id' => $this->bookcm->id]));
        $this->assertTrue($DB->record_exists('course_modules', ['id' => $this->labelcm->id]));
        $this->assertFalse($DB->record_exists('assign', ['id' => $this->assigncm->instance]));
        $this->assertFalse($DB->record_exists('page', ['id' => $this->pagecm->instance]));
        $this->assertFalse($DB->record_exists('quiz', ['id' => $this->quizcm->instance]));
        $this->assertTrue($DB->record_exists('url', ['id' => $this->urlcm->instance]));
        $this->assertTrue($DB->record_exists('book', ['id' => $this->bookcm->instance]));
        $this->assertTrue($DB->record_exists('label', ['id' => $this->labelcm->instance]));
        $this->assertEmpty($DB->get_records('page'));
        $this->assertEmpty($DB->get_records('assign'));
        $this->assertEmpty($DB->get_records('quiz'));
        $this->assertNotEmpty($DB->get_records('url'));
        $this->assertNotEmpty($DB->get_records('book'));
        $this->assertNotEmpty($DB->get_records('label'));

        // First create a delete_task_list object first.
        $deletetasklist = new delete_task_list(0);

        // Create delete_tasks from the delete_task.
        $deletetasks = array_values($deletetasklist->get_deletetasks());
        foreach ($deletetasks as $deletetask) {
            $deletemodules = $deletetask->get_deletemodules();
            if (count($deletemodules) > 1) { // It's the multi module task.
                $deletemultitask = $deletetask;
            } else { // It's one of the single module tasks.
                $deletemodule = current($deletemodules);
                $modulename = $deletemodule->get_modulename();
                switch ($modulename) {
                    case 'page':
                        $deletepagetask = $deletetask;
                        break;
                    case 'label':
                        $deletelabeltask = $deletetask;
                        break;
                    default:
                        $deleteurltask = $deletetask;
                        break;
                }
            }
        }
        // This task will not exist in the task_adhoc table.
        $deletebooktask = new delete_task(999999, json_decode($this->removaltaskbook->get_custom_data_as_string()));

        $dbtasks = $DB->get_records('task_adhoc', ['classname' => '\core_course\task\course_delete_modules']);
        $this->assertCount(4, $dbtasks);

        return [$deletemultitask, $deletepagetask, $deleteurltask, $deletebooktask, $deletelabeltask, $exceptionthrown];
    }
}
