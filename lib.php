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
 * Library of interface functions and constants.
 *
 * @package     mod_distributedquiz
 * @copyright   2021 Tristan Call <tcall@zagmail.gonzaga.edu>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function distributedquiz_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_distributedquiz into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_distributedquiz_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function distributedquiz_add_instance($moduleinstance, $mform = null) {
    global $DB;

    echo("<script>console.log(". json_encode($moduleinstance, JSON_HEX_TAG) .");</script>");
    echo("<script>console.log(". json_encode($mform, JSON_HEX_TAG) .");</script>");
    
    $moduleinstance->timecreated = time();
    $category = explode(",", $moduleinstance->category);
    $moduleinstance->category = $category[0];

    $id = $DB->insert_record('distributedquiz', $moduleinstance);
    
    
    // TODO proper preprocessing on number of questions
    $numberofquestions = $moduleinstance->numberofquestions;
    if (is_string($numberofquestions)) {
        $numberofquestions = 2;
    }
    
    // TODO call function to create ad hoc tasks
    $func = new mod_distributedquiz_quiz_creation_functions;
    $func->set_all_future_quizzes($moduleinstance->id, 
            $moduleinstance->startcreation, 
            $moduleinstance->creationduration, 
            $numberofquestions
    );

    return $id;
}

/**
 * Updates an instance of the mod_distributedquiz in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_distributedquiz_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function distributedquiz_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('distributedquiz', $moduleinstance);
}

/**
 * Removes an instance of the mod_distributedquiz from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function distributedquiz_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('distributedquiz', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('distributedquiz', array('id' => $id));

    return true;
}

/**
 * Is a given scale used by the instance of mod_distributedquiz?
 *
 * This function returns if a scale is being used by one mod_distributedquiz
 * if it has support for grading and scales.
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given mod_distributedquiz instance.
 */
function distributedquiz_scale_used($moduleinstanceid, $scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('distributedquiz', array('id' => $moduleinstanceid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of mod_distributedquiz.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_distributedquiz instance.
 */
function distributedquiz_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('distributedquiz', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given mod_distributedquiz instance.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function distributedquiz_grade_item_update($moduleinstance, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $moduleinstance->grade;
        $item['grademin']  = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('/mod/distributedquiz', $moduleinstance->course, 'mod', 'mod_distributedquiz', $moduleinstance->id, 0, null, $item);
}

/**
 * Delete grade item for given mod_distributedquiz instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function distributedquiz_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('/mod/distributedquiz', $moduleinstance->course, 'mod', 'distributedquiz',
                        $moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Update mod_distributedquiz grades in the gradebook.
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function distributedquiz_update_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();
    grade_update('/mod/distributedquiz', $moduleinstance->course, 'mod', 'mod_distributedquiz', $moduleinstance->id, 0, $grades);
}
