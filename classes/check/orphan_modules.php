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
 * Check API entry for orphaned course modules.
 *
 * @package     tool_fix_delete_modules
 * @category    check
 * @copyright   2026 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_fix_delete_modules\check;

use core\check\check;
use core\check\result;

/**
 * Status check: reports orphaned course modules.
 *
 * An "orphaned" course module is a row in course_modules with deletioninprogress=1
 * that has no matching \core_course\task\course_delete_modules adhoc task queued —
 * typically left behind when a worker is killed mid-execute (time-out, OOM, container
 * restart) before the framework can mark the task as failed. With no task left in the
 * queue, cron will never finish the deletion and the activity stays "deletion in
 * progress" on its course page indefinitely.
 *
 * Surfacing this on /report/status/index.php (core) and on the croncheck page
 * provided by tool_heartbeat means external monitoring picks it up automatically.
 *
 * @package     tool_fix_delete_modules
 * @category    check
 * @copyright   2026 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orphan_modules extends check {
    /**
     * Localised check name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('check_orphan_modules_name', 'tool_fix_delete_modules');
    }

    /**
     * Action link — drops the admin straight at the orphaned-modules section
     * of the plugin's index page, where the re-queue buttons live.
     *
     * @return \action_link|null
     */
    public function get_action_link(): ?\action_link {
        $url = new \moodle_url('/admin/tool/fix_delete_modules/index.php', null, 'orphan-modules');
        return new \action_link($url, get_string('pluginname', 'tool_fix_delete_modules'));
    }

    /**
     * Run the check.
     *
     * Uses the same orphan_module_list scan the GUI/CLI flows use, so the result
     * is always consistent with what the plugin's report page shows.
     *
     * @return result
     */
    public function get_result(): result {
        global $DB;

        $list    = new \tool_fix_delete_modules\orphan_module_list();
        $orphans = $list->get_orphan_modules();
        $count   = count($orphans);

        if ($count === 0) {
            return new result(
                result::OK,
                get_string('check_orphan_modules_ok', 'tool_fix_delete_modules')
            );
        }

        // The list is bounded (worst observed in production: 43) and the underlying
        // scan is the same one the report page runs, so we don't need a separate
        // lazy result class — just build the bullet list inline.
        $rows = [];
        foreach ($orphans as $cm) {
            $modname = $DB->get_field('modules', 'name', ['id' => $cm->module]) ?: ('module#' . $cm->module);
            $rows[]  = \html_writer::tag(
                'li',
                "cmid {$cm->id} — course {$cm->course} — {$modname} (instance {$cm->instance})"
            );
        }
        $details = \html_writer::tag('p', get_string('check_orphan_modules_details_intro', 'tool_fix_delete_modules'))
                 . \html_writer::tag('ul', implode('', $rows));

        return new result(
            result::WARNING,
            get_string('check_orphan_modules_warning', 'tool_fix_delete_modules', $count),
            $details
        );
    }
}
