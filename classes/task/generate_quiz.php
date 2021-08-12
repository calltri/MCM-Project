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
 * Date Created: 8/10/21
 * Last Updated: 8/10/21
 */

class generate_quiz extends \core\task\adhoc_task {
    public function execute() {
        // Get the custom data.
         $data = $this->get_custom_data();
         
        $quizfunctions = new mod_distributedquiz_quiz_creation_functions;
        $quizfunctions->create_quiz($data->courseid, $data->sectionid, $data->groupid);
        
        
        //
        //TODO send notificaiton
        //$functions = new mod_distributedquiz_functions;
        //$functions->send_notification(2, 2);
    }
}