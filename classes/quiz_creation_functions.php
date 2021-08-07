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
 * Date Created: 1/25/20
 * Last Updated: 1/25/20
 */

defined('MOODLE_INTERNAL') || die();

class mod_distributedquiz_quiz_creation_functions {
    
    
    public static function create_quiz($course) {
        global $DB;
        $starttime = 1624028400;
        $endtime = 125000000;
        $newquiz = self::define_quiz_form($starttime, $endtime, $course);
        $id = quiz_add_instance($newquiz);
        $module = $DB->get_record($newquiz, array('id' => $id));
        echo("<script>console.log(". json_encode($module, JSON_HEX_TAG) .");</script>");
        add_moduleinfo($module, $course);
        /*
         * These modify 3 tables. In course modules it should be modifying the quiz instance
         * But instead its modifying the distributedquiz instance to point to the quiz. Somehow
         * Also course is being set to 0 somehow
         */
        
    }
    
    public static function define_quiz_form($starttime, $endtime, $course) {
        $quiz = new stdClass();
        // quiz is currently coursemodule 3. Different handling may be required in other courses
        $quiz->coursemodule = 3;
        $quiz->name = 'distributed';
        $quiz->intro = "";
        $quiz->introformat = 1;
        $quiz->timeopen = $starttime;
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
        return $quiz;
       
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
}
    