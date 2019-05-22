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
 * Defines the editing form for the cloudpoodll question type.
 *
 * @package    qtype
 * @subpackage cloudpoodll
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use qtype_cloudpoodll\constants;
use qtype_cloudpoodll\utils;

/**
 * Cloud Poodll question type editing form.
 *
 * @copyright  2019 Cloud PoodLL Question
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_cloudpoodll_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $qtype = question_bank::get_qtype('cloudpoodll');
        $config = get_config(constants::M_COMP);

        $mform->addElement('select', 'responseformat',
                get_string('responseformat', constants::M_COMP), $qtype->response_formats());
        $mform->setDefault('responseformat', 'editor');

        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', constants::M_COMP),
                array('rows' => 10), $this->editoroptions);

        // timelimit
        $name = 'timelimit';
        $label = get_string($name, constants::M_COMP);
        $options = utils::get_timelimit_options();
        $mform->addElement('select', $name, $label, $options);
        $mform->setDefault($name, 60);

        // language options
        $name = 'language';
        $label = get_string($name, constants::M_COMP);
        $options = utils::get_lang_options();
        $mform->addElement('select', $name, $label, $options);
        $mform->setDefault($name, $config->$name);

        // audioskin
        $name = 'audioskin';
        $label = get_string($name, constants::M_COMP);
        $type = constants::REC_AUDIO;
        $options = utils::fetch_options_skins($type);
        $mform->addElement('select', $name, $label, $options);
        $mform->setDefault('audioskin', $config->$name);

        // videoskin
        $name = 'videoskin';
        $label = get_string($name, constants::M_COMP);
        $type = constants::REC_VIDEO;
        $options = utils::fetch_options_skins($type);
        $mform->addElement('select', $name, $label, $options);
        $mform->setDefault($name, $config->$name);

        // transcriber
        $name = 'transcriber';
        $label = get_string($name, constants::M_COMP);
        $options = utils::fetch_options_transcribers();
        $mform->addElement('select', $name, $label, $options);
        $mform->setDefault($name, $config->$name);

        // transcode
        $name = 'transcode';
        $label = get_string($name, constants::M_COMP);
        $text = get_string('transcode_details', constants::M_COMP);
        $mform->addElement('advcheckbox', $name, $label, $text);
        $mform->setDefault($name, $config->$name);

        // expiredays
        $name = 'expiredays';
        $label = get_string($name, constants::M_COMP);
        $options = utils::get_expiredays_options();
        $mform->addElement('select', $name, $label, $options);
        $mform->setDefault($name, $config->$name);

        // question resource
        $mform->addElement('filemanager', 'qresource', get_string('qresource', constants::M_COMP), null,
                array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));

    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        if (empty($question->options)) {
            return $question;
        }
        $question->responseformat = $question->options->responseformat;
        foreach (constants::extra_fields as $field) {
            switch ($field) {
                case 'graderinfoformat':
                case 'graderinfo':
                case 'qresource':
                    break;
                default:
                    $question->{$field} = $question->options->{$field};
            }

        }

        //Set qresource details, and configure a draft area to accept any uploaded pictures
        //all this and this whole method does, is to load existing files into a filearea
        //so it is not called when creating a new question, only when editing an existing one

        //best to use file_get_submitted_draft_itemid - because copying questions gets weird otherwise
        //$draftitemid =$question->options->qresource;
        $draftitemid = file_get_submitted_draft_itemid(\qtype_cloudpoodll\constants::FILEAREA_QRESOURCE);

        file_prepare_draft_area($draftitemid, $this->context->id, constants::M_COMP,
                \qtype_cloudpoodll\constants::FILEAREA_QRESOURCE,
                !empty($question->id) ? (int) $question->id : null,
                array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
        $question->qresource = $draftitemid;

        $draftid = file_get_submitted_draft_itemid(\qtype_cloudpoodll\constants::FILEAREA_GRADERINFO);
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
                $draftid,           // draftid
                $this->context->id, // context
                constants::M_COMP,      // component
                \qtype_cloudpoodll\constants::FILEAREA_GRADERINFO,       // filarea
                !empty($question->id) ? (int) $question->id : null, // itemid
                $this->fileoptions, // options
                $question->options->graderinfo // text
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        return $question;
    }

    public function qtype() {
        return 'cloudpoodll';
    }
}
