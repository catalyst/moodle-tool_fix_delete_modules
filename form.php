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
 * Form classes for tool_fix_delete_modules
 *
 * @package     tool_fix_delete_modules
 * @category    admin
 * @author      Brad Pasley <brad.pasley@catalyst-au.net>
 * @copyright   2022 Catalyst IT
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * fix_delete_modules_form
 *
 * @package    tool_fix_delete_modules
 * @author     Brad Pasley <brad.pasley@catalyst-au.net>
 * @copyright  2022 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fix_delete_modules_form extends moodleform {
    /**
     * definition()
     *
     * defines the form.
     */
    public function definition() {
        // Add elements to form.
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('submit', 'submit',  get_string('button_delete_mod_without_backup', 'tool_fix_delete_modules')
                                                .' #'.$this->_customdata['cmid']);
        $mform->addElement('hidden', 'action', 'fix_module');
        $mform->setType('action', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'cmname', $this->_customdata['cmname']);
        $mform->setType('cmname', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'taskid', $this->_customdata['taskid']);
        $mform->setType('taskid', PARAM_INT);
    }

}

/**
 * requeue_orphan_module_form — per-row "Permanently delete orphaned module #N" button.
 *
 * Used by the orphan-modules report section. Posts to fix_module.php with
 * action=requeue_orphan_module, which re-queues a fresh course_delete_modules
 * adhoc task for the given cmid via Moodle's course_delete_module() API.
 *
 * @package    tool_fix_delete_modules
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class requeue_orphan_module_form extends moodleform {
    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        $cmid    = $this->_customdata['cmid'];
        $label   = get_string('button_requeue_orphan_module', 'tool_fix_delete_modules');
        $icon    = '<i class="fa fa-rotate-right fix-orphan-requeue-icon" aria-hidden="true"></i> ';
        $btnhtml = '<button type="submit" class="btn btn-outline-orphan-requeue btn-sm">' . $icon . s($label) . '</button>';
        $mform->addElement('html', $btnhtml);

        $mform->addElement('hidden', 'action', 'requeue_orphan_module');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'cmname', $this->_customdata['cmname']);
        $mform->setType('cmname', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'taskid', 0);
        $mform->setType('taskid', PARAM_INT);
    }
}

/**
 * requeue_orphan_modules_all_form — "Permanently delete all orphaned modules" bulk button.
 *
 * @package    tool_fix_delete_modules
 * @copyright  2026 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class requeue_orphan_modules_all_form extends moodleform {
    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        $label   = get_string('button_requeue_orphan_modules_all', 'tool_fix_delete_modules');
        $icon    = '<i class="fa fa-rotate-right fix-orphan-requeue-icon" aria-hidden="true"></i> ';
        $btnhtml = '<button type="submit" class="btn btn-outline-orphan-requeue">' . $icon . s($label) . '</button>';
        $mform->addElement('html', $btnhtml);

        $mform->addElement('hidden', 'action', 'requeue_orphan_modules_all');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'cmid', 0);
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'cmname', 'all');
        $mform->setType('cmname', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'taskid', 0);
        $mform->setType('taskid', PARAM_INT);
    }
}

/**
 * separate_delete_modules_form Form Class.
 *
 * @package    tool_fix_delete_modules
 * @author     Brad Pasley <brad.pasley@catalyst-au.net>
 * @copyright  2022 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class separate_delete_modules_form extends moodleform {

    /**
     * definition()
     *
     * defines the form.
     */
    public function definition() {
        // Add elements to form.
        global $CFG;
        
        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('submit', 'submit',  get_string('button_separate_modules', 'tool_fix_delete_modules')
                                                ." (Task id:".$this->_customdata['taskid'].')');
        $mform->addElement('hidden', 'action', 'separate_module');
        $mform->setType('action', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'taskid', $this->_customdata['taskid']);
        $mform->setType('taskid', PARAM_INT);
    }

}
