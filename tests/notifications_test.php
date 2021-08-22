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
 * File containing tests for notifications.
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
 * The notifications test class.
 *
 * @package    mod_distributedquiz
 * @copyright  2021 Tristan Call <tcall@zagmail.gonzaga.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_distributedquiz_notifications_testcase extends advanced_testcase {

    // Write the tests here as public funcions.
    public function test_send_notification_basic() {
        $functions = new mod_distributedquiz_functions;
        $this->preventResetByRollback();
        $sink = $this->redirectMessages();
        //... code that is sending messages
        $functions->send_notification(1, 2);
        
        $messages = $sink->get_messages();
        $this->assertEquals(1, count($messages));
        $this->assertEquals($messages[0]->contexturl, '/mod/assign/view.php?id=1');
    }

}
