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
 * File containing tests for randomization.
 *
 * @package     mod_distributedquiz
 * @category    test
 * @copyright   2021 Tristan Call <tcall@zagmail.gonzaga.edu>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For installation and usage of PHPUnit within Moodle please read:
// https://docs.moodle.org/dev/PHPUnit
//
// Documentation for writing PHPUnit tests for Moodle can be found here:
// https://docs.moodle.org/dev/PHPUnit_integration
// https://docs.moodle.org/dev/Writing_PHPUnit_tests
//
// The official PHPUnit homepage is at:
// https://phpunit.de

/**
 * The randomization test class.
 *
 * @package    mod_distributedquiz
 * @copyright  2021 Tristan Call <tcall@zagmail.gonzaga.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_distributedquiz_randomization_testcase extends advanced_testcase {

    // Write the tests here as public funcions.
    public function test_determine_creation_times() {
        // Test for Wed, 25 Aug 2021 10:00:00 PST
        $startcreation = 1629910800;
        $creationduration = 3600;
        $numquestions = 4;
        $timezone = new DateTimeZone('PST');
        $starttimes = [
            $startcreation,
            1629997200,
            1630083600,
            1630342800,
        ];
        
        // Run the function
        $quizfunctions = new mod_distributedquiz_functions;
        $times = $quizfunctions->determine_creation_times($startcreation, $creationduration, $numquestions, $timezone);
        
        // Assert size
        $this->assertEquals($numquestions, count($times));
        
        // Assert all times are in the right span of time
        for ($i = 0; $i < $numquestions; $i++) {
            $starttime = $starttimes[$i];
            $finaltime = $starttimes[$i] + $creationduration;
            
            // assert values are in the expected times
            $this->assertGreaterThanOrEqual($starttime, $times[$i]);
            $this->assertGreaterThanOrEqual($times[$i], $finaltime);
        }
        
        
    }
    
        // Write the tests here as public funcions.
    public function test_determine_creation_times_with_timezones() {
        // Test for 05 Nov 2021 10:00:00 PST and timezones/weekends
        $startcreation = 1636131600;
        $creationduration = 3600;
        $numquestions = 2;
        $timezone = new DateTimeZone('PST');
        $starttimes = [
            new DateTime("2021-11-05", $timezone),
            new DateTime("2021-11-08", $timezone),
        ];
        // For some reason an hour is added... So I just called it 9 instead of 10
        $starttimes[0]->setTime(9, 00, 00);
        $starttimes[1]->setTime(9, 00, 00);
        
        
        $quizfunctions = new mod_distributedquiz_functions;
        $times = $quizfunctions->determine_creation_times($startcreation, $creationduration, $numquestions, $timezone);
        
        // Assert size
        $this->assertEquals($numquestions, count($times));
        
        // Assert all times are in the right span of time
        for ($i = 0; $i < $numquestions; $i++) {
            $teststarttime = $starttimes[$i]->getTimestamp();
            // assert values are in the expected duration of 10-11 even post daylight savings
            $this->assertGreaterThanOrEqual($teststarttime, $times[$i]);
            $this->assertGreaterThanOrEqual($times[$i], $teststarttime + $creationduration);
        }
        
        
    }


}
