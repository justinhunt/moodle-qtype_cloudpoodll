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
);
