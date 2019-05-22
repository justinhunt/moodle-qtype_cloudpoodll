<?php

namespace qtype_cloudpoodll;

/**
 * Created by PhpStorm.
 * User: justin
 * Date: 17/10/07
 * Time: 9:27
 */
class constants {
    const RESPONSEFORMAT_AUDIO = 'audio';
    const RESPONSEFORMAT_VIDEO = 'video';
    const FILEAREA_QRESOURCE = 'qresource';
    const FILEAREA_GRADERINFO = 'graderinfo';
    const M_COMP = 'qtype_cloudpoodll';
    const M_TABLE = 'qtype_cloudpoodll_opts';

    const APPID = 'cloudpoodll_api';//'qtype_cloudpoodll';

    const REC_AUDIO = 'audio';
    const REC_VIDEO = 'video';

    const SKIN_PLAIN = 'standard';
    const SKIN_BMR = 'bmr';
    const SKIN_123 = 'onetwothree';
    const SKIN_FRESH = 'fresh';
    const SKIN_ONCE = 'once';
    const SKIN_UPLOAD = 'upload';
    const SKIN_PUSH = 'push';

    const FALLBACK_UPLOAD = 'upload';
    const FALLBACK_IOSUPLOAD = 'iosupload';
    const FALLBACK_WARNING = 'warning';
    const PROCESSING = 'processing';
    const BLANK = 'empty';

    const CLASS_REC_CONTAINER = 'qtype_cloudpoodll_rec_cont';
    const CLASS_REC_OUTER = 'qtype_cloudpoodll_rec_outer';
    const ID_REC = 'qtype_cloudpoodll_therecorder';
    const ID_UPDATE_CONTROL = 'qtype_cloudpoodll_updatecontrol';
    const NAME_UPDATE_CONTROL = 'filename';

    const REGION_USEAST1 = 'useast1';
    const REGION_TOKYO = 'tokyo';
    const REGION_DUBLIN = 'dublin';
    const REGION_SYDNEY = 'sydney';
    const REGION_OTTAWA = 'ottawa';
    const REGION_SAOPAULO = 'saopaulo';
    const REGION_FRANKFURT = 'frankfurt';
    const REGION_LONDON = 'london';

    const LANG_ENUS = 'en-US';
    const LANG_ENUK = 'en-UK';
    const LANG_ENAU = 'en-AU';
    const LANG_ESUS = 'es-US';
    const LANG_FRCA = 'fr-CA';
    const LANG_FRFR = 'fr-FR';
    const LANG_ITIT = 'it-IT';
    const LANG_PTBR = 'pt-BR';
    const LANG_KOKR = 'ko-KR';
    const LANG_DEDE = 'de-DE';
    const LANG_HIIN = 'hi-IN';
    const LANG_ENIN = 'en-IN';
    const LANG_ESES = 'es-ES';


    const TRANSCRIBER_NONE = 0;
    const TRANSCRIBER_AMAZONTRANSCRIBE = 1;
    const TRANSCRIBER_GOOGLECLOUDSPEECH = 2;
    const TRANSCRIBER_GOOGLECHROME = 3;


    const LOADER_URL = '/question/type/cloudpoodll/cloudpoodll/cloudpoodllloader.html';
    const REFRESH_URL = '/question/type/cloudpoodll/cloudpoodll/refreshtoken.php';

    const extra_fields = ['responseformat', 'graderinfo', 'graderinfoformat', 'qresource', 'language', 'expiredays',
            'transcriber', 'transcode', 'audioskin', 'videoskin', 'timelimit'];
}