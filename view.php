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
 * Prints an instance of mod_distributedquiz.
 *
 * @package     mod_distributedquiz
 * @copyright   2021 Tristan Call <tcall@zagmail.gonzaga.edu>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$d  = optional_param('d', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('distributedquiz', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('distributedquiz', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($d) {
    $moduleinstance = $DB->get_record('distributedquiz', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('distributedquiz', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_distributedquiz'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);



$PAGE->set_url('/mod/distributedquiz/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);


$functions = new mod_distributedquiz_functions();
$functions->send_notification(2);

//echo $OUTPUT->header();
//echo $OUTPUT->box('This is working!!');
//echo $OUTPUT->footer();

/*
 * TODO something is messed up with quiz_data?
 */
$output = $PAGE->get_renderer('mod_distributedquiz');
$submissionwidget = new distributedquiz_data(new stdClass());
echo $output->header();
echo $output->render($submissionwidget);
//echo $output->footer();



