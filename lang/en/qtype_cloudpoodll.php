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
 * Strings for component 'qtype_cloudpoodll', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    qtype
 * @subpackage cloudpoodll
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['formateditor'] = 'HTML editor';
$string['formatvideo'] = 'Video response';
$string['formataudio'] = 'Audio response';
$string['graderinfo'] = 'Information for graders';
$string['nlines'] = '{$a} lines';
$string['cloudpoodll'] = 'Cloud Poodll';
$string['pluginname'] = 'Cloud Poodll';
$string['pluginname_help'] =
        'In response to a question (that may include an image) the respondent records an answer. The Cloud Poodll question will not be assigned a grade until it has been reviewed by a teacher and manually graded.';
$string['pluginname_link'] = 'question/type/cloudpoodll';
$string['pluginnameadding'] = 'Adding a Cloud Poodll recording question';
$string['pluginnameediting'] = 'Editing a Cloud Poodll question';
$string['pluginnamesummary'] = 'Allows an audio recording, video recording. This must then be graded manually.';
$string['responseformat'] = 'Response format';
$string['qresource'] = 'Question resource';
$string['norecording'] = 'no recording found';
$string['currentresponse'] = 'Current Response:<br />';
$string['privacy:metadata'] = 'The Cloud Poodll Question plugin does store personal data.';

$string['pluginname_help'] =
        'In response to a question that may include an image, the respondent speaks an answer of one or more paragraphs. Initially, a grade is awarded automatically based on the number of chars, words, sentences or paragarphs, and the presence of certain target phrases. The automatic grade may be overridden later by the teacher.';
$string['pluginname_link'] = 'question/type/cloudpoodll';
$string['pluginnameadding'] = 'Adding a Cloud Poodll question';
$string['pluginnameediting'] = 'Editing a Cloud Poodll question';
$string['pluginnamesummary'] =
        'Allows a short speech segment, consisting of several sentences or paragraphs, to be submitted as a question response. The text will be transcribed after a short delay.';

$string['privacy:metadata'] = 'The Cloud Poodll question type plugin does not store any personal data.';

// CloudPoodll settings and options
$string['formataudio'] = "Audio recording";
$string['formatvideo'] = "Video recording";
$string['formatupload'] = "Upload media file";

$string['recorder'] = 'Recorder type';
$string['recorderaudio'] = 'Audio recorder';
$string['recordervideo'] = 'Video recorder';
$string['defaultrecorder'] = 'Recorder type';
$string['defaultrecorder_details'] = '';

$string['apiuser'] = 'Poodll API User ';
$string['apiuser_details'] = 'The Poodll account username that authorises Poodll on this site.';
$string['apisecret'] = 'Poodll API Secret ';
$string['apisecret_details'] =
        'The Poodll API secret. See <a href= "https://support.poodll.com/support/solutions/articles/19000083076-cloud-poodll-api-secret">here</a> for more details';
$string['language'] = 'Speaker language';

$string['useast1'] = 'US East';
$string['tokyo'] = 'Tokyo, Japan';
$string['sydney'] = 'Sydney, Australia';
$string['dublin'] = 'Dublin, Ireland';
$string['ottawa'] = 'Ottawa, Canada';
$string['frankfurt'] = 'Frankfurt, Germany';
$string['london'] = 'London, U.K';
$string['saopaulo'] = 'Sao Paulo, Brazil';
$string['mumbai'] = 'Mumbai, India';
$string['singapore'] = 'Singapore';

$string['forever'] = 'Never expire';

