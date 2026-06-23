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
 * Delete Modules
 *
 * @package     tool_fix_delete_modules
 * @category    admin
 * @author      Brad Pasley <brad.pasley@catalyst-au.net>
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__.'/classes/reporter.php');

// Gate: every action below mutates state (deletes modules, queues delete tasks,
// re-queues orphans). Restrict to admins by reusing the same admin_externalpage
// hook index.php uses; this enforces the moodle/site:config capability declared
// in settings.php and also calls require_login() internally.
admin_externalpage_setup('tool_fix_delete_modules');

use tool_fix_delete_modules\reporter;

// Retrieve parameters. taskid is optional because the orphan-requeue actions
// have no associated adhoc task — there is no row in mdl_task_adhoc to point at.
$action       = required_param('action', PARAM_ALPHANUMEXT);
$cmid         = required_param('cmid', PARAM_INT);
$modulename   = required_param('cmname', PARAM_ALPHAEXT);
$taskid       = optional_param('taskid', 0, PARAM_INT);

$prevurl   = new moodle_url('/admin/tool/fix_delete_modules/index.php');
$mainurl   = $prevurl;

$validactions = ['fix_module', 'requeue_orphan_module', 'requeue_orphan_modules_all'];
if (!in_array($action, $validactions, true)) {
    throw new moodle_exception('error_actionnotfound', 'tool_fix_delete_modules', $prevurl, $action);
}

require_sesskey();

$url = new moodle_url('/admin/tool/fix_delete_modules/fix_module.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'tool_fix_delete_modules'));

$minimumfaildelay = intval(get_config('tool_fix_delete_modules', 'minimumfaildelay'));
$reporter = new reporter(true, $minimumfaildelay);

if ($action === 'fix_module') {
    $PAGE->set_heading(get_string('pluginname', 'tool_fix_delete_modules') . " - deleting module");
    echo $OUTPUT->header();
    echo $reporter->fix_tasks([$taskid]);
} else if ($action === 'requeue_orphan_module') {
    $PAGE->set_heading(get_string('pluginname', 'tool_fix_delete_modules')
                       . ' - ' . get_string('button_requeue_orphan_module', 'tool_fix_delete_modules'));
    echo $OUTPUT->header();
    echo $reporter->requeue_orphan_modules([$cmid]);
} else { // Action: requeue_orphan_modules_all.
    $PAGE->set_heading(get_string('pluginname', 'tool_fix_delete_modules')
                       . ' - ' . get_string('button_requeue_orphan_modules_all', 'tool_fix_delete_modules'));
    echo $OUTPUT->header();
    echo $reporter->requeue_orphan_modules();
}

// Return to main page link.
$urlstring = html_writer::link($mainurl, get_string('returntomainlinklabel', 'tool_fix_delete_modules'));
echo get_string('deletemodule_returntomainpagesentence', 'tool_fix_delete_modules', $urlstring);

echo $OUTPUT->footer();
