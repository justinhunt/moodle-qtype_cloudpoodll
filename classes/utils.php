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
 *
 *
 * @package   qtype_cloudpoodll
 * @copyright 2018 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_cloudpoodll;

defined('MOODLE_INTERNAL') || die();

class utils {

    public static function fetch_options_recorders() {
        $recoptions = [constants::REC_AUDIO => get_string("recorderaudio", constants::M_COMP),
                constants::REC_VIDEO => get_string("recordervideo", constants::M_COMP)];
        return $recoptions;
    }

    public static function fetch_options_fallback() {
        $options = [constants::FALLBACK_UPLOAD => get_string("fallbackupload", constants::M_COMP),
                constants::FALLBACK_IOSUPLOAD => get_string("fallbackiosupload", constants::M_COMP),
                constants::FALLBACK_WARNING => get_string("fallbackwarning", constants::M_COMP)];
        return $options;
    }

    public static function fetch_options_players() {
            $options = [ constants::PLAYERTYPE_DEFAULT => get_string("playertypedefault", constants::M_COMP),
                constants::PLAYERTYPE_INTERACTIVETRANSCRIPT  => get_string("playertypeinteractivetranscript", constants::M_COMP)];
            return $options;
    }

    public static function fetch_options_transcribers() {
        $options = [constants::TRANSCRIBER_NONE => get_string("transcriber_none", constants::M_COMP),
                constants::TRANSCRIBER_AMAZONTRANSCRIBE => get_string("transcriber_amazontranscribe", constants::M_COMP),
               // constants::TRANSCRIBER_GOOGLECLOUDSPEECH => get_string("transcriber_googlecloud", constants::M_COMP),
               // constants::TRANSCRIBER_GOOGLECHROME => get_string("transcriber_googlechrome", constants::M_COMP),
        ];
        return $options;
    }

    public static function get_timelimit_options() {
        $opts = [
                0 => get_string("notimelimit", constants::M_COMP),
                30 => get_string("xsecs", constants::M_COMP, '30'),
                45 => get_string("xsecs", constants::M_COMP, '45'),
                60 => get_string("onemin", constants::M_COMP),
                90 => get_string("oneminxsecs", constants::M_COMP, '30'),
        ];
        for($x = 2; $x <= 30; $x++){
            $opts[$x * 60] = get_string("xmins", constants::M_COMP, $x);
            $opts[($x * 60) + 30] = get_string("xminsecs", constants::M_COMP, ['minutes' => $x, 'seconds' => 30]);
        }
        return $opts;
    }

    public static function fetch_options_skins($rectype = constants::REC_VIDEO) {
        $recoptions = [];
        switch ($rectype) {
            case constants::REC_AUDIO:
                $recoptions[constants::SKIN_PLAIN] = get_string("skinplain", constants::M_COMP);
                $recoptions[constants::SKIN_BMR] = get_string("skinbmr", constants::M_COMP);
                $recoptions[constants::SKIN_123] = get_string("skin123", constants::M_COMP);
                $recoptions[constants::SKIN_FRESH] = get_string("skinfresh", constants::M_COMP);
                $recoptions[constants::SKIN_ONCE] = get_string("skinonce", constants::M_COMP);
                $recoptions[constants::SKIN_UPLOAD] = get_string("skinupload", constants::M_COMP);
                $recoptions[constants::SKIN_PUSH] = get_string("skinpush", constants::M_COMP);
                break;
            case constants::REC_VIDEO:
            default:
                $recoptions[constants::SKIN_PLAIN] = get_string("skinplain", constants::M_COMP);
                $recoptions[constants::SKIN_BMR] = get_string("skinbmr", constants::M_COMP);
                $recoptions[constants::SKIN_123] = get_string("skin123", constants::M_COMP);
                $recoptions[constants::SKIN_ONCE] = get_string("skinonce", constants::M_COMP);
                $recoptions[constants::SKIN_UPLOAD] = get_string("skinupload", constants::M_COMP);

        }
        return $recoptions;
    }

