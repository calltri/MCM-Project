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
 * The renderer for mod_distributedquiz.
 *
 * @package     mod_distributedquiz
 * @copyright   2021 Madison Call <tcall@zagmail.gonzaga.edu>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class distributedquiz_data implements renderable {
    public function __construct(stdclass $myquiz) {
        
    }
}

class mod_distributedquiz_renderer extends plugin_renderer_base {
    /** 
     * Gets the contents to be displayed 
     * 
     * @return string The content to be displayed in the block.
     */
    protected function render_distributedquiz_data(distributedquiz_data $myquiz) {
        $this->output = "This page is not yet ready for deployment :(";
        return $this->output;
    }
    
}