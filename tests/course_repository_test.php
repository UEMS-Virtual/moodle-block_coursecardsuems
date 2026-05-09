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
 * Tests for the course repository.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_repository_test extends \advanced_testcase {

    /**
     * The repository returns only courses enrolled for the current user.
     */
    public function test_get_enrolled_courses_for_current_user_returns_only_current_user_courses(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $student = $generator->create_user();
        $otherstudent = $generator->create_user();
        $studentcourse = $generator->create_course(['fullname' => 'Student course']);
        $othercourse = $generator->create_course(['fullname' => 'Other course']);
        $notenrolledcourse = $generator->create_course(['fullname' => 'Not enrolled course']);

        $generator->enrol_user($student->id, $studentcourse->id, 'student');
        $generator->enrol_user($otherstudent->id, $othercourse->id, 'student');

        $this->setUser($student);

        $courses = (new course_repository())->get_enrolled_courses_for_current_user();
        $courseids = array_map(static function($course): int {
            return (int) $course->id;
        }, $courses);

        self::assertContains((int) $studentcourse->id, $courseids);
        self::assertNotContains((int) $othercourse->id, $courseids);
        self::assertNotContains((int) $notenrolledcourse->id, $courseids);
    }

    /**
     * Guests do not receive enrolled courses from the repository.
     */
    public function test_get_enrolled_courses_for_current_user_returns_empty_for_guest(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');

        $this->setGuestUser();

        self::assertSame([], (new course_repository())->get_enrolled_courses_for_current_user());
    }
}