    public static function get_region_options() {
        return [
                constants::REGION_USEAST1 => get_string("useast1", constants::M_COMP),
                constants::REGION_TOKYO => get_string("tokyo", constants::M_COMP),
                constants::REGION_SYDNEY => get_string("sydney", constants::M_COMP),
                constants::REGION_DUBLIN => get_string("dublin", constants::M_COMP),
                constants::REGION_OTTAWA => get_string("ottawa", constants::M_COMP),
                constants::REGION_FRANKFURT => get_string("frankfurt", constants::M_COMP),
                constants::REGION_LONDON => get_string("london", constants::M_COMP),
                constants::REGION_SAOPAULO => get_string("saopaulo", constants::M_COMP),
                constants::REGION_SINGAPORE => get_string("singapore", constants::M_COMP),
                constants::REGION_MUMBAI => get_string("mumbai", constants::M_COMP),
                constants::REGION_CAPETOWN => get_string("capetown", constants::M_COMP),
                constants::REGION_BAHRAIN => get_string("bahrain", constants::M_COMP),
        ];
    }

    public static function get_expiredays_options() {
        return [
                "1" => "1",
                "3" => "3",
                "7" => "7",
                "30" => "30",
                "90" => "90",
                "180" => "180",
                "365" => "365",
                "730" => "730",
                "9999" => get_string('forever', constants::M_COMP),
        ];
    }


    public static function get_lang_options() {
        return [
            constants::LANG_ARAE => get_string('ar-ae', constants::M_COMP),
            constants::LANG_ARSA => get_string('ar-sa', constants::M_COMP),
            constants::LANG_EUES => get_string('eu-es', constants::M_COMP),
            constants::LANG_BGBG => get_string('bg-bg', constants::M_COMP),
            constants::LANG_HRHR => get_string('hr-hr', constants::M_COMP),
            constants::LANG_ZHCN => get_string('zh-cn', constants::M_COMP),
            constants::LANG_CSCZ => get_string('cs-cz', constants::M_COMP),
            constants::LANG_DADK => get_string('da-dk', constants::M_COMP),
            constants::LANG_NLNL => get_string('nl-nl', constants::M_COMP),
            constants::LANG_ENUS => get_string('en-us', constants::M_COMP),
            constants::LANG_ENGB => get_string('en-gb', constants::M_COMP),
            constants::LANG_ENAU => get_string('en-au', constants::M_COMP),
            constants::LANG_ENIN => get_string('en-in', constants::M_COMP),
            constants::LANG_ENIE => get_string('en-ie', constants::M_COMP),
            constants::LANG_ENWL => get_string('en-wl', constants::M_COMP),
            constants::LANG_ENAB => get_string('en-ab', constants::M_COMP),
            constants::LANG_FAIR => get_string('fa-ir', constants::M_COMP),
            constants::LANG_FIFI => get_string('fi-fi', constants::M_COMP),
            constants::LANG_FRCA => get_string('fr-ca', constants::M_COMP),
            constants::LANG_FRFR => get_string('fr-fr', constants::M_COMP),
            constants::LANG_DEDE => get_string('de-de', constants::M_COMP),
            constants::LANG_DECH => get_string('de-ch', constants::M_COMP),
            constants::LANG_ELGR => get_string('el-gr', constants::M_COMP),
            constants::LANG_HIIN => get_string('hi-in', constants::M_COMP),
            constants::LANG_HEIL => get_string('he-il', constants::M_COMP),
            constants::LANG_HUHU => get_string('hu-hu', constants::M_COMP),
            constants::LANG_ISIS => get_string('is-is', constants::M_COMP),
            constants::LANG_IDID => get_string('id-id', constants::M_COMP),
            constants::LANG_ITIT => get_string('it-it', constants::M_COMP),
            constants::LANG_JAJP => get_string('ja-jp', constants::M_COMP),
            constants::LANG_KOKR => get_string('ko-kr', constants::M_COMP),
            constants::LANG_LTLT => get_string('lt-lt', constants::M_COMP),
            constants::LANG_LVLV => get_string('lv-lv', constants::M_COMP),
            constants::LANG_MINZ => get_string('mi-nz', constants::M_COMP),
            constants::LANG_MSMY => get_string('ms-my', constants::M_COMP),
            constants::LANG_MKMK => get_string('mk-mk', constants::M_COMP),
            constants::LANG_PLPL => get_string('pl-pl', constants::M_COMP),
            constants::LANG_PTBR => get_string('pt-br', constants::M_COMP),
            constants::LANG_PTPT => get_string('pt-pt', constants::M_COMP),
            constants::LANG_RORO => get_string('ro-ro', constants::M_COMP),
            constants::LANG_RURU => get_string('ru-ru', constants::M_COMP),
            constants::LANG_ESUS => get_string('es-us', constants::M_COMP),
            constants::LANG_ESES => get_string('es-es', constants::M_COMP),
            constants::LANG_SKSK => get_string('sk-sk', constants::M_COMP),
            constants::LANG_SLSI => get_string('sl-si', constants::M_COMP),
            constants::LANG_SRRS => get_string('sr-rs', constants::M_COMP),
            constants::LANG_SVSE => get_string('sv-se', constants::M_COMP),
            constants::LANG_TAIN => get_string('ta-in', constants::M_COMP),
            constants::LANG_TEIN => get_string('te-in', constants::M_COMP),
            constants::LANG_TRTR => get_string('tr-tr', constants::M_COMP),
            constants::LANG_NONO => get_string('no-no', constants::M_COMP),
            constants::LANG_UKUA => get_string('uk-ua', constants::M_COMP),
            constants::LANG_VIVN => get_string('vi-vn', constants::M_COMP),
        ];
    }

