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
 * CLI script for tool_fix_delete_modules.
 *
 * @package     tool_fix_delete_modules
 * @subpackage  cli
 * @author      Brad Pasley <brad.pasley@catalyst-au.net>
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../../config.php');
require(__DIR__.'/../classes/reporter.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/clilib.php');

use tool_fix_delete_modules\reporter;

// Get the cli options.
list($options, $unrecognized) = cli_get_params(array(
    'fix'              => false,
    'minimumfaildelay' => false,
    'taskids'          => false,
    'requeue-orphans'  => false,
    'help'             => false,
),
array(
    'f' => 'fix',
    'm' => 'minimumfaildelay',
    't' => 'taskids',
    'r' => 'requeue-orphans',
    'h' => 'help'
));

$help =
"
Checks and fixes incomplete course_delete_modules adhoc tasks.

Please include a list of options and associated actions.

Avoid executing the script when another user may simultaneously edit any of the
course modules being checked (recommended to run in mainenance mode).

Options:
-t, --taskids            List adhoc tasks (by their id) that need to be
                         checked/fixed.
                         (comma-separated values or * for all).
                         Only required for fixing any tasks.
-m, --minimumfaildelay   Filter by the minimum faildelay field (in seconds)
-f, --fix                Fix the incomplete course_delete_module adhoc tasks.
                         To fix tasks '--taskids' must be explicitly
                         specified which modules.
-r, --requeue-orphans    List orphaned course modules: cms with deletioninprogress=1
                         but no queued course_delete_modules adhoc task.
                         Use with --fix to actually re-queue them via Moodle's
                         course_delete_module(\$cmid, true) helper.
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/tool/fix_delete_modules/cli/fix_course_delete_modules.php --taskids=*
\$sudo -u www-data /usr/bin/php admin/tool/fix_delete_modules/cli/fix_course_delete_modules.php --taskids=2,3,4 --fix
\$sudo -u www-data /usr/bin/php admin/tool/fix_delete_modules/cli/fix_course_delete_modules.php --requeue-orphans
\$sudo -u www-data /usr/bin/php admin/tool/fix_delete_modules/cli/fix_course_delete_modules.php --requeue-orphans --fix
";

if ($unrecognized) {
    $unrecognized = implode("\n\t", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_writeln($help);
    die();
}

// The --requeue-orphans flag short-circuits the task-driven flow: list (or re-queue)
// orphaned cms — those flagged deletioninprogress=1 with no matching task.
if ($options['requeue-orphans']) {
    require_once(__DIR__ . '/../classes/orphan_module_list.php');
    require_once(__DIR__ . '/../classes/surgeon.php');

    $orphanlist = new \tool_fix_delete_modules\orphan_module_list();
    $orphans = $orphanlist->get_orphan_modules();

    if (empty($orphans)) {
        cli_writeln(get_string('orphan_modules_none_found', 'tool_fix_delete_modules'));
        die();
    }

    cli_writeln(count($orphans) . ' orphaned course module(s) found:');
    cli_writeln(sprintf('%-8s %-10s %-10s %-15s', 'cmid', 'courseid', 'instance', 'modid'));
    foreach ($orphans as $cm) {
        cli_writeln(sprintf('%-8d %-10d %-10d %-15d', $cm->id, $cm->course, $cm->instance, $cm->module));
    }

    if (!$options['fix']) {
        cli_writeln('');
        cli_writeln('Add --fix to re-queue these via course_delete_module($cmid, true).');
        die();
    }

    cli_writeln('');
    cli_writeln('Re-queueing...');

    // Moodle's course_delete_module() writes $USER->id into the task customdata as
    // userid/realuserid. In a fresh CLI context $USER is the noreply user (id=0),
    // and the adhoc task runner skips userid=0 tasks silently. Set up the main
    // admin so the queued tasks have a runnable owner.
    \core\cron::setup_user(get_admin());

    foreach ($orphans as $cm) {
        $messages = \tool_fix_delete_modules\surgeon::requeue_orphan_module((int)$cm->id);
        foreach ($messages as $msg) {
            cli_writeln('  ' . $msg);
        }
    }
    cli_writeln('Done. Run cron (or "php admin/cli/adhoc_task.php --execute');
    cli_writeln('--classname=\'\\\\core_course\\\\task\\\\course_delete_modules\'") to drain the queue.');
    die();
}

$minimumfaildelay = 60; // Default to 60 seconds to exclude any tasks which haven't run yet.
if ($options['minimumfaildelay'] !== false) {
    if (is_numeric($options['minimumfaildelay'])) {
        $minimumfaildelay = intval($options['minimumfaildelay']);
    }
}

$taskids = preg_split('/\s*,\s*/', $options['taskids'], -1, PREG_SPLIT_NO_EMPTY);
if (in_array('*', $taskids) || empty($taskids)) {
    $where = "WHERE classname = :classname";
    $params = array('classname' => '\core_course\task\course_delete_modules');
} else {
    list($sql, $params) = $DB->get_in_or_equal($taskids, SQL_PARAMS_NAMED, 'id');
    $params += array('classname' => '\core_course\task\course_delete_modules');
    $where = "WHERE classname = :classname AND id ". $sql;
}

// Require --fix to also have the --modules param (with specific modules listed).
$isfix                     = $options['fix'];
$isfixwithmodulesspecified = $isfix && $options['taskids'] && !empty($params);
if ($isfix && !$isfixclusteredtask && !$isfixwithmodulesspecified) {
    cli_error("fix_course_delete_modules.php '--fix' requires '--taskids=[comma separated taskids]'.");
    cli_writeln($help);
    die();
}

$taskcount = $DB->get_field_sql('SELECT count(id) FROM {task_adhoc} '. $where, $params);

if (!$taskcount && !$options['fix']) { // If "fix" is included, attempt to resolve.
    cli_error('No course_delete_module adhoc tasks found');
}

$coursemoduledeletetasks = \core\task\manager::get_adhoc_tasks('\core_course\task\course_delete_modules');
$totaltaskscount = count($coursemoduledeletetasks);

echo "Checking $taskcount/$totaltaskscount course_delete_module adhoc tasks...\n\n";

$taskids = ($totaltaskscount == $taskcount) ? array() : $taskids;

if ($totaltaskscount == 0) {
    echo "\n...No course_delete_module adhoc tasks found.\n\n";
    die();
}

$problems = array();
$allerrors = array();

$reporter = new reporter(false, $minimumfaildelay, $taskids);

// Check for errors.
$diagnoses = $reporter->get_diagnosis();

if ($diagnoses !== '' || !empty($options['fix'])) {
    echo $diagnoses;
    if (!empty($options['fix'])) { // Fix if 'fix' param settings made correctly.
        $outcomes = $reporter->fix_tasks($taskids);
        echo $outcomes;
    }
} else {
    echo "\n... No issues found (minimum faildelay filter: $minimumfaildelay seconds)\n\n";
}
