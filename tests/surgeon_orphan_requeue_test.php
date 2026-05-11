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

/**
 * Tests for surgeon::requeue_orphan_module().
 *
 * Covers the fix path for the orphan-cm cleanup flow: re-queueing a fresh
 * course_delete_modules adhoc task for a cm that was flagged for deletion
 * but lost its queued task. Goes through Moodle's own course_delete_module()
 * helper, so the resulting task is identical to one the activity Delete
 * button would have produced.
 *
 * @package     tool_fix_delete_modules
 * @category    test
 * @copyright   2026 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class surgeon_orphan_requeue_test extends \advanced_testcase {
    /**
     * Re-queueing a flagged orphan cm produces a new course_delete_modules adhoc task
     * and returns the success message.
     *
     * @covers \tool_fix_delete_modules\surgeon::requeue_orphan_module
     */
    public function test_requeue_creates_adhoc_task(): void {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $page   = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);

        // Set up the orphan state: flagged, no task.
        $DB->set_field('course_modules', 'deletioninprogress', 1, ['id' => $page->cmid]);
        $this->assertCount(0, \core\task\manager::get_adhoc_tasks('\core_course\task\course_delete_modules'));

        $messages = surgeon::requeue_orphan_module($page->cmid);

        // A task should now be queued.
        $tasks = \core\task\manager::get_adhoc_tasks('\core_course\task\course_delete_modules');
        $this->assertCount(1, $tasks);

        // The task's customdata should reference our cmid. Core's course_delete_module()
        // writes cms as a 0-indexed array, so we check the cm objects' id fields rather
        // than relying on the outer array key.
        $task = reset($tasks);
        $customdata = $task->get_custom_data();
        $cms = (array)$customdata->cms;
        $cmids = array_map(fn($cm) => (int)$cm->id, $cms);
        $this->assertContains((int)$page->cmid, $cmids);

        // The success message should be returned.
        $expected = get_string('outcome_orphan_module_requeued', 'tool_fix_delete_modules', $page->cmid);
        $this->assertContains($expected, $messages);
    }

    /**
     * Calling requeue against a non-existent cm returns the "already gone" message
     * (a benign no-op, not a hard error).
     *
     * @covers \tool_fix_delete_modules\surgeon::requeue_orphan_module
     */
    public function test_requeue_already_gone(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $bogus = 99999999;
        $messages = surgeon::requeue_orphan_module($bogus);

        $expected = get_string('outcome_orphan_module_already_gone', 'tool_fix_delete_modules', $bogus);
        $this->assertContains($expected, $messages);
        $this->assertCount(0, \core\task\manager::get_adhoc_tasks('\core_course\task\course_delete_modules'));
    }

    /**
     * Calling requeue against a cm that exists but is not flagged returns the
     * "not stuck" message and queues nothing.
     *
     * @covers \tool_fix_delete_modules\surgeon::requeue_orphan_module
     */
    public function test_requeue_not_stuck(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $page   = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);
        // Note: no DIP flag set — cm is healthy.

        $messages = surgeon::requeue_orphan_module($page->cmid);

        $expected = get_string('outcome_orphan_module_not_stuck', 'tool_fix_delete_modules', $page->cmid);
        $this->assertContains($expected, $messages);
        $this->assertCount(0, \core\task\manager::get_adhoc_tasks('\core_course\task\course_delete_modules'));
    }
}
