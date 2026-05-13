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

use context_course;

/**
 * Tests for the student-facing course access filter.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_access_filter_test extends \advanced_testcase {

    /**
     * Users only see courses where the student-facing capability is available without doanything.
     */
    public function test_filters_courses_by_student_facing_capability(): void {
        global $DB;

        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $student = $generator->create_user();
        $studentcourse = $generator->create_course(['fullname' => 'Student course']);
        $managercourse = $generator->create_course(['fullname' => 'Manager course']);

        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        role_assign($studentroleid, $student->id, context_course::instance($studentcourse->id)->id);

        $this->setUser($student);

        $courses = (new course_access_filter())->filter_courses_for_current_user([$studentcourse, $managercourse]);
        $courseids = array_map(static function($course): int {
            return (int) $course->id;
        }, $courses);

        self::assertContains((int) $studentcourse->id, $courseids);
        self::assertNotContains((int) $managercourse->id, $courseids);
    }

    /**
     * Site admins keep every candidate course because their routine is an inspection view.
     */
    public function test_site_admin_keeps_all_candidate_courses(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $firstcourse = $generator->create_course(['fullname' => 'First course']);
        $secondcourse = $generator->create_course(['fullname' => 'Second course']);

        $courses = (new course_access_filter())->filter_courses_for_current_user([$firstcourse, $secondcourse], true);

        self::assertCount(2, $courses);
        self::assertSame((int) $firstcourse->id, (int) $courses[0]->id);
        self::assertSame((int) $secondcourse->id, (int) $courses[1]->id);
    }
}
