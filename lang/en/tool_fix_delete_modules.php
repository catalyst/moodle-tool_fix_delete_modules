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
 * Plugin strings are defined here.
 *
 * @package     tool_fix_delete_modules
 * @category    string
 * @author      Brad Pasley <brad.pasley@catalyst-au.net>
 * @copyright   2022 Catalyst IT

 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_fix_delete_modules\diagnosis;
use tool_fix_delete_modules\outcome;

$string['button_delete_mod_without_backup']                 = 'Permanently Delete Module';
$string['button_separate_modules']                          = 'Separate Into Individual Module Tasks';
$string['deletemodule_attemptfix']                          = 'Fixing... Attempting to fix a deleted module (Course module id: {$a}).';
$string['deletemodule_blogsdeleted']                        = 'Deleted blogs for module contextid {$a}.';
$string['deletemodule_calendareventsdeleted']               = '{$a} Calendar events deleted.';
$string['deletemodule_completionsdeleted']                  = 'Deleted Module Completion data for module cmid {$a}.';
$string['deletemodule_completioncriteriadeleted']           = 'Deleted Module Completion Criteria data for course {$a}.';
$string['deletemodule_contextdeleted']                      = 'Context data for module cmid $coursemoduleid contextid {$a}.';
$string['deletemodule_coursemoduledeleted']                 = 'Deleted Course Module record for module cmid {$a}.';
$string['deletemodule_error_dnecoursemodule']               = 'Course Module instance (cmid {$a}) doesn\'t exist. Attempting to delete other data.';
$string['deletemodule_error_dnemodcontext']                 = 'Context instance for course module (cmid {$a}) doesn\'t exist. Attempting to delete other data';
$string['deletemodule_error_failcmoduledelete']             = 'Delete Course Module record FAILED for module cmid {$a}.';
$string['deletemodule_error_failmodsectiondelete']          = 'Could NOT delete the module from section {$a}.';
$string['deletemodule_filesdeleted']                        = 'Files deleted for module contextid {$a}';
$string['deletemodule_gradeitemsdeleted']                   = '{$a} Grade items deleted.';
$string['deletemodule_modsectiondelete']                    = 'Deleted the module from section ({$a}).';
$string['deletemodule_returntomainpagesentence']            = 'Return to {$a} and check the status.';
$string['deletemodule_success']                             = 'SUCCESSFUL Deletion of Module and related data.';
$string['deletemodule_tagsdeleted']                         = 'Deleted Tag data for module contextid {$a}';
$string['displaypage']                                      = 'Check & Fix Delete Modules';
$string['displaypage-subtitle']                             = 'Check Modules';
$string['diagnosis']                                        = 'Diagnosis';
$string['diagnosis_explain_adhoctask_missing']              = 'This Adhoc task (id: {$a}) cannot be found.'
                                                             .' It is most likely that the adhoc task has been executed and completed successful.'
                                                             .' Please refresh the page and check again.';
$string['diagnosis_recommend_separate_tasks']               = 'This Adhoc task (id: {$a}) contains multiple course modules.'
                                                             .' Press the button below to separate these into multiple adhoc tasks.'
                                                             .' This will assist in reducing the complexity of the failed'
                                                             .' course_delete_modules task';
$string['diagnosis_recommend_clear_remnant_data']           = 'This course module (cmid: {$a}) cannot be backed up to the recycling bin and is failing to be deleted.'
                                                             .' Some data is missing, causing this course_delete_module adhoc task to fail to be completed.'
                                                             .' To force delete the module and attempt to wipe the remnant data of the module, click the button below.'
                                                             .' Then after the next adhoc task run, the adhoc task should complete successfully.';
