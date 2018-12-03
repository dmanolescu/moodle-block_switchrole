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
 * Switch role block.
 *
 * @package    block_switchrole
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @author     Dorel Manolescu <manolescu.dorel@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_switchrole extends block_list {

    public function init() {
        $this->title = get_string('blockname', 'block_switchrole');
    }

    public function applicable_formats() {
        return array('site' => true, 'course' => true, 'my' => false);
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function get_content() {
        global $USER, $OUTPUT, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $courseid = $this->page->course->id;
        if ($courseid <= 0) {
            $courseid = SITEID;
        }

        if (empty($this->instance->pageid)) {
            $this->instance->pageid = SITEID;
        }
        $context = context_course::instance($this->page->course->id);

        if (is_role_switched($courseid)) {
            if ($role = $DB->get_record('role', array('id' => $USER->access['rsw'][$context->path]))) {
                $icon = $OUTPUT->pix_icon('a/logout', get_string('switchrolereturn'));
                $rolereturn = new stdClass();
                $rolereturn->url = new moodle_url('/course/switchrole.php', array(
                        'id' => $courseid,
                        'sesskey' => sesskey(),
                        'switchrole' => 0,
                        'returnurl' => $this->page->url->out_as_local_url(false)
                ));

                $this->content->items[] = $icon.
                        '<a href="' . $rolereturn->url->out() . '">' . get_string('switchrolereturn') . '</a>';
            }

        } else {
            // Build switch role link.
            $roles = get_switchable_roles($context);
            if (is_array($roles) && (count($roles) > 0)) {
                $icon = $OUTPUT->pix_icon('i/switchrole', get_string('pluginname', 'block_switchrole'));
                $switchrole = new stdClass();
                $switchrole->url = new moodle_url('/course/switchrole.php', array(
                        'id' => $courseid,
                        'switchrole' => -1,
                        'returnurl' => $this->page->url->out_as_local_url(false)
                ));
                $this->content->items[] = $icon.
                        '<a href="' . $switchrole->url->out() . '">' . get_string('switchroleto') . '</a>';
            }
        }
        return $this->content;
    }
}
