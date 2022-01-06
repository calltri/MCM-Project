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
 * Plugin strings are defined here.
 *
 * @package     mod_distributedquiz
 * @category    string
 * @copyright   2021 Madison Call <tcall@zagmail.gonzaga.edu>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

#TODO Add distributedquizname_help
# distributedquizsettings
# distributedquizfieldset

$string['categoryfield'] = 'Category';
$string['categoryfield_help'] = 'Choose the category questions will be selected from';
$string['pluginname'] = 'mod_distributedquiz';
$string['distributedquizname'] = 'Distributed Quiz';
$string['pluginadministration'] = 'Madison Call';
$string['distributedquiznameplural'] = 'Distributed Quizzes';

$string['numberofquestions'] = 'How many questions should be asked';
$string['numberofquestions_help'] = 'These will be asked at a rate of 1 per day. '
        . 'If you choose more questions than exist in the quiz category, only as '
        . 'many quizzes as there are questions will be created';
$string['messageprovider:created'] = 'Notification of quiz creation';
$string['modulename'] = 'Distributed Quiz';
$string['modulenameplural'] = 'Distributed Quizzes';


$string['quizcreatednotificationmessage'] = 'A quiz has been created. Please select the link to take the quiz promptly';
$string['quizcreatednotificationsubject'] = 'Quiz Created';
$string['quizcreationduration'] = "Select the duration of time during which quizzes will be randomly generated";
$string['quizopen'] = "Open the quiz";
$string['quizurl'] = '/mod/assign/view.php?id={$a}';

$string['timing'] = 'Timing';
$string['timelimit'] = 'The duration of time the quiz will be open, starting from when the quiz can first be created';

# Placeholders to get rid of debug errors
$string['distributedquizsettings'] = 'These are settings';
$string['distributedquizfieldset'] = 'What is this';
$string['distributedquizname_help'] = 'For help email tcall';
