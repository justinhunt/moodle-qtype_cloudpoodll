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
 * @package    moodlecore
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use qtype_cloudpoodll\constants;

/**
 * restore plugin class that provides the necessary information
 * needed to restore one cloudpoodll qtype plugin
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_cloudpoodll_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        return array(
                new restore_path_element('cloudpoodll', $this->get_pathfor('/cloudpoodll'))
        );
    }

    /**
     * Process the qtype/cloudpoodll element
     */
    public function process_cloudpoodll($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        // Detect if the question is created or mapped
        $questioncreated = $this->get_mappingid('question_created',
                $this->get_old_parentid('question')) ? true : false;

        // If the question has been created by restore, we need to create its
        // qtype_cloudpoodll too
        if ($questioncreated) {
            $data->questionid = $this->get_new_parentid('question');
            $newitemid = $DB->insert_record(constants::M_TABLE, $data);
            $this->set_mapping(constants::M_COMP, $oldid, $newitemid);
        }
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder
     */
    public static function define_decode_contents() {
        return array(
                new restore_decode_content(constants::M_TABLE, array(\qtype_cloudpoodll\constants::FILEAREA_GRADERINFO,
                        'qresource'), constants::M_COMP)
        );
    }

    /**
     * When restoring old data, that does not have the cloudpoodll options information
     * in the XML, supply defaults.
     */
    protected function after_execute_question() {
        global $DB;

        $cloudpoodllswithoutoptions = $DB->get_records_sql("
                    SELECT *
                      FROM {question} q
                     WHERE q.qtype = ?
                       AND NOT EXISTS (
                        SELECT 1
                          FROM {qtype_cloudpoodll_opts}
                         WHERE questionid = q.id
                     )
                ", array('cloudpoodll'));

        foreach ($cloudpoodllswithoutoptions as $q) {
            $defaultoptions = new stdClass();
            $defaultoptions->questionid = $q->id;
            $defaultoptions->responseformat = 'editor';
            $defaultoptions->transcriber = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
            $defaultoptions->transcode = 1;
            $defaultoptions->timelimit = 0;
            $defaultoptions->expiredays = 365;
            $defaultoptions->studentplayer = constants::PLAYERTYPE_INTERACTIVETRANSCRIPT;
            $defaultoptions->teacherplayer = constants::PLAYERTYPE_INTERACTIVETRANSCRIPT;
            $defaultoptions->language = constants::LANG_ENUS;
            $defaultoptions->audioskin =  constants::SKIN_123;
            $defaultoptions->videoskin = constants::SKIN_123;
            $defaultoptions->graderinfo = '';
            $defaultoptions->qresource = '';
            $defaultoptions->graderinfoformat = FORMAT_HTML;
            $defaultoptions->safesave = 0;
            $DB->insert_record(constants::M_TABLE, $defaultoptions);
        }
    }
}
