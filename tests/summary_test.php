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

namespace block_coursecardsuems\output;

use block_coursecardsuems\local\course_status_resolver;

/**
 * Tests for the summary output model.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class summary_test extends \advanced_testcase {

    /**
     * Courses are exported in the product sections and default collapse states.
     */
    public function test_exports_courses_grouped_by_status_sections(): void {
        global $PAGE;

        $courses = [
            ['title' => 'Closed', 'status' => course_status_resolver::CLOSED],
            ['title' => 'Open', 'status' => course_status_resolver::OPEN],
            ['title' => 'Coming soon', 'status' => course_status_resolver::COMINGSOON],
        ];

        $data = (new summary($courses, '2026/1'))->export_for_template($PAGE->get_renderer('core'));

        self::assertTrue($data->hassections);
        self::assertCount(3, $data->sections);
        self::assertSame(get_string('openplural', 'block_coursecardsuems'), $data->sections[0]['title']);
        self::assertTrue($data->sections[0]['isopen']);
        self::assertSame('grid', $data->sections[0]['layout']);
        self::assertSame('Open', $data->sections[0]['courses'][0]['title']);

        self::assertSame(get_string('comingsoonplural', 'block_coursecardsuems'), $data->sections[1]['title']);
        self::assertFalse($data->sections[1]['isopen']);
        self::assertSame('list', $data->sections[1]['layout']);
        self::assertSame('Coming soon', $data->sections[1]['courses'][0]['title']);

        self::assertSame(get_string('closedplural', 'block_coursecardsuems'), $data->sections[2]['title']);
        self::assertFalse($data->sections[2]['isopen']);
        self::assertSame('list', $data->sections[2]['layout']);
        self::assertSame('Closed', $data->sections[2]['courses'][0]['title']);
    }

    /**
     * Sem data section is exported only when there are courses without schedule dates.
     */
    public function test_exports_nodate_section_when_needed(): void {
        global $PAGE;

        $courses = [
            ['title' => 'No date', 'status' => course_status_resolver::NODATE],
        ];

        $data = (new summary($courses, '2026/1'))->export_for_template($PAGE->get_renderer('core'));

        self::assertCount(4, $data->sections);
        self::assertSame(get_string('nodateplural', 'block_coursecardsuems'), $data->sections[3]['title']);
        self::assertFalse($data->sections[3]['isopen']);
        self::assertSame('list', $data->sections[3]['layout']);
        self::assertSame('No date', $data->sections[3]['courses'][0]['title']);
    }

    /**
     * Empty sections are still exported so the UI structure stays stable.
     */
    public function test_exports_empty_sections(): void {
        global $PAGE;

        $data = (new summary([], '2026/1'))->export_for_template($PAGE->get_renderer('core'));

        self::assertTrue($data->hassections);
        self::assertCount(3, $data->sections);
        self::assertFalse($data->sections[0]['hascourses']);
        self::assertSame(0, $data->sections[0]['count']);
    }
}
