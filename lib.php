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
 * Plugin callbacks for tool_fix_delete_modules.
 *
 * @package     tool_fix_delete_modules
 * @copyright   2026 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Register status checks with the Moodle Check API.
 *
 * Discovered automatically by \core\check\manager::get_status_checks() (M3.9+) so
 * the orphan-cm check appears on /report/status/index.php and is picked up by
 * tool_heartbeat's croncheck.php. On Moodle &lt;3.9 the Check API doesn't exist
 * and this callback is simply never invoked.
 *
 * @return \core\check\check[]
 */
function tool_fix_delete_modules_status_checks(): array {
    return [
        new \tool_fix_delete_modules\check\orphan_modules(),
    ];
}
