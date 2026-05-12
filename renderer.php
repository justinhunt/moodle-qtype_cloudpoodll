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
 * Cloudpoodll question renderer class.
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
                ['class' => 'qtext']);

        $result .= html_writer::start_tag('div', ['class' => 'ablock']);
        $result .= html_writer::tag('div', $answer, ['class' => 'answer']);
        $result .= html_writer::end_tag('div');

        return $result;
    }

    protected function class_name() {
        return 'qtype_cloudpoodll';
    }

    /**
     * Get the draft ID suffix for the question type.
     *
     * @return string The suffix for the draft id field.
     */
    protected function get_draft_id_suffix() {
        return '_draftid';
    }

    protected function replace_url_filext($url, $ext) {
        $url = preg_replace('/\.[^.]+$/', '.' . $ext, $url);
        return $url;
    }

    protected function fetch_fileext_from_mimetype($mimetype) {
            $ext = "";

            // A little sanity check first.
        if (empty($mimetype)) {
            return $ext;
        }

            // In the case of a string like this:
            // "audio/webm;codecs=opus" we do not want the codecs.
        if(strpos($mimetype, ';') !== false){
            $mimetype = explode(';', $mimetype)[0];
        }

            // Search on mimetype and add the corresponding file extension.
        switch ($mimetype) {
            case "image/jpeg":
                $ext = "jpg";
                break;
            case "image/png":
                $ext = "png";
                break;
            case "audio/wav":
                $ext = "wav";
                break;
            case "audio/ogg":
                $ext = "ogg";
                break;
            case "audio/mpeg3":
                $ext = "mp3";
                break;
            case "audio/mp3":
                $ext = "mp3";
                break;
            case "audio/webm":
                $ext = "webm";
                break;
            case "audio/wma":
                $ext = "wma";
                break;
            case "audio/x-mpeg-3":
                $ext = "mp3";
                break;
            case "audio/mp4":
            case "audio/m4a":
            case "audio/x-m4a":
                $ext = "m4a";
                break;
            case "audio/3gpp":
                $ext = "3gpp";
                break;
            case "video/mpeg3":
                $ext = "3gpp";
                break;
            case "video/m4v":
                $ext = "m4v";
                break;
            case "video/mp4":
                $ext = "mp4";
                break;
            case "video/mov":
            case "video/quicktime":
                $ext = "mov";
                break;
            case "video/x-matroska":
            case "video/webm":
                $ext = "webm";
                break;
            case "video/wmv":
                $ext = "wmv";
                break;
            case "video/ogg":
                $ext = "ogg";
                break;
        }
            // If we get here we have an unknown mime type, just guess based on the mediatype.
        if($ext === ""){
            if(strpos($mimetype, 'video') !== false){
                $ext = "mp4";
            }else{
                $ext = "mp3";
            }
        }
            return $ext;

    }

    public function response_area_read_only($name, $qa, $step, $lines, $context, $mediaurl = null) {
        $question = $qa->get_question();

        // Fetch submitted data.
        if ($mediaurl === null) {
            $mediaurl = $step->get_qt_var($name . 'mediaurl');
        }
        $transcript = $step->get_qt_var($name . 'transcript');
        $details = $step->get_qt_var($name . 'details');


        // Assume no subtitles.
        $havesubtitles = false;

        // If Amazon transcribe OR Google Cloud Speech then we have subtitles.
        if (!empty($mediaurl) && $mediaurl !== constants::BLANK) {
            switch ($question->transcriber) {
                case constants::TRANSCRIBER_AMAZONTRANSCRIBE:
                    $transcript = utils::fetch_transcript($mediaurl);
                    if ($transcript) {
                        $havesubtitles = true;
                    }
                    break;
                case constants::TRANSCRIBER_GOOGLECLOUDSPEECH:
                    $transcript = utils::fetch_transcript($mediaurl);
                    if ($transcript) {
                        $havesubtitles = true;
                    }
                    break;
            }
        }

        // Transcript could be a url, or a block of text or empty.
        // Here we turn a url into text if we can.
        if (empty($transcript)) {
            if ($question->transcriber == constants::TRANSCRIBER_AMAZONTRANSCRIBE ||
                    $question->transcriber == constants::TRANSCRIBER_GOOGLECLOUDSPEECH
            ) {
                $transcript = get_string('transcriptnotready', CONSTANTS::M_COMP);
            }
            $havesubtitles = false;
        }

        // It's all very well having subtitles or transcript , but we also check if the teacher or student is intended to see them.
        // Are we a person who can grade, ie a teacher?
        $isgrader = false;
        if (has_capability('mod/quiz:grade', $context)) {
            $isgrader = true;
        }

        // If not a teacher and student player is default, then no subtitles.
        if (!$isgrader && $question->studentplayer == constants::PLAYERTYPE_DEFAULT) {
            $havesubtitles = false;
            $transcript = '';
            // If is a teacher and teacher player is default, then no subtitles.
        } else if ($isgrader && $question->teacherplayer == constants::PLAYERTYPE_DEFAULT) {
            $havesubtitles = false;
            $transcript = '';
        }

        // Return html.
        $rethtml = '';

        // Fetch the player.
        if (!empty($mediaurl) && $mediaurl !== constants::BLANK) {
            $playerdiv = $this->fetch_player($mediaurl, $question->language, $havesubtitles, $question);
            $rethtml .= $playerdiv;
        } else {
            $rethtml .= html_writer::div(get_string('norecordreceived', constants::M_COMP),
                    'qtype_cloudpoodll_norecordreceived');
        }

        // If we have subtitles, then add them to the player.
        if ($havesubtitles) {
            // Do nothing.
        } else if(!empty($transcript) && $transcript != constants::BLANK) {
            $rethtml .= html_writer::div($transcript, 'qtype_cloudpoodll_transcriptdiv', []);
        }else{
            // do nothing
        }

        // Get details display.
        // Make sure the json and details are properly formed.
        if ($isgrader && !empty($details)) {
            $reclog = json_decode($details);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($reclog->recevents) && count($reclog->recevents) > 0) {
                    $lastmimetype = '';
                    foreach ($reclog->recevents as $recevent) {
                        // This is a hack that allows mustache to show the output specific to the event.
                        $recevent->{$recevent->type} = 1;
                        // We store the mime type from the upload commenced event.
                        if($recevent->type == 'uploadcommenced'){
                            $lastmimetype = $recevent->mimetype;
                        }
                        if($recevent->type == 'awaitingprocessing'){
                            $ext = $this->fetch_fileext_from_mimetype($lastmimetype);
                            if(!empty($ext) && !empty($recevent->targetfile)) {
                                $recevent->srcfile = $this->replace_url_filext($recevent->targetfile, $ext);
                                $recevent->srcfilename = pathinfo($recevent->srcfile, PATHINFO_BASENAME);
                                $recevent->targetfilename = pathinfo($recevent->targetfile, PATHINFO_BASENAME);
                            }
                        }
                        if($recevent->type == 'filesubmitted'){
                            $recevent->finalfilename = pathinfo($recevent->finalfile, PATHINFO_BASENAME);
                        }
                    }
                    $detailsdiv = $this->fetch_details_display($reclog);
                    $rethtml .= $detailsdiv;
                }
            }
        }

        // Return html.
        return $rethtml;

        // Do this for testing fetch and process of transcript via ad hoc task.
        // But we do not do that.
        // utils::register_fetch_transcript_task($url,$qa,$step);
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
        $question = $qa->get_question();
        $fieldname = $qa->get_qt_field_name($name);// $name = "answer".

        // Setup the recorder DIV.
        $options = get_config('qtype_cloudpoodll');
        $draftid = $step->get_qt_var($name . '_draftid');
        if (!$draftid) {
            $draftid = file_get_unused_draft_itemid();
        }
        $recorder = $this->fetch_recorder($qa, $step, $options, $question, $fieldname, $draftid);

        // The recorder status field.
        $details = $step->get_qt_var($name . 'details');
        $templateoptions = [];
        if ($details) {
            $reclog = json_decode($details);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($reclog->recevents) && count($reclog->recevents) > 0) {
                    // We get the last event.
                    $lastevent = $reclog->recevents[array_key_last($reclog->recevents)];
                    // But we want the last event, that saved a recording if we have one.
                    for ($i = count($reclog->recevents) - 1; $i >= 0; $i--) {
                        $recevent = $reclog->recevents[$i];
                        if ($recevent->type == 'awaitingconversion' || $recevent->type == 'filesubmitted') {
                            $lastevent = $recevent;
                            break;
                        }
                    }
                    $templateoptions['lastevent'] = $lastevent;
                    $templateoptions['insession'] = false;
                    $templateoptions[$lastevent->type] = 1;// "filesubmitted" "recordingstarted" "recordingstopped" "uploadcommenced" "awaitingconversion".
                }
            }
        }
        $answerstatus = $this->render_from_template(constants::M_COMP . '/answerstatus', $templateoptions);

        // The elementid of the div in the DOM.
        $answerstatuscontainer = html_writer::div($answerstatus, 'qtype_cloudpoodll_asc', ['id' => $fieldname . '_asc']);

        // Answer field.
        if (!$useanswer = $step->get_qt_var($name)) {
            $useanswer = constants::BLANK;
        }
        $answer = html_writer::empty_tag('input', ['type' => 'hidden',
                'name' => $fieldname,
                'value' => $useanswer]);

        // Media URL field.
        if (!$usemediaurl = $step->get_qt_var($name . 'mediaurl')) {
            $usemediaurl = '';
        }
        $mediaurl = html_writer::empty_tag('input', ['type' => 'hidden',
                'name' => $fieldname . 'mediaurl',
                'value' => $usemediaurl]);

        // Transcript field.
        if (!$usetranscript = $step->get_qt_var($name . 'transcript')) {
            $usetranscript = constants::BLANK;
        }
        $transcript = html_writer::empty_tag('input', ['type' => 'hidden',
                'name' => $fieldname . 'transcript',
                'value' => $usetranscript]);

        // Details field.
        if (!$usedetails = $step->get_qt_var($name . 'details')) {
            $usedetails = '';
        }
        $details = html_writer::empty_tag('input', ['type' => 'hidden',
            'name' => $fieldname . 'details',
            'value' => $usedetails]);

        // Vector field.
        if (!$usevector = $step->get_qt_var($name . 'vector')) {
            $usevector = '';
        }
        $vector = html_writer::empty_tag('input', ['type' => 'hidden',
            'id' => $fieldname . 'vector',
            'name' => $fieldname . 'vector',
            'value' => $usevector]);

        // Draft ID field.
        $draftidfield = html_writer::empty_tag('input', ['type' => 'hidden',
            'name' => $fieldname . $this->get_draft_id_suffix(),
            'value' => $draftid]);

        // Return recorder and associated hidden fields.
        return $answerstatuscontainer . $recorder . $transcript . $details . $mediaurl . $answer . $vector . $draftidfield;
    }


    /**
     * @return string The HTML for the recorder log (details).
     */
    protected function fetch_details_display($details) {
        $detailsid = html_writer::random_id(CONSTANTS::M_COMP . '_');
        $details->id = $detailsid;
        return $this->render_from_template(constants::M_COMP . '/recorderdetailslog', $details);
    }

    /**
     * @return string The HTML for the media player.
     */
    protected function fetch_player($mediaurl, $language, $havesubtitles = false, $question = null) {
        global $PAGE;

        $playerid = html_writer::random_id(CONSTANTS::M_COMP . '_');

        // For right to left languages we want to add the RTL direction and right justify.
        switch($language){
            case constants::LANG_ARAE:
            case constants::LANG_ARSA:
            case constants::LANG_FAIR:
            case constants::LANG_HEIL:
                $rtl = constants::M_COMP. '_rtl';
                break;
            default:
                $rtl = '';
        }

        $poptions = new \stdClass();
        $poptions->playerid = $playerid;
        $poptions->mediaurl = $mediaurl;
        $poptions->lang = $language;
        $poptions->rtl = $rtl;
        $poptions->maxaudiowidth = 480;
        $poptions->maxvideowidth = 480;
        $poptions->maxvideoheight = 360;
        // Transcript bits.
        if ($havesubtitles) {
            $poptions->transcripturl = $mediaurl . '.vtt';
            $poptions->component = CONSTANTS::M_COMP;
            $poptions->containerid = \html_writer::random_id(CONSTANTS::M_COMP . '_');
            $poptions->cssprefix = CONSTANTS::M_COMP . '_transcript';
            $PAGE->requires->js_call_amd(CONSTANTS::M_COMP . '/interactivetranscript', 'init', [$poptions]);
            $PAGE->requires->strings_for_js(['transcripttitle'], CONSTANTS::M_COMP);
        }else{
            $poptions->notranscript = true;
        }

        if($this->class_name() == 'qtype_cloudpoodll_video') {
            $player = $this->render_from_template(constants::M_COMP . '/videoplayerstandard', $poptions);
        }else{
            $player = $this->render_from_template(constants::M_COMP . '/audioplayerstandard', $poptions);
        }

        return $player;

    }

    /**
     * @return string The HTML for the textarea.
     */
    protected function fetch_recorder($qa, $step, $roptions, $question, $inputname, $draftid = 0) {
        global $CFG, $USER;

        $width = '';
        $height = '';
        $recorderskin = '';
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
                        // Bmr 123, once, standard.
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
                    case constants::SKIN_SCREEN:
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

        // Transcription defaults.
        $transcriber = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
        $chrometranscribe = '0';
        $subtitle = "0";
        $hints->encoder = 'auto';

        // Shadowing.
        $hints->shadowing = $question->noaudiofilters ? 1 : 0;

        // Branch based on which transcriber we are using.
        switch($question->transcriber) {
            // Amazon transcribe.
            case constants::TRANSCRIBER_AMAZONTRANSCRIBE:
                $cantranscribe = utils::can_transcribe($roptions);
                if ($cantranscribe) {
                    $transcriber = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
                    $subtitle = "1";
                } else{
                    $transcriber = constants::TRANSCRIBER_NONE;
                }
                break;

            // Chrometranscribe.
            case constants::TRANSCRIBER_GOOGLECHROME:
                $chrometranscribe = '1';
                break;

                // Google cloud speech.
            case constants::TRANSCRIBER_GOOGLECLOUDSPEECH:
                // We can not use google cloud speech for video, so do not even try.
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

        // Transcode.
        $transcode = ($question->transcode ? '1' : '0');

        // Time limit.
        $timelimit = $question->timelimit;

        // Fetch cloudpoodll token.
        $apiuser = get_config(CONSTANTS::M_COMP, 'apiuser');
        $apisecret = get_config(CONSTANTS::M_COMP, 'apisecret');

        // Id user has errors with tokens or cloudpoodll API send those back.
        if (empty($apiuser) || empty($apisecret)) {
            $message = get_string('nocredentials', constants::M_COMP,
                    $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            $errormessage = $this->show_problembox($message);
            return $errormessage;

        } else {
            // Fetch token.
            $token = utils::fetch_token($apiuser, $apisecret);

            // Check token authenticated and no errors in it.
            $errormessage = utils::fetch_token_error($token);
            if (!empty($errormessage)) {
                $errormessage = $this->show_problembox($errormessage);
                return $errormessage;
            }
        }

        // Any recorder hints ... get sorted here.
        $stringhints = base64_encode(json_encode($hints));

        // the elementid of the div in the DOM
        $domid = html_writer::random_id('');

        $toptions = new \stdClass();
        $toptions->recid = 'therecorder_' . $domid;
        $toptions->dataid = 'therecorder_' . $domid;
        $toptions->parent = $CFG->wwwroot;
        $toptions->owner = hash('md5', $USER->username);
        $toptions->localloader = constants::LOADER_URL;
        $toptions->cloudpoodllurl = utils::get_cloud_poodll_server();
        $toptions->recordertype = $recordertype;
        $toptions->appid = constants::APPID;
        $toptions->recorderskin = $recorderskin;
        $toptions->width = $width;
        $toptions->height = $height;
        $toptions->updatecontrol = $inputname;
        $toptions->timelimit = $timelimit;
        $toptions->transcode = $transcode;
        $toptions->transcribe = $transcriber;
        $toptions->subtitle = $subtitle;
        $toptions->speechevents = $chrometranscribe;
        $toptions->language = $question->language;
        $toptions->expiredays = $question->expiredays;
        $toptions->awsregion = $roptions->awsregion;
        $toptions->fallback = $roptions->fallback;
        $toptions->string_hints = $stringhints;
        $toptions->token = $token;


        if ($recordertype == constants::REC_AUDIO) {
            $toptions->iframeclass = constants::CLASS_AUDIOREC_IFRAME;
            $recorderhtml = $this->render_from_template(constants::M_COMP . '/audiorecordercontainer', $toptions);
        } else {
            $toptions->iframeclass = constants::CLASS_VIDEOREC_IFRAME;
            $recorderhtml = $this->render_from_template(constants::M_COMP . '/videorecordercontainer', $toptions);
        }

        // Set up the AMD for the recorder.
        $opts = [
                'component' => CONSTANTS::M_COMP,
                'data_id' => 'therecorder_' . $domid,
                'inputname' => $inputname,
                'transcriber' => $transcriber,
                'safesave' => $question->safesave,
        ];

        $this->page->requires->js_call_amd(CONSTANTS::M_COMP . '/cloudpoodllhelper', 'init', [$opts]);
        return $recorderhtml;
    }

    /**
     * Return HTML to display message about problem.
     */
    public function show_problembox($msg) {
        $output = '';
        $output .= $this->output->box_start(constants::M_COMP . '_problembox');
        $output .= $this->notification($msg, 'warning');
        $output .= $this->output->box_end();
        return $output;
    }

    /**
     * Return HTML for the manual comment area.
     *
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return string
     */
    public function manual_comment(question_attempt $qa, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }

        $question = $qa->get_question();
        return html_writer::nonempty_tag('div', $question->format_text(
                $question->graderinfo, $question->graderinfoformat, $qa, 'qtype_poodllrecording',
                \qtype_cloudpoodll\constants::FILEAREA_GRADERINFO, $question->id), ['class' => 'graderinfo']);
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

/**
 * An cloudpoodll format renderer for cloudpoodlls where the student should draw on a whiteboard.
 *
 * @copyright  2024Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_cloudpoodll_whiteboard_renderer extends qtype_cloudpoodll_renderer {
    protected function class_name() {
        return 'qtype_cloudpoodll_whiteboard';
    }

    protected function get_draft_id_suffix() {
        return ':itemid';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context, $mediaurl = null) {
        // Fetch file from storage and figure out URL.
        $storedfiles = $qa->get_last_qt_files($name, $context->id);
        $thefile = false;
        foreach ($storedfiles as $sf) {
            // When we find the file that matches the filename in $step, use that.
            $usefilename = (string) $step->get_qt_var($name);
            // Strip any file hash comments (added by Moodle question file loader).
            $usefilename = preg_replace('/<!--.*-->/', '', $usefilename);
            $storedfilename = $sf->get_filename();
            if ($usefilename === $storedfilename) {
                $thefile = $sf;
                break;
            }
        }

        // Now call the parent to handle player rendering.
        // We pass the mediaurl from the file if we found one.
        if ($mediaurl === null) {
            $mediaurl = '';
            if ($thefile) {
                $mediaurl = $qa->get_response_file_url($thefile);
            }
        }

        return parent::response_area_read_only($name, $qa, $step, $lines, $context, $mediaurl);
    }

    protected function fetch_player($mediaurl, $language, $havesubtitles = false, $question = null) {
        $poptions = new \stdClass();
        $poptions->mediaurl = $mediaurl;
        return $this->render_from_template(constants::M_COMP . '/whiteboardplayer', $poptions);
    }

    protected function fetch_recorder($qa, $step, $roptions, $question, $inputname, $draftid = 0) {
        global $CFG, $USER;

        $width = !empty($question->whiteboardwidth) ? $question->whiteboardwidth : '800';
        $height = !empty($question->whiteboardheight) ? $question->whiteboardheight : '600';

        // fetch cloudpoodll token
        $apiuser = get_config(CONSTANTS::M_COMP, 'apiuser');
        $apisecret = get_config(CONSTANTS::M_COMP, 'apisecret');

        if(empty($apiuser) || empty($apisecret)){
             return $this->show_problembox(get_string('nocredentials', constants::M_COMP, $CFG->wwwroot . constants::M_PLUGINSETTINGS));
        }
        $token = utils::fetch_token($apiuser, $apisecret);
        $errormessage = utils::fetch_token_error($token);
        if(!empty($errormessage)){
             return $this->show_problembox($errormessage);
        }

        $domid = html_writer::random_id('');
        $toptions = new \stdClass();
        $toptions->recid = 'therecorder_' . $domid;
        $toptions->dataid = 'therecorder_' . $domid;
        $toptions->parent = $CFG->wwwroot;
        $toptions->owner = hash('md5', $USER->username);
        $toptions->localloader = constants::LOADER_URL;
        $toptions->cloudpoodllurl = utils::get_cloud_poodll_server();
        $toptions->recordertype = 'whiteboard';
        $toptions->appid = constants::APPID;
        $toptions->width = $width;
        $toptions->height = $height;
        $toptions->updatecontrol = $inputname;
        $toptions->language = $question->language;
        $toptions->token = $token;

        $toptions->uniqid = $domid;
        $toptions->vectorcontrol = $inputname . 'vector';
        $toptions->draftitemid = $draftid;

        // Background image.
        $toptions->backimage = '';
        $fs = get_file_storage();
        $context = \context_system::instance();
        if (isset($question->contextid)) {
            $context = \context::instance_by_id($question->contextid);
        }
        $files = $fs->get_area_files($context->id, constants::M_COMP, constants::FILEAREA_QRESOURCE, $question->id, 'filename', false);
        if ($files) {
            $file = reset($files);
            $toptions->backimage = $qa->rewrite_pluginfile_urls('@@PLUGINFILE@@/' . $file->get_filename(), $file->get_component(), $file->get_filearea(), $file->get_itemid());
        }

        // Vector data.
        $toptions->vdata = $step->get_qt_var('answervector');
        if (!$toptions->vdata) {
            $toptions->vdata = '';
        }

        return $this->render_from_template(constants::M_COMP . '/whiteboardrecorder', $toptions);
    }
}
