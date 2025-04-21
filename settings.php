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
 * This file defines the admin settings for this plugin
 *
 * @package   qtype_cloudpoodll
 * @copyright 2019 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use qtype_cloudpoodll\constants;
use qtype_cloudpoodll\utils;

if ($ADMIN->fulltree) {
    $plugin = constants::M_COMP;

    $name = 'apiuser';
    $label = get_string($name, $plugin);
    $details = get_string('apiuser_details', $plugin);
    $settings->add(new admin_setting_configtext("$plugin/$name", $label, $details, '', PARAM_TEXT));

    $cloudpoodllapiuser = get_config(constants::M_COMP, 'apiuser');
    $cloudpoodllapisecret = get_config(constants::M_COMP, 'apisecret');
    $showbelowapisecret = '';
    // if we have an API user and secret we fetch token
    if(!empty($cloudpoodllapiuser) && !empty($cloudpoodllapisecret)) {
        $tokeninfo = utils::fetch_token_for_display($cloudpoodllapiuser, $cloudpoodllapisecret);
        $showbelowapisecret = $tokeninfo;
        // if we have no API user and secret we show a "fetch from elsewhere on site" or "take a free trial" link
    }else{
        $amddata = ['apppath' => $CFG->wwwroot . '/' .constants::M_URL];
        $cpcomponents = ['filter_poodll', 'mod_readaloud', 'mod_wordcards', 'mod_solo', 'mod_minilesson', 'mod_englishcentral', 'mod_pchat',
            'atto_cloudpoodll', 'tinymce_cloudpoodll', 'assignfeedback_cloudpoodll', 'assignsubmission_cloudpoodll'];
        foreach($cpcomponents as $cpcomponent){
            switch($cpcomponent){
                case 'filter_poodll':
                    $apiusersetting = 'cpapiuser';
                    $apisecretsetting = 'cpapisecret';
                    break;
                case 'mod_englishcentral':
                    $apiusersetting = 'poodllapiuser';
                    $apisecretsetting = 'poodllapisecret';
                    break;
                default:
                    $apiusersetting = 'apiuser';
                    $apisecretsetting = 'apisecret';
            }
            $cloudpoodllapiuser = get_config($cpcomponent, $apiusersetting);
            if(!empty($cloudpoodllapiuser)){
                $cloudpoodllapisecret = get_config($cpcomponent, $apisecretsetting);
                if(!empty($cloudpoodllapisecret)){
                    $amddata['apiuser'] = $cloudpoodllapiuser;
                    $amddata['apisecret'] = $cloudpoodllapisecret;
                    break;
                }
            }
        }
        $showbelowapisecret = $OUTPUT->render_from_template( constants::M_COMP . '/managecreds', $amddata);
    }

    $name = 'apisecret';
    $label = get_string($name, $plugin);
    $settings->add(new admin_setting_configtext("$plugin/$name", $label, $showbelowapisecret, '', PARAM_TEXT));

    // Cloud Poodll Server.
    $settings->add(new admin_setting_configtext(constants::M_COMP .  '/cloudpoodllserver',
        get_string('cloudpoodllserver', constants::M_COMP),
        get_string('cloudpoodllserver_details', constants::M_COMP),
        constants::M_DEFAULT_CLOUDPOODLL, PARAM_URL));



    $name = 'awsregion';
    $label = get_string($name, $plugin);
    $default = constants::REGION_USEAST1;
    $options = utils::get_region_options();
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, '', $default, $options));

    $name = 'expiredays';
    $label = get_string('expiredays', $plugin);
    $default = '365';
    $options = utils::get_expiredays_options();
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, '', $default, $options));

    $name = 'language';
    $label = get_string($name, $plugin);
    $default = 'en-US';
    $options = utils::get_lang_options();
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, '', $default, $options));

    // Audio record skin
    $name = 'audioskin';
    $label = get_string($name, $plugin);
    $details = get_string($name, $plugin);
    $default = constants::SKIN_123;
    $options = utils::fetch_options_skins(constants::REC_AUDIO);
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, $details, $default, $options));

    // Video record skin
    $name = 'videoskin';
    $label = get_string($name, $plugin);
    $details = get_string($name, $plugin);
    $options = utils::fetch_options_skins(constants::REC_VIDEO);
    $settings->add(new admin_setting_configselect("$plugin/$name", $label,
            $details, constants::SKIN_123, $options));

    // Transcriber options
    $name = 'transcriber';
    $label = get_string($name, $plugin);
    $details = get_string($name . '_details', $plugin);
    $default = constants::TRANSCRIBER_AMAZONTRANSCRIBE;
    $options = utils::fetch_options_transcribers();
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, $details, $default, $options));

    // Student Player options
    $name = 'studentplayer';
    $label = get_string($name, $plugin);
    $details = get_string($name . '_details', $plugin);
    $default = constants::PLAYERTYPE_INTERACTIVETRANSCRIPT;
    $options = utils::fetch_options_players();
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, $details, $default, $options));

    // Teacher Player options
    $name = 'teacherplayer';
    $label = get_string($name, $plugin);
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, $details, $default, $options));

    // Transcode audio/video
    $name = 'transcode';
    $label = get_string($name, $plugin);
    $details = get_string($name . '_details', $plugin);
    $options = [0 => get_string('no'), 1 => get_string('yes')];
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, $details, 1, $options));

    // Default html5 fallback
    $name = 'fallback';
    $label = get_string($name, $plugin);
    $details = get_string($name . '_details', $plugin);
    $default = constants::FALLBACK_IOSUPLOAD;
    $options = utils::fetch_options_fallback();
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, $details, $default, $options));

    // SafeSave
    $name = 'safesave';
    $label = get_string($name, $plugin);
    $details = get_string($name . '_details', $plugin);
    $options = [0 => get_string('no'), 1 => get_string('yes')];
    $settings->add(new admin_setting_configselect("$plugin/$name", $label, $details, 1, $options));

}
