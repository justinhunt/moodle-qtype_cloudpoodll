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

namespace qtype_cloudpoodll\setting;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

/**
 * No setting - just heading and text.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class apicredshelper extends \admin_setting {

    /** @var mixed int index of template*/
    public $templateindex;
    /** @var array template data for spec index */
    public $presetdata;
    public $visiblename;
    public $information;

    const CLEARTEMPLATEKEY = 'cleartemplate';

    /**
     * not a setting, just text
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $heading heading
     * @param string $information text in box
     */
    public function __construct($name, $visiblename, $information, $default) {
        $this->nosave = true;
        $this->visiblename = $visiblename;
        $this->information = $information;
        parent::__construct($name, $visiblename, $information, $default);
    }

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_setting() {
        return true;
    }//end of get_setting

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_defaultsetting() {
        return true;
    }//get_defaultsetting

    /**
     * Never write settings
     * @return string Always returns an empty string
     */
    public function write_setting($data) {
        // do not write any setting
        return '';
    }//write_setting

    /**
     * Returns an HTML string
     * @return string Returns an HTML string
     */
    public function output_html($data, $query='') {
        global $PAGE;

        $launchbutton = \html_writer::tag('button', 'Open Me', ['id' => 'id_s_qtype_cloudpoodll_apihelperbutton', 'type' => 'button']);
        return $launchbutton;
        /*
        $presetscontrol = \html_writer::tag('input', '', array('id' => 'id_s_filter_poodll_presetdata_' . $this->templateindex, 'type' => 'hidden', 'value' => $presetsjson));


        //Add javascript handler for presets
        $PAGE->requires->js_call_amd('filter_poodll/template_presets_amd',
        'init',array(array('templateindex'=>$this->templateindex)));

        $select = \html_writer::select($usearray,'filter_poodll/presets','','--custom--');

        $dragdropsquare = \html_writer::tag('div',get_string('bundle','filter_poodll'),array('id' => 'id_s_filter_poodll_dragdropsquare_' . $this->templateindex,
        'class' => 'filter_poodll_dragdropsquare'));

        return format_admin_setting($this, $this->visiblename,
        $dragdropsquare .'<div class="form-text defaultsnext">'. $presetscontrol . $select .  '</div>',
        $this->information, true, '','', $query);
        */
    }//end of output html

    public static function set_preset_to_config($preset, $templateindex) {

        /*
        $fields = array();
        $fields['name']='templatename';
        $fields['key']='templatekey';
        $fields['version']='templateversion';
        $fields['instructions']='templateinstructions';
        $fields['body']='template';
        $fields['bodyend']='templateend';
        $fields['showatto']='template_showatto';
        $fields['showplayers']='template_showplayers';
        $fields['requirecss']='templaterequire_css';
        $fields['requirejs']='templaterequire_js';
        $fields['shim']='templaterequire_js_shim';
        $fields['defaults']='templatedefaults';
        $fields['amd']='template_amd';
        $fields['script']='templatescript';
        $fields['style']='templatestyle';
        $fields['dataset']='dataset';
        $fields['datavars']='datavars';


            //If we are setting the template, then lets do that.
        foreach($fields as $fieldkey=>$fieldname){
        if(array_key_exists($fieldkey,$preset)){
        $fieldvalue=$preset[$fieldkey];
        }else{
        $fieldvalue='';
        }
        set_config($fieldname . '_' . $templateindex, $fieldvalue, 'filter_poodll');

        }
        */
    }//End of set_preset_to_config


}//end of class
