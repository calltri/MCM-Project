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
require(__DIR__.'/../../config.php');

class mod_distributedquiz_quiz_creation_functions {
    
    
    public static function create_quiz() {
        
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
        
        $chosen = self::get_random_nonused_option($valid_options, $used);
        return $chosen;
        
    }
}
    