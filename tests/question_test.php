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
 * Unit tests for the cloudpoodll question type.
 *
 * @package    qtype_cloudpoodll
 * @copyright  2025 Antigravity
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/cloudpoodll/question.php');
require_once($CFG->dirroot . '/question/type/cloudpoodll/questiontype.php');

/**
 * Unit tests for the cloudpoodll question type.
 *
 * @copyright  2025 Antigravity
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_cloudpoodll_test extends advanced_testcase {
    /**
     * Test saving and loading the noaudiofilters option.
     */
    public function test_save_and_load_noaudiofilters() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('qtype_cloudpoodll');
        $cat = $questiongenerator->create_question_category();

        // Create a question with noaudiofilters = 1.
        $questiondata = $questiongenerator->create_question('cloudpoodll', 'cloudpoodll',
                ['category' => $cat->id, 'noaudiofilters' => 1]);

        // Load the question.
        $question = question_bank::load_question($questiondata->id);

        // Verify the option is saved and loaded correctly.
        $this->assertEquals(1, $question->noaudiofilters);

        // Create a question with noaudiofilters = 0.
        $questiondata2 = $questiongenerator->create_question('cloudpoodll', 'cloudpoodll',
                ['category' => $cat->id, 'noaudiofilters' => 0]);

        // Load the question.
        $question2 = question_bank::load_question($questiondata2->id);

        // Verify the option is saved and loaded correctly.
        $this->assertEquals(0, $question2->noaudiofilters);
    }
}