    // are we willing and able to transcribe submissions?
    public static function can_transcribe($instance) {

        // we default to true
        // but it only takes one no ....
        $ret = true;

        // The regions that can transcribe
        switch($instance->awsregion){
            default:
                $ret = true;
        }

        return $ret;
    }

    // we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    // this is our helper
    public static function curl_fetch($url, $postdata = false) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();

        $result = $curl->get($url, $postdata);
        return $result;
    }

    // This is called from the settings page and we do not want to make calls out to cloud.poodll.com on settings
    // page load, for performance and stability issues. So if the cache is empty and/or no token, we just show a
    // "refresh token" links
    public static function fetch_token_for_display($apiuser, $apisecret) {
        global $CFG;

        // First check that we have an API id and secret
        // refresh token
        $refresh = \html_writer::link($CFG->wwwroot . constants::REFRESH_URL,
                        get_string('refreshtoken', constants::M_COMP)) . '<br>';

        $message = '';
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);
        if (empty($apiuser)) {
            $message .= get_string('noapiuser', constants::M_COMP) . '<br>';
        }
        if (empty($apisecret)) {
            $message .= get_string('noapisecret', constants::M_COMP);
        }

        if (!empty($message)) {
            return $refresh . $message;
        }

        // Fetch from cache and process the results and display
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMP, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');

        // if we have no token object the creds were wrong ... or something
        if (!($tokenobject)) {
            $message = get_string('notokenincache', constants::M_COMP);
            // if we have an object but its no good, creds werer wrong ..or something
        } else if (!property_exists($tokenobject, 'token') || empty($tokenobject->token)) {
            $message = get_string('credentialsinvalid', constants::M_COMP);
            // if we do not have subs, then we are on a very old token or something is wrong, just get out of here.
        } else if (!property_exists($tokenobject, 'subs')) {
            $message = 'No subscriptions found at all';
        }
        if (!empty($message)) {
            return $refresh . $message;
        }

        // we have enough info to display a report. Lets go.
        foreach ($tokenobject->subs as $sub) {
            $sub->expiredate = date('d/m/Y', $sub->expiredate);
            $message .= get_string('displaysubs', constants::M_COMP, $sub) . '<br>';
        }
        // Is app authorised
        if (in_array(constants::M_COMP, $tokenobject->apps)) {
            $message .= get_string('appauthorised', constants::M_COMP) . '<br>';
        } else {
            $message .= get_string('appnotauthorised', constants::M_COMP) . '<br>';
        }

        return $refresh . $message;

    }

    // We need a Poodll token to make this happen
    public static function fetch_token($apiuser, $apisecret, $force = false) {

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMP, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');
        $tokenuser = $cache->get('recentpoodlluser');
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);

        // if we got a token and its less than expiry time
        // use the cached one
        if ($tokenobject && $tokenuser && $tokenuser == $apiuser && !$force) {
            if ($tokenobject->validuntil == 0 || $tokenobject->validuntil > time()) {
                return $tokenobject->token;
            }
        }

        // Send the request & save response to $resp
        $tokenurl = "https://cloud.poodll.com/local/cpapi/poodlltoken.php";
        $postdata = [
                'username' => $apiuser,
                'password' => $apisecret,
                'service' => 'cloud_poodll',
        ];
        $tokenresponse = self::curl_fetch($tokenurl, $postdata);
        if ($tokenresponse) {
            $respobject = json_decode($tokenresponse);
            if ($respobject && property_exists($respobject, 'token')) {
                $token = $respobject->token;
                // store the expiry timestamp and adjust it for diffs between our server times
                if ($respobject->validuntil) {
                    $validuntil = $respobject->validuntil - ($respobject->poodlltime - time());
                    // we refresh one hour out, to prevent any overlap
                    $validuntil = $validuntil - (1 * HOURSECS);
                } else {
                    $validuntil = 0;
                }

                // cache the token
                $tokenobject = new \stdClass();
                $tokenobject->token = $token;
                $tokenobject->validuntil = $validuntil;
                $tokenobject->subs = false;
                $tokenobject->apps = false;
                $tokenobject->sites = false;
                if (property_exists($respobject, 'subs')) {
                    $tokenobject->subs = $respobject->subs;
                }
                if (property_exists($respobject, 'apps')) {
                    $tokenobject->apps = $respobject->apps;
                }
                if (property_exists($respobject, 'sites')) {
                    $tokenobject->sites = $respobject->sites;
                }
                $cache->set('recentpoodlltoken', $tokenobject);
                $cache->set('recentpoodlluser', $apiuser);

            } else {
                $token = '';
                if ($respobject && property_exists($respobject, 'error')) {
                    // ERROR = $resp_object->error
                }
            }
        } else {
            $token = '';
        }
        return $token;
    }

    // check token and tokenobject(from cache)
    // return error message or blank if its all ok
    public static function fetch_token_error($token) {
        global $CFG;

        // check token authenticated
        if(empty($token)) {
            $message = get_string('novalidcredentials', constants::M_COMP,
                    $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            return $message;
        }

        // Fetch from cache and process the results and display.
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMP, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');

        // we should not get here if there is no token, but lets gracefully die, [v unlikely]
        if (!($tokenobject)) {
            $message = get_string('notokenincache', constants::M_COMP);
            return $message;
        }

        // We have an object but its no good, creds were wrong ..or something. [v unlikely]
        if (!property_exists($tokenobject, 'token') || empty($tokenobject->token)) {
            $message = get_string('credentialsinvalid', constants::M_COMP);
            return $message;
        }
        // if we do not have subs.
        if (!property_exists($tokenobject, 'subs')) {
            $message = get_string('nosubscriptions', constants::M_COMP);
            return $message;
        }
        // Is app authorised?
        if (!property_exists($tokenobject, 'apps') || !in_array(constants::M_COMP, $tokenobject->apps)) {
            $message = get_string('appnotauthorised', constants::M_COMP);
            return $message;
        }

        // just return empty if there is no error.
        return '';
    }

    // transcripts become ready in their own time, fetch them here
    public static function fetch_transcript($mediaurl) {
        $url = $mediaurl . '.txt';
        $transcript = self::curl_fetch($url);
        if (strpos($transcript, "<Error><Code>AccessDenied</Code>") > 0) {
            return false;
        }
        return $transcript;
    }

    // vtt data becomes ready in its own time, fetch them here
    public static function fetch_vtt($mediaurl) {
        $url = $mediaurl . '.vtt';
        $vtt = self::curl_fetch($url);
        if (strpos($vtt, "<Error><Code>AccessDenied</Code>") > 0) {
            return false;
        }
        return $vtt;
    }

}
