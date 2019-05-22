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

    return true;
}
