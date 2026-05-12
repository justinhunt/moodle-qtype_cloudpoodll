<?php
/**
 * External.
 *
 * @package qtype_cloudpoodll
 * @author  Justin Hunt - Poodll.com
 */

namespace qtype_cloudpoodll;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/externallib.php');
use external_api;
use external_function_parameters;
use external_value;

/**
 * External class.
 *
 * @package qtype_cloudpoodll
 * @author  Justin Hunt - Poodll.com
 */
class external extends external_api {

    /**
     * Upload whiteboard image.
     *
     * @param string $base64data
     * @param int $draftitemid
     * @param string $filename
     * @return string
     */
    public static function upload_whiteboard_image($base64data, $draftitemid, $filename) {
        global $USER;

        $params = self::validate_parameters(self::upload_whiteboard_image_parameters(), [
            'base64data' => $base64data,
            'draftitemid' => $draftitemid,
            'filename' => $filename,
        ]);
        extract($params);

        // Check there is no metadata prefixed to the base 64.
        $metapos = strrpos($base64data, ",");
        if ($metapos) {
            $base64data = substr($base64data, $metapos + 1);
        }

        // Decode the data.
        $filecontent = base64_decode($base64data);

        // Save the file.
        $fs = get_file_storage();
        $filerecord = new \stdClass();
        $filerecord->contextid = \context_user::instance($USER->id)->id;
        $filerecord->component = 'user';
        $filerecord->filearea = 'draft';
        $filerecord->itemid = $draftitemid;
        $filerecord->filepath = '/';
        $filerecord->filename = $filename;
        $filerecord->userid = $USER->id;

        // If file already exists, delete it.
        if ($fs->file_exists($filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename)) {
            $file = $fs->get_file($filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename);
            $file->delete();
        }

        $fs->create_file_from_string($filerecord, $filecontent);

        return $filename;
    }

    /**
     * Parameters for upload_whiteboard_image.
     *
     * @return external_function_parameters
     */
    public static function upload_whiteboard_image_parameters() {
        return new external_function_parameters([
            'base64data' => new external_value(PARAM_RAW, 'Base64 image data'),
            'draftitemid' => new external_value(PARAM_INT, 'Draft item id'),
            'filename' => new external_value(PARAM_FILE, 'Filename')
        ]);
    }

    /**
     * Returns for upload_whiteboard_image.
     *
     * @return external_value
     */
    public static function upload_whiteboard_image_returns() {
        return new external_value(PARAM_RAW, 'The filename');
    }
}
