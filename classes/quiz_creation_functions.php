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
 * Author: Madison Call
 * Date Created: 1/25/21
 * Last Updated: 8/10/21
 */
require_once(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../../../course/modlib.php');
defined('MOODLE_INTERNAL') || die();

class mod_distributedquiz_quiz_creation_functions {
    
    /*
     * Sets up numquestions number of quizzes to occur in the future
     * @param course moduleid of associated distributedquiz
     * @param $startcreation time
     * @param creationduration
     * @param numquestions
     */
    public static function set_all_future_quizzes($moduleid, $startcreation, $creationduration, $numquestions) {
        $timezone = core_date::get_user_timezone_object();
        
        // Calculate times for quizzes
        $func = new mod_distributedquiz_functions;
        $times = $func->determine_creation_times($startcreation, $creationduration, $numquestions, $timezone);
        
        // Set tasks to create them all
        for ($i = 0; $i < $numquestions; $i++) {
            self::set_future_quiz_creation($times[$i], $moduleid);
        }
    }
    
    /*
     * Sets an ad hoc generate_quiz task to occur
     * @param runtime
     * @param course moduleid
     * Note: Schedules a task
     */
    public static function set_future_quiz_creation($runtime, $moduleid) {
        $task = new \mod_distributedquiz\task\generate_quiz();
        $task->set_custom_data(array(
           'course_module_id' => $moduleid,
        ));
        $task->set_next_run_time($runtime);
        \core\task\manager::queue_adhoc_task($task);
    }
    
    /*
     * This function creates a quiz from a distributedquiz, updates relevant tables,
     * and adds questions to those quizzes.
     * @param coursemoduleid for the distributedquiz 
     */
    public static function fully_define_quiz($coursemoduleid) {
        global $DB;
        // Seed random number generator
        // Get required information from the distributed quiz instance
        $sql = 'select course, section, availability, instance '
                . 'from {course_modules} ' 
                . 'where id = ?;';
        $records = $DB->get_record_sql($sql, array('id' => $coursemoduleid));
        $course = $records->course;
        $section_record = $DB->get_record('course_sections',
            array('id' => $records->section), '*', IGNORE_MISSING);
        $section = $section_record->section;
        $availability = $records->availability;
        $instance = $records->instance;
        // Grab the quiz duration
         $distributedrecord= $DB->get_record_sql('SELECT name, timelimit FROM {distributedquiz} WHERE id = ?',
                array('id' => $instance));
        
        $newmodule = self::create_quiz($distributedrecord->name, $distributedrecord->timelimit, $course, $section, $availability);
                
        // update subquizzes table
        $DB->insert_record('subquizzes', array(
            'distributedquiz_id' => $instance,
            'quiz_id' => $newmodule->id,
            'creation_time' => $newmodule->timecreated,
        ));
        
        self::assign_questions_to_quiz($instance, $newmodule->id);
        
        // Assign quiz to a grade category
        self::set_quiz_grade_category($newmodule->id, $distributedrecord->name, $course);
        
        return $newmodule->id;
    }
    
    /*
     * Function to create a quiz in a course/section
     * @params name of distributed quiz
     * @params quizduration
     * @params courseid
     * @params section - Should be the same as the corresponding distributedquiz
     * @params availability
     * @return updated module object
     */
    public static function create_quiz($name, $quizduration, $courseid, $section, $availability) {
        global $DB;
        
        $moduleid = $DB->get_record_sql('SELECT id FROM {modules} WHERE name = ?;',
                array('name' => 'quiz')); 
        
        $module = self::define_quiz_form($name, $quizduration, $courseid, $moduleid->id, $section, $availability);
        
        $course = $DB->get_record('course', array('id' => $courseid));
        $newmodule = add_moduleinfo($module, $course);
        
        
        return $newmodule;
        
    }
    
    /*
     * Creates a quiz object to pass to add_moduleinfo
     * @params name of distributed quiz
     * @params $quizduration
     * @params $course(id)
     * @params $coursemoduleid
     * @params $section of the course
     * @params $availability 
     * @return quiz stdClass
     */
    public static function define_quiz_form($name, $quizduration, $course, $moduleid, $section, $availability) {
        $quiz = new stdClass();        
        date_default_timezone_set('PST');
        $quizname = strval($name) . ': ' . strval(date('y:m:d h:m:s'));
        //echo("<script>console.log(". json_encode($quizname, JSON_HEX_TAG) .");</script>");
        $quiz->name = $quizname;
        $quiz->intro = "";
        $quiz->introformat = 1;
        $quiz->course = $course;
        $quiz->timeopen = time();
        $quiz->timeclose = $quiz->timeopen + $quizduration;
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
        // Sumgrades is points in the quiz, grade refers to how many points 
        // the entire quiz is to the best of my understanding
        $quiz->sumgrades = 1.0;
        $quiz->grade = 1.0;
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
        // Make grouping id be the same as the one passed in
        $quiz->availability = $availability;
        
        return $quiz;
       
    }
    
    /*
     * Assigns questions to a quiz formed from a distributed quiz
     * @param quizid
     */
    public static function assign_questions_to_quiz($dbquizid, $quizid) {
        global $DB;
        // Grab an unused question
        $category = self::get_distributed_quiz_category($dbquizid);
        $question = self::get_random_question($dbquizid, $category);
        
        // Insert into appropriate databases
        if ($question != null) {
            $DB->insert_record('used_questions', array('distributedquiz_id' => $dbquizid,
                'question_id' => $question->id));
            $DB->insert_record('quiz_slots', array(
                'quizid' => $quizid,
                'questionid' => $question->id,
                'questioncategoryid' => $category,
                'slot' => 1,
                'requireprevious' => 0,
                'maxmark' => 1,
                'page' => 1,
            ));
        }
        else {
            echo("<script>console.log(". json_encode("Error, no more questions", JSON_HEX_TAG) .");</script>");
        }
    }
    
    /*
     * Gets the category of a given distributedquiz coursemoduleid
     * @param $coursemoduleid
     */
    public static function get_distributed_quiz_category($id) {
        global $DB;
        $sql = 'select dq.category '
                . 'from {distributedquiz} dq '
                . 'where dq.id = ?';
        $category = $DB->get_record_sql($sql, (array('id' => $id)));
        return $category->category;
    }
    
    /*
     * Gets a random, unused array value 
     * @param valid_options = valid questions, array of
     * @param used = used questions, array of
     * @return $chosen
     */
    public static function get_random_nonused_option($validoptions, $used) {
        // Find unused, valid questions
        $nonused = [];
        foreach ($validoptions as $option) {
            $unused = false;
            foreach ($used as $notvalid) {
                if ($option->id == $notvalid->question_id) {
                    $unused = true;
                    break;
                }
            }
            if ($unused == false) {
                array_push($nonused, $option);
            }
        }
        
        //choose one randomly if exists
        if (count($nonused) == 0) {
            return [];
        }
        else {
            $chosen = array_rand($nonused);
            return $nonused[$chosen]->id;
        }
    }
    
    /*
     * Gets a random, unused question
     * @param distributed quiz id
     * @param distributed quiz category
     */
    public static function get_random_question($id, $category) {
        global $DB;
        
        // Grab options and used options
        $sql = "SELECT q.id
                FROM {question} q
                    JOIN {question_categories} qc ON q.category = qc.id
                WHERE qc.id = ?;";
        $valid_options = $DB->get_records_sql($sql, array('category' => $category));
        //echo("<script>console.log(". json_encode($valid_options, JSON_HEX_TAG) .");</script>");
        
        $sql = "SELECT question_id
                FROM {used_questions}
                WHERE distributedquiz_id = ?;";
        $used = $DB->get_records_sql($sql, array('distributedquiz_id' => $id));
        
        // Grab the right quiz
        $chosen = self::get_random_nonused_option($valid_options, $used);
        $sql = "SELECT *
                FROM {question}
                WHERE id = ?;";
        $chosen = $DB->get_record_sql($sql, array('id' => $chosen));
        return $chosen;
        
    }
    
    /*
     * Assigns a quiz to a category
     * @param quizid
     * @param name of distributed quiz
     * $param course(id)
     */
    public static function set_quiz_grade_category($quizid, $name, $course) {
        global $DB;
        
        $categoryname = strval($name);
        // Find if grade category exists
        $sql = "SELECT *
                FROM {grade_categories}
                WHERE courseid = ? and fullname = ?;";
        $category = $DB->get_record_sql($sql, array('courseid' => $course, 'fullname' => $categoryname), $strictness=IGNORE_MISSING);
         
        // If doesn't already exist, create a grade category for the distributed quizzes
        if ($category == false) {
            $id = self::create_grade_category($categoryname, $course);
            $category = $DB->get_record_sql('SELECT * FROM {grade_categories} WHERE id = ?;', array('id' => $id));    
        }
        // Assign quiz to a grade category
        $quiz_item = $DB->get_record_sql("SELECT * 
            FROM {grade_items} 
            WHERE itemmodule = ? and iteminstance = ?;", 
                array('itemmodule' => 'quiz', 'iteminstance' => $quizid));
        $quiz_item->categoryid = $category->id;        
        $DB->update_record('grade_items', $quiz_item, $bulk=false);
    }
    
    /*
     * Creates a grade category in the course 
     * @param name of distributed quiz
     * $param course(id)
     */
    public static function create_grade_category($name, $course) {
        global $DB;
        
        $category = new stdClass();
        $category->courseid = $course;
        $category->parent = 1;
        $category->depth = 2;
        $category->path = '/1/2/';
        $category->fullname = $name;
        $category->aggregation = 13;
        $category->keephigh = 0;
        $category->droplow = 0;
        $category->aggregationonlygraded = 1;
        $category->aggregateoutcomes = 0;
        $category->timecreated = time();
        $category->timemodified = time();
        $category->hidden = 0;
        
        
        $id = $DB->insert_record('grade_categories', $category, $returnid=true, $bulk=false);
        return $id;
        
    }
}
    