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

class mod_distributedquiz_functions {
    
        /**
     * Sends out a notification 
     * 
     * $quizid
     * $userid
         * TODO Test on server where a proper email output is configured
     */
    public static function send_notification ($quizid, $userid) {
        global $DB;
        
        $message = new \core\message\message();
        $message->component = get_string('pluginname', 'distributedquiz'); // Your plugin's name
        $message->name = 'created'; // Your notification name from message.php
        $message->userfrom = core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $DB->get_record('user', array('id' => $userid));
        $message->subject = get_string('quizcreatednotificationsubject', 'distributedquiz');
        $message->fullmessage = get_string('quizcreatednotificationmessage', 'distributedquiz');
        $message->fullmessageformat = FORMAT_MARKDOWN;
        //$message->fullmessagehtml = '<p>message body</p>';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url(get_string('quizurl', 'distributedquiz', $quizid)))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl
        
        
        // Actually send the message
        $messageid = message_send($message);
    }
    
    public static function get_moduleid_from_id($quizid, $modulename) {
        global $DB;
        $cm = $DB->get_record_sql("select id 
                                    from {course_modules} cm 
                                    join {modules} m on m.id = cm.instance 
                                    where m.id = 'quiz' and cm.instance = ?);",
                array('m.id' => $modulename, 'cm.instance' => $quizid));
        return cm;
    }
    
    public static function send_all_notifications($cmid, $quizid, $include_admins=false) {
        global $DB;
        // Grab course module id and course id to get the course context
        $cm = self::get_module_from_cmid($cmid);
        $courseid = $DB->get_records_sql("select course from {course_modules} 
               where id = ?;", array('id' => $cm));
        $info = new \core_availability\info_module($cm);
        
        // Get enrolled users
        $users = get_enrolled_users(context_course::instance($courseid));      
        // Get users that can access the quiz
        $filtered = $info->filter_user_list($users);
        
        // TODO Filter out admin users
        
        
        // Send notifications to all users
        foreach($filtered as $user) {
            self::send_notification($cmid, $user);
        }
    }
    
    /* 
     * Generates random quiz creation times at the given time at $startcreation
     * in the appropriate time zone within the creationduration
     * Note: all times should be timestamps
     * @param startcreation 
     * @param creationduration
     * @param numquestions
     * @param timezoneobj a DateTimeZone
     * @return list of all quiz creation times
     */
    public static function determine_creation_times($startcreation, $creationduration, $numquestions, $timezoneobj) {
        $times = [];
        // Set first time
        $timezone = new DateTime();
        $timezone->setTimezone($timezoneobj);
        $timezone->setTimestamp($startcreation);
                
        // Create timestamps
        for ($i = 0; $i < $numquestions; $i++) {
            // Get current day
            $day = $timezone->format('D');
            
            // Make sure is not a weekend
            if ($day != 'Sat' && $day != 'Sun') {
                // Add a new random time
                $time = $timezone->getTimestamp();
                $time += rand(0, $creationduration);
                array_push($times, $time);
            }
            else {
                // if it is it doesn't count as a day
                $i -= 1;
            }
            // Move to next day
            $timezone->add(new DateInterval('P1D'));
        }
        return $times;        
    }

}