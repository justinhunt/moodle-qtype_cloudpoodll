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
use qtype_cloudpoodll\cbcredentials;

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
        if (empty($mimetype)) {
            return '';
        }

        // Strip codecs parameter (e.g. "audio/webm;codecs=opus").
        if (strpos($mimetype, ';') !== false) {
            $mimetype = explode(';', $mimetype)[0];
        }

        $mimetypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'audio/wav' => 'wav',
            'audio/ogg' => 'ogg',
            'audio/mpeg3' => 'mp3',
            'audio/mp3' => 'mp3',
            'audio/x-mpeg-3' => 'mp3',
            'audio/webm' => 'webm',
            'audio/wma' => 'wma',
            'audio/mp4' => 'm4a',
            'audio/m4a' => 'm4a',
            'audio/x-m4a' => 'm4a',
            'audio/3gpp' => '3gpp',
            'video/mpeg3' => '3gpp',
            'video/m4v' => 'm4v',
            'video/mp4' => 'mp4',
            'video/mov' => 'mov',
            'video/quicktime' => 'mov',
            'video/x-matroska' => 'webm',
            'video/webm' => 'webm',
            'video/wmv' => 'wmv',
            'video/ogg' => 'ogg',
        ];

        if (isset($mimetypes[$mimetype])) {
            return $mimetypes[$mimetype];
        }

        // Unknown mime type, guess based on mediatype.
        return (strpos($mimetype, 'video') !== false) ? 'mp4' : 'mp3';
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
        if (!empty($mediaurl) && $mediaurl !== constants::BLANK &&
            ($question->transcriber == constants::TRANSCRIBER_AMAZONTRANSCRIBE ||
             $question->transcriber == constants::TRANSCRIBER_GOOGLECLOUDSPEECH)) {
            $transcript = utils::fetch_transcript($mediaurl);
            $havesubtitles = !empty($transcript);
        }

        // Transcript could be a url, or a block of text or empty.
        // Here we turn a url into text if we can.
        if (empty($transcript)) {
            if ($question->transcriber == constants::TRANSCRIBER_AMAZONTRANSCRIBE ||
                $question->transcriber == constants::TRANSCRIBER_GOOGLECLOUDSPEECH) {
                $transcript = get_string('transcriptnotready', constants::M_COMP);
            }
            $havesubtitles = false;
        }

        // Check if teacher/grader.
        $isgrader = has_capability('mod/quiz:grade', $context);

        // Hide subtitles/transcript if using default player for the user's role.
        $player = $isgrader ? $question->teacherplayer : $question->studentplayer;
        if ($player == constants::PLAYERTYPE_DEFAULT) {
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

        // If we don't have interactive subtitles in the player, show the transcript text block.
        if (!$havesubtitles && !empty($transcript) && $transcript !== constants::BLANK) {
            $rethtml .= html_writer::div($transcript, 'qtype_cloudpoodll_transcriptdiv');
        }

        // Get details display.
        // Make sure the json and details are properly formed.
        if ($isgrader && !empty($details)) {
            $reclog = json_decode($details);
            if (json_last_error() === JSON_ERROR_NONE && !empty($reclog->recevents)) {
                $lastmimetype = '';
                foreach ($reclog->recevents as $recevent) {
                    $recevent->{$recevent->type} = 1; // Hack for mustache templates.
                    if ($recevent->type === 'uploadcommenced') {
                        $lastmimetype = $recevent->mimetype;
                    } else if ($recevent->type === 'awaitingprocessing' && !empty($recevent->targetfile)) {
                        $ext = $this->fetch_fileext_from_mimetype($lastmimetype);
                        if (!empty($ext)) {
                            $recevent->srcfile = $this->replace_url_filext($recevent->targetfile, $ext);
                            $recevent->srcfilename = pathinfo($recevent->srcfile, PATHINFO_BASENAME);
                            $recevent->targetfilename = pathinfo($recevent->targetfile, PATHINFO_BASENAME);
                        }
                    } else if ($recevent->type === 'filesubmitted') {
                        $recevent->finalfilename = pathinfo($recevent->finalfile, PATHINFO_BASENAME);
                    }
                }
                $rethtml .= $this->fetch_details_display($reclog);
            }
        }

        return $rethtml;
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
        $question = $qa->get_question();
        $fieldname = $qa->get_qt_field_name($name); // $name = "answer".

        // Setup the recorder DIV.
        $options = get_config('qtype_cloudpoodll');
        $draftid = $step->get_qt_var($name . '_draftid') ?: file_get_unused_draft_itemid();
        $recorder = $this->fetch_recorder($qa, $step, $options, $question, $fieldname, $draftid);

        // The recorder status field.
        $details = $step->get_qt_var($name . 'details');
        $templateoptions = [];
        if ($details) {
            $reclog = json_decode($details);
            if (json_last_error() === JSON_ERROR_NONE && !empty($reclog->recevents)) {
                // Find the last event that saved a recording (or default to the very last event).
                $lastevent = end($reclog->recevents);
                foreach (array_reverse($reclog->recevents) as $recevent) {
                    if ($recevent->type === 'awaitingconversion' || $recevent->type === 'filesubmitted') {
                        $lastevent = $recevent;
                        break;
                    }
                }
                $templateoptions = [
                    'lastevent' => $lastevent,
                    'insession' => false,
                    $lastevent->type => 1
                ];
            }
        }
        $answerstatus = $this->render_from_template(constants::M_COMP . '/answerstatus', $templateoptions);

        // The elementid of the div in the DOM.
        $answerstatuscontainer = html_writer::div($answerstatus, 'qtype_cloudpoodll_asc', ['id' => $fieldname . '_asc']);

        // Generate the hidden fields.
        $fields = [
            '' => $step->get_qt_var($name) ?: constants::BLANK,
            'mediaurl' => $step->get_qt_var($name . 'mediaurl') ?: '',
            'transcript' => $step->get_qt_var($name . 'transcript') ?: constants::BLANK,
            'details' => $step->get_qt_var($name . 'details') ?: '',
            'vector' => $step->get_qt_var($name . 'vector') ?: ''
        ];

        $hiddenfields = '';
        foreach ($fields as $suffix => $value) {
            $attrs = [
                'type' => 'hidden',
                'name' => $fieldname . $suffix,
                'value' => $value
            ];
            if ($suffix === 'vector') {
                $attrs['id'] = $fieldname . 'vector';
            }
            $hiddenfields .= html_writer::empty_tag('input', $attrs);
        }

        // Draft ID field.
        $hiddenfields .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => $fieldname . $this->get_draft_id_suffix(),
            'value' => $draftid
        ]);

        // Return recorder and associated hidden fields.
        return $answerstatuscontainer . $recorder . $hiddenfields;
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

        // If the credentials are missing, or cloudpoodll rejects them, send that back. Administrators
        // are pointed at the settings page and the free trial, everybody else is told who to ask.
        // A question always renders inside the attempt form, so this must not contain a form.
        $errormessage = cbcredentials::credentials_error();
        if (!empty($errormessage)) {
            return $this->show_cbcredentials_notice($errormessage);
        }

        // Fetch token.
        $token = utils::fetch_token($apiuser, $apisecret);

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
     * Return HTML to let an administrator sort out the Poodll API credentials without leaving the page.
     *
     * This contains a form, so it can only be used somewhere that is not already inside one. A
     * question is always rendered inside the attempt form, so use show_cbcredentials_notice() there.
     *
     * @param \moodle_url|string $returnurl where to send the administrator after saving
     * @param string $errormessage what is currently wrong with the credentials, if anything
     * @return string HTML
     */
    public function show_cbcredentials_setup($returnurl, $errormessage = '') {
        if (cbcredentials::can_manage()) {
            return $this->render_from_template(
                constants::M_COMP . '/cbcredentialspanel',
                cbcredentials::export_panel_data($returnurl, $errormessage)
            );
        }
        // Users who cannot fix it get no technical detail, just who to ask.
        return $this->show_problembox(get_string('cbaskadmin', constants::M_COMP));
    }

    /**
     * Return HTML explaining that the Poodll credentials need attention, safe to place inside
     * another form. Administrators are pointed at the settings page, and at the free trial when
     * there are no credentials to be found anywhere on this site.
     *
     * @param string $errormessage what is currently wrong with the credentials
     * @return string HTML
     */
    public function show_cbcredentials_notice($errormessage) {
        global $CFG;

        if (!cbcredentials::can_manage()) {
            // Users who cannot fix it get no technical detail, just who to ask.
            return $this->show_problembox(get_string('cbaskadmin', constants::M_COMP));
        }

        $links = html_writer::link(
            $CFG->wwwroot . constants::M_PLUGINSETTINGS,
            get_string('cbgotosettings', constants::M_COMP)
        );
        if (!cbcredentials::find_elsewhere()) {
            $links .= ' | ' . html_writer::link(
                $CFG->wwwroot . constants::M_URL . '/fetchcbpage.php',
                get_string('freetrial', constants::M_COMP),
                ['target' => '_blank', 'rel' => 'noopener']
            );
        }
        return $this->show_problembox($errormessage . '<br>' . $links);
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
        if ($mediaurl === null) {
            $mediaurl = '';
            $usefilename = preg_replace('/<!--.*-->/', '', (string) $step->get_qt_var($name));
            foreach ($qa->get_last_qt_files($name, $context->id) as $sf) {
                if ($usefilename === $sf->get_filename()) {
                    $mediaurl = $qa->get_response_file_url($sf);
                    break;
                }
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

        // Same credentials check as fetch_recorder_html above, and same reason it must not be a form.
        $errormessage = cbcredentials::credentials_error();
        if (!empty($errormessage)) {
            return $this->show_cbcredentials_notice($errormessage);
        }
        $token = utils::fetch_token($apiuser, $apisecret);

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
