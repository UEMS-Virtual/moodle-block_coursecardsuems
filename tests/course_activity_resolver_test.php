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

namespace block_coursecardsuems\local;

/**
 * Tests for detecting dated activities pending in a course.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_activity_resolver_test extends \advanced_testcase {

    /**
     * Future assign dates make the course have a pending activity.
     */
    public function test_detects_future_assign_activity(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $generator->create_module('assign', [
            'course' => $course->id,
            'name' => 'Exame final',
            'allowsubmissionsfromdate' => make_timestamp(2026, 6, 27, 9),
            'duedate' => make_timestamp(2026, 6, 27, 12),
            'cutoffdate' => make_timestamp(2026, 6, 27, 12),
        ]);

        self::assertTrue((new course_activity_resolver())->has_pending_activity(
            (int) $course->id,
            make_timestamp(2026, 6, 23)
        ));
    }

    /**
     * Past assign dates do not make the course have a pending activity.
     */
    public function test_ignores_past_assign_activity(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $generator->create_module('assign', [
            'course' => $course->id,
            'name' => 'Exame encerrado',
            'allowsubmissionsfromdate' => make_timestamp(2026, 6, 20, 9),
            'duedate' => make_timestamp(2026, 6, 20, 12),
            'cutoffdate' => make_timestamp(2026, 6, 20, 12),
        ]);

        self::assertFalse((new course_activity_resolver())->has_pending_activity(
            (int) $course->id,
            make_timestamp(2026, 6, 23)
        ));
    }
}
