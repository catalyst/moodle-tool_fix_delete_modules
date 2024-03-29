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

use Exception;
use tool_fix_delete_modules\reporter;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . "/../classes/diagnosis.php");
require_once(__DIR__ . "/../classes/delete_module.php");
require_once(__DIR__ . "/../classes/delete_task_list.php");
require_once("fix_course_delete_module_test.php");

/**
 * The test_fix_course_delete_module_class_reporter test class.
 *
 * Tests for the reporter class.
 *
 * @package     tool_fix_delete_modules
 * @category    test
 * @author      Brad Pasley <brad.pasley@catalyst-au.net>
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reporter_test extends fix_course_delete_module_test {

    /**
     * Test for get/set modulename & get/set contextid.
     *
     * @covers \tool_fix_course_delete_module\reporter
     */
    public function test_reporter_class() {
        global $DB;

        [$deletemultitask, $deletepagetask, $deleteurltask, $deletebooktask, $deletelabeltask, $exceptionthrown]
            = $this->setup_test();

        // Creating diagnosis objects.
        $diagnosermultitask = new diagnoser($deletemultitask);
        $diagnoserpagetask  = new diagnoser($deletepagetask);
        $diagnoserurltask   = new diagnoser($deleteurltask);
        $diagnoserbooktask  = new diagnoser($deletebooktask);
        $diagnoserlabeltask  = new diagnoser($deletelabeltask);

        // Create Test surgeon objects.
        $surgeonmultitask = new surgeon($diagnosermultitask->get_diagnosis());
        $surgeonpagetask  = new surgeon($diagnoserpagetask->get_diagnosis());
        $surgeonurltask   = new surgeon($diagnoserurltask->get_diagnosis());
        $surgeonbooktask  = new surgeon($diagnoserbooktask->get_diagnosis());
        $surgeonlabeltask  = new surgeon($diagnoserlabeltask->get_diagnosis());

        // Expected outcome messages.
        $messagesmulti = [get_string('outcome_separate_into_individual_task', 'tool_fix_delete_modules'),
                          get_string('outcome_separate_into_individual_task', 'tool_fix_delete_modules'),
                          get_string('outcome_separate_old_task_deleted', 'tool_fix_delete_modules'),
                          get_string('outcome_task_fix_successful', 'tool_fix_delete_modules')
        ];

        $messagespage = [get_string('outcome_file_table_record_deleted', 'tool_fix_delete_modules'),
                         get_string('outcome_blog_table_record_deleted', 'tool_fix_delete_modules'),
                         get_string('outcome_completion_table_record_deleted', 'tool_fix_delete_modules'),
                         get_string('outcome_completion_criteria_table_record_deleted', 'tool_fix_delete_modules'),
                         get_string('outcome_tag_table_record_deleted', 'tool_fix_delete_modules'),
                         get_string('outcome_context_table_record_deleted', 'tool_fix_delete_modules'),
                         get_string('outcome_course_module_table_record_deleted', 'tool_fix_delete_modules'),
                         get_string('outcome_course_section_data_delete_fail', 'tool_fix_delete_modules'),
                         get_string('outcome_adhoc_task_record_rescheduled', 'tool_fix_delete_modules'),
                         get_string('outcome_module_fix_successful', 'tool_fix_delete_modules')
        ];
        $messagesurl = $messagespage;
        array_unshift($messagesurl, get_string('outcome_course_module_table_record_not_found', 'tool_fix_delete_modules'));

        $messagesbook = [get_string('outcome_adhoc_task_record_advice', 'tool_fix_delete_modules')];
        $messageslabel = [get_string('outcome_course_section_data_fixed', 'tool_fix_delete_modules'),
            get_string('outcome_file_table_record_deleted', 'tool_fix_delete_modules'),
            get_string('outcome_blog_table_record_deleted', 'tool_fix_delete_modules'),
            get_string('outcome_completion_table_record_deleted', 'tool_fix_delete_modules'),
            get_string('outcome_completion_criteria_table_record_deleted', 'tool_fix_delete_modules'),
            get_string('outcome_tag_table_record_deleted', 'tool_fix_delete_modules'),
            get_string('outcome_context_table_record_deleted', 'tool_fix_delete_modules'),
            get_string('outcome_course_module_table_record_deleted', 'tool_fix_delete_modules'),
            get_string('outcome_course_section_data_deleted', 'tool_fix_delete_modules'),
            get_string('outcome_adhoc_task_record_rescheduled', 'tool_fix_delete_modules'),
            get_string('outcome_module_fix_successful', 'tool_fix_delete_modules')
        ];

        $expectedoutcomemultitask = new outcome($deletemultitask, $messagesmulti);
        $expectedoutcomepage      = new outcome($deletepagetask,  $messagespage);
        $expectedoutcomeurltask   = new outcome($deleteurltask,   $messagesurl);
        $expectedoutcomebooktask  = new outcome($deletebooktask,  $messagesbook);
        $expectedoutcomelabeltask  = new outcome($deletelabeltask,  $messageslabel);

        $testoutcomemulti = $surgeonmultitask->get_outcome();
        $testoutcomepage  = $surgeonpagetask->get_outcome();
        $testoutcomeurl   = $surgeonurltask->get_outcome();
        $testoutcomebook  = $surgeonbooktask->get_outcome();
        $testoutcomelabel  = $surgeonlabeltask->get_outcome();

        $this->assertEquals($expectedoutcomemultitask->get_messages(), $testoutcomemulti->get_messages());
        $this->assertEquals($expectedoutcomepage->get_messages(), $testoutcomepage->get_messages());
        $this->assertEquals($expectedoutcomeurltask->get_messages(), $testoutcomeurl->get_messages());
        $this->assertEquals($expectedoutcomebooktask->get_messages(), $testoutcomebook->get_messages());
        $this->assertEquals($expectedoutcomelabeltask->get_messages(), $testoutcomelabel->get_messages());

        // Test reporter: CLI.
        $testreporter = new reporter(false, 0);

        // Test output displays for get_diagnosis_data().
        $testdiagnoses = $testreporter->get_diagnosis();
        $this->assertNotEquals('', $testdiagnoses);
        $this->assertTrue(strpos($testdiagnoses, get_string('diagnosis', 'tool_fix_delete_modules')) !== false);
        $this->assertTrue(strpos($testdiagnoses, get_string('symptoms', 'tool_fix_delete_modules')) !== false);

        // Test output displays for get_tables_report().
        $testreports = $testreporter->get_tables_report();
        $this->assertNotEquals('', $testreports);
        $this->assertTrue(strpos($testreports, get_string('report_heading', 'tool_fix_delete_modules')) !== false);
        $this->assertTrue(strpos($testreports, get_string('table_title_adhoctask', 'tool_fix_delete_modules')) !== false);

        // Test output displays for fix_tasks().
        $fixresults = $testreporter->fix_tasks();
        $this->assertNotEquals('', $fixresults);
        $this->assertTrue(strpos($fixresults, get_string('results', 'tool_fix_delete_modules')) !== false);
        $this->assertTrue(strpos($fixresults, get_string('result_messages', 'tool_fix_delete_modules')) !== false);

        // Run Adhoc Tasks.
        // Get Tasks from the scheduler and run them.
        $adhoctaskprecount = count($DB->get_records('task_adhoc'));
        $now = time();
        while (($task = \core\task\manager::get_next_adhoc_task($now + 120)) !== null) {
            // Check is a course_delete_modules adhoc task.
            $this->assertInstanceOf('\\core_course\\task\\course_delete_modules', $task);
            // Check faildelay is 0.
            $this->assertEquals(0, $task->get_fail_delay());
            // Check nextrun is equal or later than "now".
            $this->assertTrue($now >= $task->get_next_run_time());
            // Check adhoc task count.
            $this->assertCount($adhoctaskprecount, $DB->get_records('task_adhoc'));
            $task->execute(); // Not expecting any failed tasks.
            \core\task\manager::adhoc_task_complete($task);
            $this->assertCount(--$adhoctaskprecount, $DB->get_records('task_adhoc'));
            // Check Adhoc Task is now cleared.
            $this->assertEmpty($DB->get_records('task_adhoc', array('id' => $task->get_id())));
        }

        if ($exceptionthrown) {
            $this->expectException('moodle_exception');
            throw $exceptionthrown;
        } else {
            $this->assertTrue($exceptionthrown, "Expected Exception wasn't thrown for line 148");
        }

    }
}
