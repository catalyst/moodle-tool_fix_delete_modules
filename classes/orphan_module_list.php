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
 * Class to enumerate orphaned course modules: rows in course_modules with
 * deletioninprogress = 1 that have no matching \core_course\task\course_delete_modules
 * adhoc task queued.
 *
 * @package     tool_fix_delete_modules
 * @category    admin
 * @copyright   2026 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_fix_delete_modules;

/**
 * List of orphaned course modules — flagged for deletion but no queued task.
 *
 * @package     tool_fix_delete_modules
 * @category    admin
 * @copyright   2026 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orphan_module_list {
    /** @var \stdClass[] $orphanmodules course_modules rows that are stuck without a queued task, keyed by cmid. */
    private $orphanmodules;

    /**
     * Constructor — populates the orphan list immediately.
     */
    public function __construct() {
        $this->set_orphan_modules();
    }

    /**
     * Get the array of orphaned course module records, keyed by cmid.
     *
     * @return \stdClass[]
     */
    public function get_orphan_modules() {
        return $this->orphanmodules;
    }

    /**
     * Get just the cmids of orphaned modules.
     *
     * @return int[]
     */
    public function get_cmids() {
        return array_keys($this->orphanmodules);
    }

    /**
     * Whether any orphaned modules exist.
     *
     * @return bool
     */
    public function has_orphans() {
        return !empty($this->orphanmodules);
    }

    /**
     * Build the list:
     *   1. Find every cm with deletioninprogress=1.
     *   2. Walk every queued course_delete_modules adhoc task and collect referenced cmids.
     *   3. Stuck cms not referenced by any task = orphans.
     *
     * Avoids JSON_EXTRACT for cross-DB portability (MySQL/PostgreSQL).
     */
    private function set_orphan_modules() {
        global $DB;

        $this->orphanmodules = [];

        // Step 1: stuck cms.
        $stuck = $DB->get_records('course_modules', ['deletioninprogress' => 1], 'course, id');
        if (empty($stuck)) {
            return;
        }

        // Step 2: cmids referenced by any queued task.
        $referenced = [];
        $tasks = \core\task\manager::get_adhoc_tasks('\core_course\task\course_delete_modules');
        foreach ($tasks as $task) {
            $customdata = $task->get_custom_data();
            if (empty($customdata) || empty($customdata->cms)) {
                continue;
            }
            foreach ($customdata->cms as $cm) {
                if (isset($cm->id)) {
                    $referenced[(int)$cm->id] = true;
                }
            }
        }

        // Step 3: subtract.
        foreach ($stuck as $cm) {
            if (!isset($referenced[(int)$cm->id])) {
                $this->orphanmodules[(int)$cm->id] = $cm;
            }
        }
    }
}
