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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_distributedquiz
 * @category    upgrade
 * @copyright   2021 Tristan Call <tcall@zagmail.gonzaga.edu>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_distributedquiz upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_distributedquiz_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // For further information please read the Upgrade API documentation:
    // https://docs.moodle.org/dev/Upgrade_API
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at:
    // https://docs.moodle.org/dev/XMLDB_editor
    
    if ($oldversion < 2021081100) {

        // Define table subquizzes to be created.
        $table = new xmldb_table('subquizzes');

        // Adding fields to table subquizzes.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('distributedquiz_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('quiz_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('creation_time', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table subquizzes.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('quiz_ref', XMLDB_KEY_FOREIGN, ['quiz_id'], 'quiz', ['id']);
        $table->add_key('distributedquiz_ref', XMLDB_KEY_FOREIGN, ['distributedquiz_id'], 'distributedquiz', ['id']);

        // Conditionally launch create table for subquizzes.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Distributedquiz savepoint reached.
        upgrade_mod_savepoint(true, 2021081100, 'distributedquiz');
    }
    
    if ($oldversion < 2021081101) {

        // Define table used_questions to be created.
        $table = new xmldb_table('used_questions');

        // Adding fields to table used_questions.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('distributedquiz_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table used_questions.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('distributedquiz_ref', XMLDB_KEY_FOREIGN, ['distributedquiz_id'], 'distributedquiz', ['id']);
        $table->add_key('question_ref', XMLDB_KEY_FOREIGN, ['question_id'], 'question', ['id']);

        // Conditionally launch create table for used_questions.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Distributedquiz savepoint reached.
        upgrade_mod_savepoint(true, 2021081101, 'distributedquiz');
    }


    return true;
}
