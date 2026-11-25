<?php
/**
 * Services.
 *
 * @package qtype_cloudpoodll
 * @author  Justin Hunt - Poodll.com
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'qtype_cloudpoodll_upload_whiteboard_image' => array(
        'classname'   => 'qtype_cloudpoodll\external',
        'methodname'  => 'upload_whiteboard_image',
        'description' => 'Upload whiteboard image',
        'type'        => 'write',
        'ajax'        => true,
    ),
    'qtype_cloudpoodll_fetch_upload_details' => array(
        'classname'   => '\qtype_cloudpoodll\external\fetch_upload_details',
        'methodname'  => 'execute',
        'description' => 'Get the S3 upload details for one local recorder recording',
        'type'        => 'write',
        'ajax'        => true,
    ),
);
