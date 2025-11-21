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
 * Essay question type upgrade code.
 *
 * @package    qtype
 * @subpackage cloudpoodll
 * @copyright  2019 Poodll Co. Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use qtype_cloudpoodll\constants;

/**
 * Upgrade code for the cloudpoodll question type.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_cloudpoodll_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2017102301) {

        $DB->set_field(constants::M_TABLE, 'responseformat', 'audio', array('responseformat' => 'mp3'));

        $table = new xmldb_table(constants::M_TABLE);
        $field = new xmldb_field('qresource', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'qresource');
        }

        // cloudpoodll savepoint reached
        upgrade_plugin_savepoint(true, 2017102301, 'qtype', 'cloudpoodll');

    }

    if ($oldversion < 2020110402) {


        $table = new xmldb_table(constants::M_TABLE);
        $field = new xmldb_field('safesave', XMLDB_TYPE_INTEGER, '2', null, XMLDB_TYPE_INTEGER, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cloudpoodll savepoint reached
        upgrade_plugin_savepoint(true, 2020110402, 'qtype', 'cloudpoodll');

    }

    if ($oldversion < 2024013102) {

        $table = new xmldb_table(constants::M_TABLE);
        $fields=[];
        $fields[] = new xmldb_field('studentplayer', XMLDB_TYPE_INTEGER, '2', null, XMLDB_TYPE_INTEGER, null, constants::PLAYERTYPE_INTERACTIVETRANSCRIPT);
        $fields[] = new xmldb_field('teacherplayer', XMLDB_TYPE_INTEGER, '2', null, XMLDB_TYPE_INTEGER, null, constants::PLAYERTYPE_INTERACTIVETRANSCRIPT);
        foreach($fields as $field){
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // cloudpoodll savepoint reached
        upgrade_plugin_savepoint(true, 2024013102, 'qtype', 'cloudpoodll');
    }

    if ($oldversion < 2025112100) {
        $table = new xmldb_table(constants::M_TABLE);
        $field = new xmldb_field('noaudiofilters', XMLDB_TYPE_INTEGER, '2', null, XMLDB_TYPE_INTEGER, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // cloudpoodll savepoint reached
        upgrade_plugin_savepoint(true, 2025112100, 'qtype', 'cloudpoodll');
    }

    return true;
}
