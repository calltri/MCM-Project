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
 * @copyright   2021 Tristan Call <tcall@zagmail.gonzaga.edu>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

#TODO Add distributedquizname_help
# distributedquizsettings
# distributedquizfieldset


$string['pluginname'] = 'Distributed Quiz';
$string['distributedquizname'] = 'Distributed Quiz';
$string['pluginadministration'] = 'Tristan Call';
$string['distributedquiznameplural'] = 'Distributed Quizzes';

$string['messageprovider:created'] = 'Notification of quiz creation';
$string['modulename'] = 'Distributed Quiz';
$string['modulenameplural'] = 'Distributed Quizzes';

$string['quizcreatednotificationmessage'] = 'A quiz has been created. Please select the link to take the quiz promptly';
$string['quizcreatednotificationsubject'] = 'Quiz Created';
$string['quizurl'] = '/mod/assign/view.php?id={$a}';

# Placeholders to get rid of debug errors
$string['distributedquizsettings'] = 'These are settings';
$string['distributedquizfieldset'] = 'What is this';
$string['distributedquizname_help'] = 'For help email tcall';
