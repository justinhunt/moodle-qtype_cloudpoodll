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
 * PoodLL Recording question definition class.
 *
 * @package    qtype
 * @subpackage cloudpoodll
 * @copyright  2019 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use qtype_cloudpoodll\constants;

/**
 * Represents a cloudpoodll question.
 *
 * @copyright  2019 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_cloudpoodll_question extends question_with_responses {
    public $responseformat;
    public $graderinfo;
    public $graderinfoformat;

    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        question_engine::load_behaviour_class('manualgraded');
        return new qbehaviour_manualgraded($qa, $preferredbehaviour);
    }

    /**
     * @param moodle_page the page we are outputting to.
     * @return qtype_cloudpoodll_format_base_renderer the response-format-specific renderer.
     */
    public function get_format_renderer(moodle_page $page) {
        return $page->get_renderer(constants::M_COMP, $this->responseformat);
    }

    /**
     *    This tells Moodle what fields to expect, in particular it tells it
     *   to look for uploaded file URLs in the answer field
     */
    public function get_expected_data() {
        $expecteddata = array('answer' => PARAM_URL);
        $expecteddata['answermediaurl'] = PARAM_URL;
        $expecteddata['answertranscript'] = PARAM_TEXT;
        return $expecteddata;
    }

    public function summarise_response(array $response) {
        if (isset($response['answertranscript']) &&
                !empty($response['answertranscript']) &&
                strpos($response['answertranscript'], 'http') !== 0
        ) {
            return $response['answertranscript'];
        } else if ((isset($response['answermediaurl']) && !empty($response['answermediaurl']))) {
            return $response['answermediaurl'];
        } else {
            return null;
        }
    }

    public function get_correct_response() {
        return null;
    }

    public function is_complete_response(array $response) {
        return !empty($response['answer']);
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        //print_object($qa);
        if ($component == 'question' && $filearea == 'response_answer') {
            //since we will put files in respnse_answer, this is likely to be always true.
            return true;

            //if we are using a qresource, there is no need to restrict here
        } else if ($component == constants::M_COMP && $filearea == \qtype_cloudpoodll\constants::FILEAREA_QRESOURCE) {
            return true;

        } else if ($component == constants::M_COMP && $filearea == \qtype_cloudpoodll\constants::FILEAREA_GRADERINFO) {
            return $options->manualcomment;

        } else {
            return parent::check_file_access($qa, $options, $component,
                    $filearea, $args, $forcedownload);

        }
    }
}
