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
 * Test helpers for the cloudpoodll question type.
 *
 * @package    qtype_cloudpoodll
 * @copyright  2026 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qtype_cloudpoodll\constants;

/**
 * Test helper class for the cloudpoodll question type.
 *
 * Used by test_question_maker and the core_question generator, e.g.
 * $generator->create_question('cloudpoodll', 'video', $overrides).
 *
 * @copyright  2026 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_cloudpoodll_test_helper extends question_test_helper {
    /**
     * The example questions this helper can make, one per response format.
     *
     * @return array
     */
    public function get_test_questions() {
        return [constants::RESPONSEFORMAT_AUDIO, constants::RESPONSEFORMAT_VIDEO, constants::RESPONSEFORMAT_WHITEBOARD];
    }

    /**
     * Makes a cloudpoodll question definition with the given response format.
     *
     * @param string $responseformat one of the constants::RESPONSEFORMAT_* values
     * @return qtype_cloudpoodll_question
     */
    protected function make_cloudpoodll_question(string $responseformat) {
        question_bank::load_question_definition_classes('cloudpoodll');
        $q = new qtype_cloudpoodll_question();
        test_question_maker::initialise_a_question($q);
        $q->name = 'Cloud Poodll question (' . $responseformat . ')';
        $q->questiontext = 'Please tell us about your weekend.';
        $q->generalfeedback = '';
        $q->qtype = question_bank::get_qtype('cloudpoodll');
        foreach ($this->get_options($responseformat) as $field => $value) {
            $q->{$field} = $value;
        }
        $q->graderinfo = '';
        $q->graderinfoformat = FORMAT_HTML;
        $q->qresource = null;
        return $q;
    }

    /**
     * Makes the data that would come back from the editing form for a cloudpoodll question.
     *
     * @param string $responseformat one of the constants::RESPONSEFORMAT_* values
     * @return stdClass
     */
    protected function get_cloudpoodll_form_data(string $responseformat) {
        $fromform = new stdClass();
        $fromform->name = 'Cloud Poodll question (' . $responseformat . ')';
        $fromform->questiontext = ['text' => 'Please tell us about your weekend.', 'format' => FORMAT_HTML];
        $fromform->defaultmark = 1.0;
        $fromform->generalfeedback = ['text' => '', 'format' => FORMAT_HTML];
        $fromform->graderinfo = ['text' => '', 'format' => FORMAT_HTML];
        foreach ($this->get_options($responseformat) as $field => $value) {
            $fromform->{$field} = $value;
        }
        $fromform->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        return $fromform;
    }

    /**
     * The qtype_cloudpoodll_opts values shared by the question definition and the form data.
     *
     * @param string $responseformat one of the constants::RESPONSEFORMAT_* values
     * @return array field => value
     */
    protected function get_options(string $responseformat): array {
        return [
            'responseformat' => $responseformat,
            'language' => constants::LANG_ENUS,
            'expiredays' => 365,
            'transcriber' => constants::TRANSCRIBER_NONE,
            'studentplayer' => constants::PLAYERTYPE_DEFAULT,
            'teacherplayer' => constants::PLAYERTYPE_DEFAULT,
            'transcode' => 1,
            'audioskin' => constants::SKIN_123,
            'videoskin' => constants::SKIN_123,
            'timelimit' => 0,
            'safesave' => 0,
            'noaudiofilters' => 0,
            'whiteboardwidth' => 800,
            'whiteboardheight' => 600,
        ];
    }

    /**
     * Makes an audio recording question.
     *
     * @return qtype_cloudpoodll_question
     */
    public function make_cloudpoodll_question_audio() {
        return $this->make_cloudpoodll_question(constants::RESPONSEFORMAT_AUDIO);
    }

    /**
     * Makes a video recording question.
     *
     * @return qtype_cloudpoodll_question
     */
    public function make_cloudpoodll_question_video() {
        return $this->make_cloudpoodll_question(constants::RESPONSEFORMAT_VIDEO);
    }

    /**
     * Makes a whiteboard question.
     *
     * @return qtype_cloudpoodll_question
     */
    public function make_cloudpoodll_question_whiteboard() {
        return $this->make_cloudpoodll_question(constants::RESPONSEFORMAT_WHITEBOARD);
    }

    /**
     * Form data for an audio recording question.
     *
     * @return stdClass
     */
    public function get_cloudpoodll_question_form_data_audio() {
        return $this->get_cloudpoodll_form_data(constants::RESPONSEFORMAT_AUDIO);
    }

    /**
     * Form data for a video recording question.
     *
     * @return stdClass
     */
    public function get_cloudpoodll_question_form_data_video() {
        return $this->get_cloudpoodll_form_data(constants::RESPONSEFORMAT_VIDEO);
    }

    /**
     * Form data for a whiteboard question.
     *
     * @return stdClass
     */
    public function get_cloudpoodll_question_form_data_whiteboard() {
        return $this->get_cloudpoodll_form_data(constants::RESPONSEFORMAT_WHITEBOARD);
    }
}
