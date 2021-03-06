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
 * cloudpoodll question renderer class.
 *
 * @package    qtype
 * @subpackage cloudpoodll
 * @copyright  2019 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use qtype_cloudpoodll\constants;
use qtype_cloudpoodll\utils;

class qtype_cloudpoodll_renderer extends qtype_renderer {

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();
        $responseoutput = $question->get_format_renderer($this->page);

        // Answer field.
        $step = $qa->get_last_step_with_qt_var('answer');
        if (empty($options->readonly)) {
            $answer = $responseoutput->response_area_input('answer', $qa,
                    $step, 1, $options->context);

        } else {
            $answer = $responseoutput->response_area_read_only('answer', $qa,
                    $step, 1, $options->context);
        }

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
                array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $answer, array('class' => 'answer'));
        $result .= html_writer::end_tag('div');

        return $result;
    }

    protected function class_name() {
        return 'qtype_cloudpoodll_';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        $question = $qa->get_question();

        // fetch submitted data
        $mediaurl = $step->get_qt_var($name . 'mediaurl');
        $transcript = $step->get_qt_var($name . 'transcript');

        // assume no subtitles
        $have_subtitles = false;

        // if Amazon transcribe OR Google Cloud Speech then we have subtitles
        switch($question->transcriber){
            case constants::TRANSCRIBER_AMAZONTRANSCRIBE:
                $transcript = utils::fetch_transcript($mediaurl);
                if ($transcript) {
                    $have_subtitles = true;
                }
                break;
            case constants::TRANSCRIBER_GOOGLECLOUDSPEECH:
                $transcript = utils::fetch_transcript($mediaurl);
                if ($transcript) {
                    $have_subtitles = true;
                }
        }


        // transcript could be a url, or a block of text or empty
        // here we turn a url into text if we can
        if (empty($transcript)) {
            if($question->transcriber == constants::TRANSCRIBER_AMAZONTRANSCRIBE ||
                    $question->transcriber == constants::TRANSCRIBER_GOOGLECLOUDSPEECH
            ) {
                $transcript = get_string('transcriptnotready', CONSTANTS::M_COMP);
            }
            $have_subtitles = false;
        }

        $player_div = $this->fetch_player($mediaurl, $question->language, $have_subtitles);

        if ($have_subtitles) {
            return $player_div;
        } else if(!empty($transcript) && $transcript != constants::BLANK) {
            return $player_div . html_writer::div($transcript, 'qtype_cloudpoodll_transcriptdiv', array());
        }else{
            return $player_div;
        }

        //Do this for testing fetch and process of transcript via ad hoc task.
        //but we do not do that.
        //utils::register_fetch_transcript_task($url,$qa,$step);
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
        $question = $qa->get_question();
        $fieldname = $qa->get_qt_field_name($name);//$name = "answer"

        // setup the recorder DIV
        $options = get_config('qtype_cloudpoodll');
        $recorder = $this->fetch_recorder($options, $question, $fieldname);

        //Answer field
        if (!$use_answer = $step->get_qt_var($name)) {
            $use_answer = constants::BLANK;
        }
        $answer = html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => $fieldname,
                'value' => $use_answer));

        // Media URL field
        if (!$use_mediaurl = $step->get_qt_var($name . 'mediaurl')) {
            $use_mediaurl = '';
        }
        $mediaurl = html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => $fieldname . 'mediaurl',
                'value' => $use_mediaurl));

        // Transcript field
        if (!$use_transcript = $step->get_qt_var($name . 'transcript')) {
            $use_transcript = constants::BLANK;
        }
        $transcript = html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => $fieldname . 'transcript',
                'value' => $use_transcript));

        // return recorder and associated hidden fields
        return $recorder . $transcript . $mediaurl . $answer;
    }

    /**
     * @return string the HTML for the textarea.
     */
    protected function fetch_player($mediaurl, $language, $havesubtitles = false) {
        global $PAGE;

        $playerid = html_writer::random_id(CONSTANTS::M_COMP . '_');



        $p_options = new \stdClass();
        $p_options->playerid=$playerid;
        $p_options->mediaurl=$mediaurl;
        $p_options->lang=$language;
        $p_options->maxaudiowidth=480;
        $p_options->maxvideowidth=480;
        $p_options->maxvideoheight=360;
        //transcript bits
        if ($havesubtitles) {
            $p_options->transcripturl = $mediaurl . '.vtt';
            $p_options->component = CONSTANTS::M_COMP;
            $p_options->containerid = \html_writer::random_id(CONSTANTS::M_COMP . '_');
            $p_options->cssprefix = CONSTANTS::M_COMP . '_transcript';
            $PAGE->requires->js_call_amd(CONSTANTS::M_COMP . '/interactivetranscript', 'init', array($p_options));
            $PAGE->requires->strings_for_js(array('transcripttitle'), CONSTANTS::M_COMP);
        }else{
            $p_options->notranscript= true;
        }

        if($this->class_name() == 'qtype_cloudpoodll_video') {
            $player = $this->render_from_template(constants::M_COMP . '/videoplayerstandard', $p_options);
        }else{
            $player = $this->render_from_template(constants::M_COMP . '/audioplayerstandard', $p_options);
        }

        return $player;

    }

    /**
     * @return string the HTML for the textarea.
     */
    protected function fetch_recorder($r_options, $question, $inputname) {
        global $CFG, $USER;

        $width = '';
        $height = '';
        $hints = new \stdClass();
        switch ($this->class_name()) {

            case 'qtype_cloudpoodll_audio':
                $recordertype = constants::REC_AUDIO;
                $recorderskin = $question->audioskin;
                switch ($question->audioskin) {
                    case constants::SKIN_FRESH:
                        $width = '400';
                        $height = '300';
                        break;
                    case constants::SKIN_PLAIN:
                        $width = '360';
                        $height = '190';
                        break;
                    default:
                        // bmr 123, once, standard
                        $width = '360';
                        $height = '240';
                }
                break;

            case 'qtype_cloudpoodll_video':
            default:
                $recordertype = constants::REC_VIDEO;
                $recorderskin = $question->videoskin;
                switch ($question->videoskin) {
                    case constants::SKIN_BMR:
                        $width = '360';
                        $height = '450';
                        break;
                    case constants::SKIN_123:
                        $width = '450';
                        $height = '550';
                        break;
                    case constants::SKIN_ONCE:
                        $width = '350';
                        $height = '290';
                        break;
                    default:
                        $width = '360';
                        $height = '410';
                }
        }

        //transcription defaults
        $transcriber = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
        $chrometranscribe = '0';
        $subtitle="0";
        $hints->encoder = 'auto';

        //branch based on which transcriber we are using
        switch($question->transcriber) {
            // amazon transcribe
            case constants::TRANSCRIBER_AMAZONTRANSCRIBE:
                $can_transcribe = utils::can_transcribe($r_options);
                if ($can_transcribe) {
                    $transcriber = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
                    $subtitle = "1";
                } else{
                    $transcriber = constants::TRANSCRIBER_NONE;
                }
                break;

            // chrometranscribe
            case constants::TRANSCRIBER_GOOGLECHROME:
                $chrometranscribe = '1';
                break;

                //google cloud speech
            case constants::TRANSCRIBER_GOOGLECLOUDSPEECH:
                //we can not use google cloud speech for video, so do not even try
                if($recordertype === constants::REC_VIDEO){
                    $transcriber = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
                    $subtitle = "1";
                }else {
                    $transcriber = constants::TRANSCRIBER_GOOGLECLOUDSPEECH;
                    $subtitle = "1";
                    $hints->encoder = 'stereoaudio';
                }
                break;

            default:
                $transcriber = constants::TRANSCRIBER_NONE;

        }

        // transcode
        $transcode = ($question->transcode ? '1' : '0');

        // time limit
        $timelimit = $question->timelimit;

        // fetch cloudpoodll token
        $api_user = get_config(CONSTANTS::M_COMP, 'apiuser');
        $api_secret = get_config(CONSTANTS::M_COMP, 'apisecret');


        //id user has errors with tokens or cloudpoodll API send those back
        if(empty($api_user) || empty($api_secret)){
            $message = get_string('nocredentials',constants::M_COMP,
                    $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            $errormessage = $this->show_problembox($message);
            return $errormessage;

        }else{
            //fetch token
            $token = utils::fetch_token($api_user, $api_secret);

            //check token authenticated and no errors in it
            $errormessage = utils::fetch_token_error($token);
            if(!empty($errormessage)){
                $errormessage = $this->show_problembox($errormessage);
                return $errormessage;
            }
        }


        // any recorder hints ... get sorted here
        $string_hints = base64_encode(json_encode($hints));

        // the elementid of the div in the DOM
        $dom_id = html_writer::random_id('');
        
        $t_options= new \stdClass();
        $t_options->recid ='therecorder_' . $dom_id;
        $t_options->dataid = 'therecorder_' . $dom_id;
        $t_options->parent=$CFG->wwwroot;
        $t_options->owner= hash('md5',$USER->username);
        $t_options->localloader= constants::LOADER_URL;
        $t_options->recordertype= $recordertype;
        $t_options->appid= constants::APPID;
        $t_options->recorderskin= $recorderskin;
        $t_options->width= $width;
        $t_options->height= $height;
        $t_options->updatecontrol= $inputname;
        $t_options->timelimit= $timelimit;
        $t_options->transcode= $transcode;
        $t_options->transcribe= $transcriber;
        $t_options->subtitle= $subtitle;
        $t_options->speechevents= $chrometranscribe;
        $t_options->language= $question->language;
        $t_options->expiredays= $question->expiredays;
        $t_options->awsregion= $r_options->awsregion;
        $t_options->fallback= $r_options->fallback;
        $t_options->string_hints= $string_hints;
        $t_options->token= $token;

        if($recordertype ==constants::REC_AUDIO) {
            $t_options->iframeclass=constants::CLASS_AUDIOREC_IFRAME;
            $recorderhtml = $this->render_from_template(constants::M_COMP . '/audiorecordercontainer', $t_options);
        }else{
            $t_options->iframeclass=constants::CLASS_VIDEOREC_IFRAME;
            $recorderhtml = $this->render_from_template(constants::M_COMP . '/videorecordercontainer', $t_options);
        }


        // set up the AMD for the recorder
        $opts = array(
                'component' => CONSTANTS::M_COMP,
                'data_id' => 'therecorder_' . $dom_id,
                'inputname' => $inputname,
                'transcriber' => $transcriber,
                'safesave' => $question->safesave
        );

        $this->page->requires->js_call_amd(CONSTANTS::M_COMP . '/cloudpoodllhelper', 'init', array($opts));
        return $recorderhtml;
    }

    /**
     * Return HTML to display message about problem
     */
    public function show_problembox($msg) {
        $output = '';
        $output .= $this->output->box_start(constants::M_COMP . '_problembox');
        $output .= $this->notification($msg, 'warning');
        $output .= $this->output->box_end();
        return $output;
    }

    public function manual_comment(question_attempt $qa, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }

        $question = $qa->get_question();
        return html_writer::nonempty_tag('div', $question->format_text(
                $question->graderinfo, $question->graderinfo, $qa, 'qtype_poodllrecording',
                \qtype_cloudpoodll\constants::FILEAREA_GRADERINFO, $question->id), array('class' => 'graderinfo'));
    }

}

/**
 * An cloudpoodll format renderer for cloudpoodlls where the student should record audio.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_cloudpoodll_audio_renderer extends qtype_cloudpoodll_renderer {
    protected function class_name() {
        return 'qtype_cloudpoodll_audio';
    }
}

/**
 * An cloudpoodll format renderer for cloudpoodlls where the student should record audio.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_cloudpoodll_video_renderer extends qtype_cloudpoodll_renderer {
    protected function class_name() {
        return 'qtype_cloudpoodll_video';
    }
}
