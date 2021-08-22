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
 * The main mod_distributedquiz configuration form.
 *
 * @package     mod_distributedquiz
 * @copyright   2021 Tristan Call <tcall@zagmail.gonzaga.edu>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_distributedquiz
 * @copyright  2021 Tristan Call <tcall@zagmail.gonzaga.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_distributedquiz_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;
        // Get necessary information for later
        $courseid = required_param('course', PARAM_INT);
        $course = $DB->get_record('course', array('id' => $courseid), 'id, category');
        if ($course) { // Should always exist, but just in case ...
            $categoryid = $course->category;
        }

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('distributedquizname', 'mod_distributedquiz'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'distributedquizname', 'mod_distributedquiz');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }
        
        // Find all the required contexts
        $contexts = [
            context_course::instance($courseid),
            context_coursecat::instance($categoryid),
            context_system::instance(),
        ];
        $currentcat = 0;
        
        // Add the 'select category' field
        $mform->addElement('questioncategory', 'category', get_string('categoryfield', 'mod_distributedquiz'),
                array('contexts' => $contexts, 'top' => true, 'currentcat' => $currentcat, 'nochildrenof' => $currentcat));
        $mform->setType('category', PARAM_SEQUENCE);
        $mform->addHelpButton('category', 'categoryfield', 'mod_distributedquiz');
        
        

        // Adding the rest of mod_distributedquiz settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        //$mform->addElement('static', 'label1', 'distributedquizsettings', get_string('distributedquizsettings', 'mod_distributedquiz'));
        //$mform->addElement('header', 'distributedquizfieldset', get_string('distributedquizfieldset', 'mod_distributedquiz'));
        
        // Add Start date for the quiz
        $mform->addElement('header', 'timing', get_string('timing', 'mod_distributedquiz'));
        $mform->addElement('date_time_selector', 'timeopen', get_string('quizopen', 'mod_distributedquiz'));
        $mform->addElement('duration', 'creationduration', get_string('quizcreationduration', 'mod_distributedquiz'));
        $mform->addElement('duration', 'timelimit', get_string('timelimit', 'mod_distributedquiz'));
        
        // Get number
        $attemptoptions = array('0' => get_string('unlimited'));
        for ($i = 1; $i <= 40; $i++) {
            $attemptoptions[$i] = $i;
        }
        $mform->addElement('select', 'numberofquestions', get_string('numberofquestions', 'mod_distributedquiz'),
                $attemptoptions);
        $mform->addHelpButton('numberofquestions', 'numberofquestions', 'mod_distributedquiz');

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
