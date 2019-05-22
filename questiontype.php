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
 * Question type class for the cloudpoodll question type.
 *
 * @package    qtype
 * @subpackage cloudpoodll
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use qtype_cloudpoodll\constants;

/**
 * The cloudpoodll question type.
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_cloudpoodll extends question_type {

    public function is_manual_graded() {
        return true;
    }

    public function response_file_areas() {
        return array('answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record(constants::M_TABLE,
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        $options = $DB->get_record(constants::M_TABLE, array('questionid' => $formdata->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $formdata->id;
            $options->id = $DB->insert_record(constants::M_TABLE, $options);
        }

        //"import_or_save_files" won't work, because it expects output from an editor which is an array with member itemid
        //the filemanager doesn't produce this, so need to use file save draft area directly
        if (isset($formdata->qresource)) {
            file_save_draft_area_files($formdata->qresource, $context->id, constants::M_COMP,
                    \qtype_cloudpoodll\constants::FILEAREA_QRESOURCE, $formdata->id,
                    array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));

            //save the itemid of the qresource filearea
            $options->qresource = $formdata->qresource;
        } else {
            $options->qresource = null;
        }

        //if we have a recording time limit
        if (isset($formdata->timelimit)) {
            $options->timelimit = $formdata->timelimit;
        } else {
            $options->timelimit = 0;
        }

        //other options
        $options->language = $formdata->language;
        $options->expiredays = $formdata->expiredays;
        $options->transcriber = $formdata->transcriber;
        $options->transcode = $formdata->transcode;
        $options->audioskin = $formdata->audioskin;
        $options->videoskin = $formdata->videoskin;

        $options->responseformat = $formdata->responseformat;
        $options->graderinfo = $this->import_or_save_files($formdata->graderinfo,
                $context, constants::M_COMP, 'graderinfo', $formdata->id);
        $options->graderinfoformat = $formdata->graderinfo['format'];
        $DB->update_record(constants::M_TABLE, $options);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        foreach (constants::extra_fields as $field) {
            $question->{$field} = $questiondata->options->{$field};
        }
        $question->responseformat = $questiondata->options->responseformat;
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
                'audio' => get_string('formataudio', constants::M_COMP),
                'video' => get_string('formatvideo', constants::M_COMP)
        );
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, constants::M_COMP,
                \qtype_cloudpoodll\constants::FILEAREA_GRADERINFO, $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, constants::M_COMP,
                \qtype_cloudpoodll\constants::FILEAREA_QRESOURCE, $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, constants::M_COMP,
                \qtype_cloudpoodll\constants::FILEAREA_GRADERINFO, $questionid);
        $fs->delete_area_files($contextid, constants::M_COMP,
                \qtype_cloudpoodll\constants::FILEAREA_QRESOURCE, $questionid);
    }

    /**
     * If your question type has a table that extends the question table, and
     * you want the base class to automatically save, backup and restore the extra fields,
     * override this method to return an array wherer the first element is the table name,
     * and the subsequent entries are the column names (apart from id and questionid).
     *
     * @return mixed array as above, or null to tell the base class to do nothing.
     */
    public function extra_question_fields() {
        $tableinfo = array(constants::M_TABLE);
        foreach (constants::extra_fields as $field) {
            $tableinfo[] = $field;
        }
        return $tableinfo;
    }

    /*
     * Export question to the Moodle XML format
     *
     * Export question using information from extra_question_fields function
     * We override this because we need to export file fields as base 64 strings, not ids
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {

        //get file storage
        $fs = get_file_storage();
        $expout = "";

        $expout .= "    <responseformat>" . $question->options->responseformat .
                "</responseformat>\n";
        $expout .= "    <graderinfo " .
                $format->format($question->options->graderinfoformat) . ">\n";
        $expout .= $format->writetext($question->options->graderinfo, 3);
        $expout .= $format->write_files($fs->get_area_files($question->contextid, constants::M_COMP,
                \qtype_cloudpoodll\constants::FILEAREA_GRADERINFO, $question->id));
        $expout .= "    </graderinfo>\n";
        $expout .= "    <qresource>" . $format->write_files($fs->get_area_files($question->contextid, constants::M_COMP,
                        \qtype_cloudpoodll\constants::FILEAREA_QRESOURCE, $question->id)) .
                "</qresource>\n";
        foreach (constants::extra_fields as $field) {
            switch ($field) {
                case 'graderinfoformat':
                case 'graderinfo':
                case 'qresource':
                    break;
                default:
                    $expout .= "    <$field>" . $question->options->{$field} .
                            "</$field>\n";

            }

        }

        return $expout;

    }

    /*
 * Imports question from the Moodle XML format
 *
 * Imports question using information from extra_question_fields function
 * If some of you fields contains id's you'll need to reimplement this
 */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        global $CFG;

        $question_type = "cloudpoodll";

        //omit table name
        $qo = $format->import_headers($data);
        $qo->qtype = $question_type;
        $q = $data;

        $qo->responseformat = $format->getpath($q,
                array('#', 'responseformat', 0, '#'), \qtype_cloudpoodll\constants::RESPONSEFORMAT_AUDIO);
        $qo->graderinfo = $format->import_text_with_files($q, array('#', \qtype_cloudpoodll\constants::FILEAREA_GRADERINFO, 0), '',
                $qo->questiontextformat);
        $qo->qresource = $format->import_files_as_draft($format->getpath($q,
                array('#', \qtype_cloudpoodll\constants::FILEAREA_QRESOURCE, '0', '#', 'file'), array()));

        $qo->language = $format->getpath($q,
                array('#', 'language', 0, '#'), constants::LANG_ENUS);
        $qo->expiredays = $format->getpath($q,
                array('#', 'expiredays', 0, '#'), 365);
        $qo->transcriber = $format->getpath($q,
                array('#', 'transcriber', 0, '#'), constants::TRANSCRIBER_AMAZONTRANSCRIBE);
        $qo->transcode = $format->getpath($q,
                array('#', 'transcode', 0, '#'), 1);
        $qo->audioskin = $format->getpath($q,
                array('#', 'audioskin', 0, '#'), constants::SKIN_123);
        $qo->videoskin = $format->getpath($q,
                array('#', 'videoskin', 0, '#'), constants::SKIN_123);
        $qo->timelimit = $format->getpath($q,
                array('#', 'timelimit', 0, '#'), 0);

        return $qo;

    }//end of import from xml

}
