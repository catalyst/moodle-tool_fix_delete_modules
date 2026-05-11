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
 * Tests for the orphan_module_list class.
 *
 * Covers the data-source layer for the orphan-cm cleanup flow: scanning
 * mdl_course_modules for deletioninprogress=1 rows and subtracting any
 * cmids referenced by a queued course_delete_modules adhoc task.
 *
 * @package     tool_fix_delete_modules
 * @category    test
 * @copyright   2026 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class orphan_module_list_test extends \advanced_testcase {
    /**
     * On a clean site, orphan_module_list is empty.
     *
     * @covers \tool_fix_delete_modules\orphan_module_list
     */
    public function test_empty_when_no_stuck_modules(): void {
        $this->resetAfterTest(true);

        $list = new orphan_module_list();

        $this->assertFalse($list->has_orphans());
        $this->assertSame([], $list->get_orphan_modules());
        $this->assertSame([], $list->get_cmids());
    }

    /**
     * A cm with deletioninprogress=1 and no matching adhoc task is reported as an orphan.
     *
     * @covers \tool_fix_delete_modules\orphan_module_list
     */
    public function test_flagged_cm_with_no_task_is_orphan(): void {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $page   = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);

        // Flag the cm without queuing a task — exactly the state we want to detect.
        $DB->set_field('course_modules', 'deletioninprogress', 1, ['id' => $page->cmid]);

        $list = new orphan_module_list();

        $this->assertTrue($list->has_orphans());
        $this->assertSame([$page->cmid], $list->get_cmids());
        $orphans = $list->get_orphan_modules();
        $this->assertArrayHasKey($page->cmid, $orphans);
        $this->assertEquals(1, $orphans[$page->cmid]->deletioninprogress);
    }

    /**
     * A cm with deletioninprogress=1 AND a matching queued task is NOT an orphan —
     * its deletion is in-flight and the existing flow handles it.
     *
     * @covers \tool_fix_delete_modules\orphan_module_list
     */
    public function test_flagged_cm_with_matching_task_is_not_orphan(): void {
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $page   = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);

        // The course_delete_module(..., true) call sets DIP=1 AND queues the task — the canonical path.
        course_delete_module($page->cmid, true);

        $list = new orphan_module_list();

        $this->assertFalse($list->has_orphans(), 'A cm referenced by a queued task should not be reported as an orphan.');
    }

    /**
     * A mix of orphans and in-flight cms returns only the orphans.
     *
     * @covers \tool_fix_delete_modules\orphan_module_list
     */
    public function test_mixed_set_returns_only_orphans(): void {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();

        // Orphan #1 — flagged, no task.
        $orphan1 = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);
        $DB->set_field('course_modules', 'deletioninprogress', 1, ['id' => $orphan1->cmid]);

        // Orphan #2 — flagged, no task.
        $orphan2 = $this->getDataGenerator()->create_module('label', ['course' => $course->id]);
        $DB->set_field('course_modules', 'deletioninprogress', 1, ['id' => $orphan2->cmid]);

        // In-flight — flagged AND task queued via the canonical Moodle helper.
        $inflight = $this->getDataGenerator()->create_module('url', ['course' => $course->id]);
        course_delete_module($inflight->cmid, true);

        $list = new orphan_module_list();
        $cmids = $list->get_cmids();
        sort($cmids);

        $expected = [$orphan1->cmid, $orphan2->cmid];
        sort($expected);

        $this->assertSame($expected, $cmids);
        $this->assertNotContains($inflight->cmid, $cmids);
    }
}