$string['diagnosis']                                        = 'Diagnosis';
$string['error_dne_context']                                = 'Module ({$a}) record not found in the context table';
$string['error_dne_coursemodules']                          = 'Module ({$a}) record not found in the course module table';
$string['error_dne_files']                                  = 'Module ({$a}) records not found in the files table';
$string['error_dne_grades']                                 = 'Module ({$a}) records not found in the grades tables';
$string['error_dne_moduletable']                            = 'record not found in {$a} table';
$string['error_dne_moduleidinmoduletable']                  = 'Module ({$a}) ';
$string['error_dne_recyclebin']                             = 'Module ({$a}) records not found in the recyclebin table';
$string['error_actionnotfound']                             = 'Invalid form action: {$a}.';
$string['heading_coursemodules']                            = 'Course module(s)';
$string['outcome_separate_into_individual_task']            = 'Adhoc Task was successfully separated into individual tasks';
$string['outcome_separate_into_individual_task_fail']       = 'Adhoc Task was NOT successfully separated into individual tasks';
$string['outcome_separate_old_task_deleted']                = 'The original multi-module Adhoc Task was successfully deleted';
$string['outcome_separate_old_task_delete_fail']            = 'The original multi-module Adhoc Task was NOT successfully deleted';
$string['outcome_adhoc_task_record_advice']                 = 'Adhoc Task could not be found.';
$string['outcome_adhoc_task_record_rescheduled']            = 'Individual Module Adhoc Task(s) was successfully rescheduled';
$string['outcome_adhoc_task_record_rescheduled_failed']     = 'Individual Module Adhoc Task(s) was NOT successfully rescheduled';
$string['outcome_task_fix_successful']                      = 'Adhoc Task Separation was Successful';
$string['outcome_task_fix_fail']                            = 'Adhoc Task Separation Failed';
$string['outcome_module_table_record_deleted']              = 'The related module table record was successfully deleted';
$string['outcome_module_table_cminstance_not_found']        = 'The module\'s instanceid could NOT be found';
$string['outcome_course_module_id_not_found']               = 'The course_module id could NOT be found';
$string['outcome_course_module_table_record_not_found']     = 'The course_module record could NOT be found';
$string['outcome_course_module_table_record_deleted']       = 'The course_module record was successfully deleted';
$string['outcome_context_table_record_delete_fail']         = 'The course_module record was NOT successfully deleted';
$string['outcome_course_section_data_deleted']              = 'The course section was successfully deleted';
$string['outcome_course_section_data_delete_fail']          = 'The course section was NOT successfully deleted';
$string['outcome_course_section_data_fixed']                = 'The course section record was fixed';
$string['outcome_context_table_record_deleted']             = 'The module\'s context record was successfully deleted';
$string['outcome_context_id_not_found']                     = 'The module\'s context record could NOT be found';
$string['outcome_file_table_record_deleted']                = 'File records related to the module were successfully deleted';
$string['outcome_calendar_event_deleted']                   = 'Calendar event records related to the module were successfully deleted';
$string['outcome_grade_tables_records_deleted']             = 'Grades data related to the module were successfully deleted';
$string['outcome_blog_table_record_deleted']                = 'Blog data related to the module were successfully deleted';
$string['outcome_completion_table_record_deleted']          = 'Completion records related to the module were successfully deleted';
$string['outcome_completion_criteria_table_record_deleted'] = 'Completion criteria data related to the module were successfully deleted';
$string['outcome_tag_table_record_deleted']                 = 'Tag data related to the module were successfully deleted';
$string['outcome_module_fix_successful']                    = 'The module and its related data was successfully deleted';
$string['outcome_module_fix_fail']                          = 'The module and its related data was NOT successfully deleted';
$string['pluginname']                                       = 'Fix Delete Modules';
$string['privacy:metadata']                                 = 'The tool_fix_course_delete_modules plugin does not store any personal data.';
$string['report_heading']                                   = 'Report of related tables';
$string['results']                                          = 'Results';
$string['result_messages']                                  = 'Messages';
$string['returntomainlinklabel']                            = 'Fix Delete Modules Report page';
$string['table_title_adhoctask']                            = 'Adhoc tasks table';
$string['table_title_context']                              = 'Context table';
$string['table_title_coursemodules']                        = 'Course modules table';
$string['table_title_files']                                = 'Files table stats';
$string['table_title_grades']                               = 'Grades data';
$string['table_title_module']                               = 'Modules table';
$string['table_title_recyclebin']                           = 'Course Recycle bin table';
$string['separatetask_attemptfix']                          = 'Fixing... Attempting to separate clustered adhoc task (taskid: {$a}).';
$string['separatetask_error_dnfadhoctask']                  = 'Adhoc task course_delete_module (taskid {$a}) could not be found.';
$string['separatetask_error_failedtaskdelete']              = 'Could NOT delete Original course_delete_module task (id {$a}).';
$string['separatetask_originaltaskdeleted']                 = 'Original course_delete_module task (id {$a}) deleted.';
$string['separatetask_returntomainpagesentence']            = 'Refresh {$a} and check the status.';
$string['separatetask_taskscreatedcount']                   = '{$a} course_delete_module tasks created.';
$string['setting_manage_general_title']                     = 'Fix Delete Modules settings';
$string['setting_manage_general_desc']                      = 'Adjust which course_delete_module fix settings';
$string['setting_minimumfaildelay_title']                   = 'Minimum faildelay';
$string['setting_minimumfaildelay_desc']                    = 'Only show course_delete_module adhoc tasks with a minimum faildelay (in seconds)';
$string['success_none_found']                               = 'No course_delete_module tasks in queue (with minimal faildelay settings)';
$string['success_no_issues']                                = 'No course_delete_module tasks require fixing';
$string['symptom_multiple_modules_in_task']                 = 'There are multiple modules in this adhoc task';
$string['symptom_adhoc_task_record_missing']                = 'This adhoc task doesn\'t exist in the database';
$string['symptom_module_table_record_missing']              = 'This module\'s related database table is missing a record for this course module';
$string['symptom_course_module_table_record_missing']       = 'There is no course_modules table record for this course module';
$string['symptom_course_section_table_record_missing']      = 'There is no course_sections table record for this course module';
$string['symptom_context_table_record_missing']             = 'There is no context table record for this course module';
$string['symptom_good_no_issues']                           = 'GOOD! No issues.';
$string['symptoms']                                         = 'Symptoms';
$string['button_requeue_orphan_module']                     = 'Re-queue stalled deletion';
$string['button_requeue_orphan_modules_all']                = 'Re-queue all stalled deletions';
$string['cli_requeue_orphans_help']                         = 'Re-queue orphaned course modules — those with deletioninprogress=1 but no queued course_delete_modules adhoc task. Use --fix to actually re-queue.';
$string['heading_orphan_modules']                           = 'Orphaned course modules';
$string['orphan_modules_count']                             = '{$a} orphaned module(s) found';
$string['orphan_modules_explanation_action']                = 'Click <em>Re-queue stalled deletion</em> to queue a fresh course_delete_module() task; the next cron run will process the deletion normally.';
$string['orphan_modules_explanation_intro']                 = 'Each row below is an activity that is stuck showing "deletion in progress" on its course page — the deletion was started but no adhoc task remains to complete it.';
$string['orphan_modules_none_found']                        = 'No orphaned course modules — every flagged module has a queued task or is being processed normally.';
$string['orphan_modules_summary_heading']                   = 'Re-queue summary';
$string['outcome_orphan_module_already_gone']               = 'Course module {$a} had already been removed — no further action required.';
$string['outcome_orphan_module_fix_fail']                   = 'The course module could NOT be re-queued.';
$string['outcome_orphan_module_fix_successful']             = 'The course module was successfully re-queued for deletion.';
$string['outcome_orphan_module_not_stuck']                  = 'Course module {$a} was no longer flagged for deletion — no further action required.';
$string['outcome_orphan_module_requeue_failed']             = 'Course module {$a->cmid} could NOT be re-queued. Error: {$a->error}';
$string['outcome_orphan_module_requeued']                   = 'Course module {$a} was successfully re-queued for deletion.';
$string['outcome_orphan_modules_summary_all_fail_plural']   = 'None of the {$a} course modules were successfully re-queued.';
$string['outcome_orphan_modules_summary_all_fail_single']   = 'The course module could NOT be re-queued.';
$string['outcome_orphan_modules_summary_all_noop_plural']   = 'None of the {$a} course modules required action.';
$string['outcome_orphan_modules_summary_all_noop_single']   = 'The course module required no action.';
$string['outcome_orphan_modules_summary_all_ok_plural']     = 'All {$a} course modules were successfully re-queued for deletion.';
$string['outcome_orphan_modules_summary_all_ok_single']     = 'The course module was successfully re-queued for deletion.';
$string['outcome_orphan_modules_summary_partial']           = '{$a->ok} of {$a->total} course modules were successfully re-queued. {$a->fail} could NOT be re-queued and {$a->noop} required no action.';
$string['table_title_orphan_modules']                       = 'Orphaned course modules table';
$string['check_orphan_modules_name']                        = 'Orphaned course modules';
$string['check_orphan_modules_ok']                          = 'No orphaned course modules detected.';
$string['check_orphan_modules_warning']                     = '{$a} orphaned course module(s) detected. Re-queue them from the Fix Delete Modules page.';
$string['check_orphan_modules_details_intro']               = 'The following course modules are flagged with deletioninprogress=1 but have no queued course_delete_modules adhoc task. They were most likely left behind when a worker process was killed mid-execute. Open the plugin\'s report page to re-queue them.';
