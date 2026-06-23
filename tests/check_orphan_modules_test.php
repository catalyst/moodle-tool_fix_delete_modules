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

use core\check\result;
use tool_fix_delete_modules\check\orphan_modules;

/**
 * Tests for the orphan_modules status check.
 *
 * The check is a Check-API adaptor on top of \tool_fix_delete_modules\orphan_module_list,
 * so these tests focus on the OK / WARNING decision and the summary payload — the
 * underlying detection rules are covered by orphan_module_list_test.
 *
 * @package     tool_fix_delete_modules
 * @category    test
 * @copyright   2026 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class check_orphan_modules_test extends \advanced_testcase {
    /**
     * On a clean site, the check is OK.
     *
     * @covers \tool_fix_delete_modules\check\orphan_modules
     */
    public function test_ok_when_no_orphans(): void {
        $this->resetAfterTest(true);

        $check  = new orphan_modules();
        $result = $check->get_result();

        $this->assertSame(result::OK, $result->get_status());
        $this->assertNotEmpty($result->get_summary());
    }

    /**
     * A cm flagged deletioninprogress=1 with no queued task triggers WARNING and
     * the summary mentions the count.
     *
     * @covers \tool_fix_delete_modules\check\orphan_modules
     */
    public function test_warning_when_orphan_present(): void {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $page   = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);
        // Flag without queuing a task — this is the orphan state we want to detect.
        $DB->set_field('course_modules', 'deletioninprogress', 1, ['id' => $page->cmid]);

        $check  = new orphan_modules();
        $result = $check->get_result();

        $this->assertSame(result::WARNING, $result->get_status());
        $this->assertStringContainsString('1', $result->get_summary());
        // The detail block lists the offending cmid so an operator can act on it.
        $this->assertStringContainsString((string)$page->cmid, $result->get_details());
    }

    /**
     * A cm flagged AND covered by a queued task is in-flight, not orphaned —
     * the check should stay OK in that case.
     *
     * @covers \tool_fix_delete_modules\check\orphan_modules
     */
    public function test_ok_when_flagged_but_task_queued(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $page   = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);
        // The course_delete_module(..., true) call sets DIP=1 AND queues the task — the
        // canonical in-flight state, which is not an orphan.
        course_delete_module($page->cmid, true);

        $check  = new orphan_modules();
        $result = $check->get_result();

        $this->assertSame(result::OK, $result->get_status());
    }
}