$string['en-us'] = 'English (US)';
$string['en-gb'] = 'English (GB)';
$string['en-au'] = 'English (AU)';
$string['en-in'] = 'English (IN)';
$string['es-es'] = 'Spanish (ES)';
$string['es-us'] = 'Spanish (US)';
$string['fr-fr'] = 'French (FR.)';
$string['fr-ca'] = 'French (CA)';
$string['ko-kr'] = 'Korean(KR)';
$string['pt-br'] = 'Portuguese(BR)';
$string['it-it'] = 'Italian(IT)';
$string['de-de'] = 'German(DE)';
$string['hi-in'] = 'Hindi(IN)';
$string['ko-kr'] = 'Korean';
$string['ar-ae'] = 'Arabic (Gulf)';
$string['ar-sa'] = 'Arabic (Modern Standard)';
$string['zh-cn'] = 'Chinese (Mandarin-Mainland)';
$string['nl-nl'] = 'Dutch';
$string['en-ie'] = 'English (Ireland)';
$string['en-wl'] = 'English (Wales)';
$string['en-ab'] = 'English (Scotland)';
$string['fa-ir'] = 'Farsi';
$string['de-ch'] = 'German (Swiss)';
$string['he-il'] = 'Hebrew';
$string['id-id'] = 'Indonesian';
$string['ja-jp'] = 'Japanese';
$string['ms-my'] = 'Malay';
$string['pt-pt'] = 'Portuguese (PT)';
$string['ru-ru'] = 'Russian';
$string['ta-in'] = 'Tamil';
$string['te-in'] = 'Telegu';
$string['tr-tr'] = 'Turkish';

$string['awsregion'] = 'AWS Region';
$string['region'] = 'AWS Region';
$string['expiredays'] = 'Days to keep file';

$string['timelimit'] = 'Recording time limit';
$string['currentsubmission'] = 'Current Submission:';

$string['recordertype'] = 'Cloud Poodll recording type';
$string['audioskin'] = 'Audio recorder skin';
$string['videoskin'] = 'Video recorder skin';
$string['skinplain'] = 'Plain';
$string['skinbmr'] = 'Burnt Rose';
$string['skinfresh'] = 'Fresh (audio only)';
$string['skin123'] = 'One Two Three';
$string['skinonce'] = 'Once';
$string['skinupload'] = 'Upload';
$string['skinpush'] = 'Push';

$string['fallback'] = 'non-HTML5 Fallback';
$string['fallback_details'] =
        'If the browser does not support HTML5 recording for the selected mediatype, fallback to an upload screen or a warning.';
$string['fallbackupload'] = 'Upload';
$string['fallbackiosupload'] = 'iOS: upload, else warning';
$string['fallbackwarning'] = 'Warning';

$string['displaysubs'] = '{$a->subscriptionname} : expires {$a->expiredate}';
$string['noapiuser'] = 'No API user entered. Plugin will not work correctly.';
$string['noapisecret'] = 'No API secret entered. Plugin will not work correctly.';
$string['credentialsinvalid'] = 'The API user and secret entered could not be used to get access. Please check them.';
$string['appauthorised'] = 'Cloud Poodll question is authorised for this site.';
$string['appnotauthorised'] = 'Cloud Poodll question is NOT authorised for this site.';
$string['refreshtoken'] = 'Refresh license information';
$string['notokenincache'] = 'Refresh to see license information. Contact support if there is a problem.';

//these errors are displayed on quiz page
$string['nocredentials'] = 'API user and secret not entered. Please enter them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['novalidcredentials'] = 'API user and secret were rejected and could not gain access. Please check them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['nosubscriptions'] = "There is no current subscription for this site/plugin.";

$string['transcode'] = 'Transcode';
$string['transcode_details'] = 'Transcode audio to MP3 and video to MP4.';
$string['transcriber'] = 'Transcriber';
$string['transcriber_details'] = 'The transcription engine to use';
$string['transcriber_amazontranscribe'] = 'Standard transcription';
$string['transcriber_googlechrome'] = 'Instant transcription (Chrome only)';
$string['transcriber_googlecloud'] = 'Fast transcription (audio only, less than 60 seconds)';
$string['transcriber_none'] = 'No transcription';
$string['transcriptnotready'] = 'Transcript not ready yet';
$string['transcripttitle'] = 'Transcript';

$string['notimelimit'] = 'No time limit';
$string['xsecs'] = '{$a} seconds';
$string['onemin'] = '1 minute';
$string['xmins'] = '{$a} minutes';
$string['oneminxsecs'] = '1 minutes {$a} seconds';
$string['xminsecs'] = '{$a->minutes} minutes {$a->seconds} seconds';

$string['apicredshelper']="Api Creds Helper";
$string['apicredshelper_details']="--";
