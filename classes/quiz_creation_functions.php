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

/*
 * Author: Tristan Call
 * Date Created: 1/25/21
 * Last Updated: 8/10/21
 */
require_once(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../../../course/modlib.php');
defined('MOODLE_INTERNAL') || die();

class mod_distributedquiz_quiz_creation_functions {
    
    public static function set_future_quiz_creation($runtime, $moduleid) {
        $task = new generate_quiz();
        $task->set_custom_data(array(
           'course_module_id' => $moduleid,
        ));
        $task->set_next_run_time($runtime);
        \core\task\manager::queue_adhoc_task($task);
    }
    
    public static function fully_define_quiz($coursemoduleid) {
        global $DB;
        // Get required information from the distributed quiz instance
        $sql = 'select course, section, groupingid, instance '
                . 'from {course_modules} ' 
                . 'where id = ?;';
        $records = $DB->get_record_sql($sql, array('id' => $coursemoduleid));
        $course = $records->course;
        $section = $records->section;
        $groupingid = $records->groupingid;
        $instance = $records->instance;
        
        $newmodule = self::create_quiz($course, $section, $groupingid);
                
        // update subquizzes table
        $DB->insert_record('subquizzes', array(
            'distributedquiz_id' => $instance,
            'quiz_id' => $newmodule->id,
            'creation_time' => $newmodule->timecreated,
        ));
        //TODO assign questions to quiz
        //TODO update questions used
    }
    
    /*
     * Function to create a quiz in a course/section
     * @params courseid
     * @params section - Should be the same as the corresponding distributedquiz
     * @return updated module object
     */
    public static function create_quiz($courseid, $section, $groupnum = null) {
        global $DB;
        $endtime = 125000000;
        $moduleid = $DB->get_record_sql('SELECT id FROM {modules} WHERE name = ?;',
                array('name' => 'quiz')); 
        
        $module = self::define_quiz_form($endtime, $courseid, $moduleid->id, $section, $groupnum);
        
        $course = $DB->get_record('course', array('id' => $courseid));
        $newmodule = add_moduleinfo($module, $course);
        
        
        return $newmodule;
        
    }
    
    /*
     * Creates a quiz object to pass to quiz_add_instance
     * @params $starttime
     * @params $endtime
     * @params $course(id)
     * @params $coursemodule
     * @return quiz stdClass
     */
    public static function define_quiz_form($endtime, $course, $moduleid, $section, $groupnum = null) {
        $quiz = new stdClass();        
        date_default_timezone_set('PST');
        $name = date('y:m:d h:m:s');
        echo("<script>console.log(". json_encode($name, JSON_HEX_TAG) .");</script>");
        $quiz->name = $name;
        $quiz->intro = "";
        $quiz->introformat = 1;
        $quiz->course = $course;
        $quiz->timeopen = time();
        $quiz->timeclose = $endtime;
        $quiz->timelimit = 0;
        $quiz->overduehandling = 'autosubmit';
        $quiz->graceperiod = 0;
        $quiz->preferredbehaviour = 'deferredfeedback';
        $quiz->canredoquestions = 0;
        $quiz->attempts = 1;
        $quiz->attemptonlast = 0;
        $quiz->grademethod = 1;
        $quiz->decimalpoints = 2;
        $quiz->questiondecimalpoints = -1;
        $quiz->reviewattempt = 69904;
        $quiz->reviewcorrectness = 'autosubmit';
        $quiz->reviewmarks = 4368;
        $quiz->reviewspecificfeedback = 4368;
        $quiz->reviewregularfeedback = 4368;
        $quiz->reviewrightanswer = 4368;
        $quiz->reviewoverallfeedback = 4368;
        $quiz->questionsperpage = 1;
        $quiz->navmethod = 'free';
        $quiz->shuffleanswers = 1;
        $quiz->sumgrades = 0.0;
        $quiz->grade = 10.0;
        $quiz->timecreated = 0; //overwritten in quiz_add_instance
        $quiz->timemodified = 0;
        // When inserting quiz the code assigns quiz->password from quiz->quizpassword
        $quiz->quizpassword = "";
        $quiz->subnet = '';
        $quiz->browsersecurity = '-';
        $quiz->delay1 = 0;
        $quiz->delay2 = 0;
        $quiz->showuserpicture = 0;
        $quiz->showblocks = 0;
        $quiz->completionattemptsexhausted = 0;
        $quiz->completionpass = 0;
        $quiz->completionminattempts = 0;
        $quiz->allowofflineattempts = 0;
        $quiz->modulename = 'quiz';
        
        $quiz->module = $moduleid;
        $quiz->visible = 1;
        $quiz->visibleoncoursepage = 1;
        $quiz->visibleold = 1;
        $quiz->section = $section;
        // Add group number if applicable
        if ($groupnum != null) {
            $quiz->groupmode = 1;
            $quiz->groupingid = $groupnum;
        }
        return $quiz;
       
    }
    
    public static function assign_questions_to_quiz($quizid) {
        
    }
    
    /*
     * Gets a random, unused array value 
     * @param valid_options = valid questions, array of
     * @param used = used questions, array of
     * @return $chosen
     */
    public static function get_random_nonused_option($valid_options, $used) {
        $nonused = array_diff_assoc($valid_options, $used);
        $chosen = array_rand($nonused);
        return $chosen;
    }
    
    /*
     * Gets a random, unused question
     * @param distributed quiz id
     * @param distributed quiz category
     */
    public static function get_random_question($id, $category) {
        global $DB;
        
        // Grab options and used options
        $sql = "SELECT id
                FROM question
                WHERE category = ?";
        $valid_options = $DB->get_records_sql($sql, array($category));
        $sql = "SELECT quiz_id
                FROM quiz_creation_times
                WHERE id = ?";
        $used = $DB->get_records_sql($sql, array($id));
        
        // Grab the right quiz
        $chosen = self::get_random_nonused_option($valid_options, $used);
        $sql = "SELECT *
                FROM question
                WHERE id = ?";
        $chosen = $DB->get_records_sql($sql, array($chosen));
        return $chosen;
        
    }
    // TODO TEST
    // Need to wait until create database first

    /* 
     * Generates random quiz creation times based on a number of factors
     * @param startcreation hour
     * @param endcreation
     * @param makequiztime
     * @param numquestions
     * @return list of all quiz creation times
     */
    public static function determine_creation_times($startcreation, $endcreation, $makequiztime, $numquestions) {
        
    }
    
    private static function randomize_creation_times() {
            
    }
    
}
    