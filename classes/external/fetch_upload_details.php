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

namespace qtype_cloudpoodll\external;

use qtype_cloudpoodll\constants;
use qtype_cloudpoodll\localrecorder\upload_details;
use qtype_cloudpoodll\utils;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/lib.php');

/**
 * Gets the S3 upload details for one local recorder recording.
 *
 * The browser never talks to Cloud Poodll or sees the API token. Everything that decides where the
 * recording goes and how it is processed comes from the question and site settings, not the browser.
 *
 * @package    qtype_cloudpoodll
 * @copyright  2026 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fetch_upload_details extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'qubaid' => new external_value(PARAM_INT, 'The question usage id of the attempt'),
            'slot' => new external_value(PARAM_INT, 'The slot of the question in the usage'),
            'sourcemimetype' => new external_value(PARAM_TEXT, 'The mime type of the recorded file'),
        ]);
    }

    /**
     * Gets the upload details for a new recording.
     *
     * The question can be in a quiz, a preview or any other activity that uses the question engine, so
     * permission comes from the attempt itself: it must be this user's, and still open.
     *
     * @param int $qubaid The question usage id of the attempt.
     * @param int $slot The slot of the question in the usage.
     * @param string $sourcemimetype The mime type of the recorded file.
     * @return array
     */
    public static function execute(int $qubaid, int $slot, string $sourcemimetype): array {
        global $CFG, $USER;

        $params = self::validate_parameters(
            self::execute_parameters(),
            ['qubaid' => $qubaid, 'slot' => $slot, 'sourcemimetype' => $sourcemimetype]
        );

        try {
            $quba = \question_engine::load_questions_usage_by_activity($params['qubaid']);
            $qa = $quba->get_question_attempt($params['slot']);
        } catch (\Exception $e) {
            throw new \moodle_exception('lr_cannotrecord', constants::M_COMPONENT);
        }
        self::validate_context($quba->get_owning_context());

        $question = $qa->get_question(false);
        if (
            !($question instanceof \qtype_cloudpoodll_question) ||
            $qa->get_step(0)->get_user_id() != $USER->id ||
            $qa->get_state()->is_finished()
        ) {
            throw new \moodle_exception('lr_cannotrecord', constants::M_COMPONENT);
        }

        // The media type comes from the question, so the browser can not choose it.
        $mediatype = $question->responseformat;
        if ($mediatype !== constants::REC_AUDIO && $mediatype !== constants::REC_VIDEO) {
            throw new \moodle_exception('lr_cannotrecord', constants::M_COMPONENT);
        }

        // The local recorder uses Amazon Transcribe for both kinds of server transcription, as the
        // iframe recorders do for video. Chrome speech recognition questions keep the iframe recorder.
        $siteconfig = get_config(constants::M_COMPONENT);
        $transcribe = constants::TRANSCRIBER_NONE;
        if (
            in_array((int)$question->transcriber, [constants::TRANSCRIBER_AMAZONTRANSCRIBE,
                constants::TRANSCRIBER_GOOGLECLOUDSPEECH]) && utils::can_transcribe($siteconfig)
        ) {
            $transcribe = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
        }

        $token = utils::fetch_token($siteconfig->apiuser, $siteconfig->apisecret);

        $details = upload_details::fetch(utils::get_cloud_poodll_server(), is_string($token) ? $token : '', [
            'mediatype' => $mediatype,
            'parent' => $CFG->wwwroot,
            'appid' => constants::APPID,
            'owner' => hash('md5', $USER->username),
            'region' => $siteconfig->awsregion,
            'expiredays' => $question->expiredays ?: $siteconfig->expiredays,
            'transcribe' => $transcribe,
            'subtitle' => $transcribe == constants::TRANSCRIBER_AMAZONTRANSCRIBE ? 1 : 0,
            'language' => $question->language ?: ($siteconfig->language ?? 'en-US'),
            'vocab' => 'none',
            'sourcemimetype' => $params['sourcemimetype'],
        ]);

        if (!$details['success']) {
            throw new \moodle_exception('lr_uploaddetailsfailed', constants::M_COMPONENT, '', s($details['message']));
        }

        return [
            'posturl' => $details['posturl'],
            'mediaurl' => $details['mediaurl'],
        ];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'posturl' => new external_value(PARAM_RAW, 'The presigned S3 URL to PUT the recording to'),
            'mediaurl' => new external_value(PARAM_URL, 'The URL the converted recording will appear at'),
        ]);
    }
}
