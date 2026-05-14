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
 * Tests for user perspective resolution.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class user_perspective_resolver_test extends \advanced_testcase {

    /**
     * Student perspective is detected through the student-facing capability.
     */
    public function test_detects_student_perspective_from_capability(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $studentcourse = $generator->create_course([
            'fullname' => 'Student course',
            'customfield_ead_inicio' => make_timestamp(2026, 3, 1),
            'customfield_ead_final' => make_timestamp(2026, 4, 1),
        ]);
        $othercourse = $generator->create_course(['fullname' => 'Other course']);
        $generator->enrol_user($user->id, $studentcourse->id, 'student');

        $perspectives = (new user_perspective_resolver())->resolve([$studentcourse, $othercourse], $user->id, false);

        self::assertSame([user_perspective_resolver::STUDENT], $this->keys($perspectives));
        self::assertSame([(int) $studentcourse->id], $perspectives[0]['courseids']);
        self::assertTrue($perspectives[0]['isdefault']);
    }

    /**
     * Tutor perspective groups presencial tutors and pedagogical mediators.
     */
    public function test_detects_tutor_perspective_from_tutor_roles(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $tutorcourse = $generator->create_course(['fullname' => 'Tutor course']);
        $mediatorcourse = $generator->create_course(['fullname' => 'Mediator course']);
        $tutorroleid = create_role('Tutor presencial', 'mod_tutor', 'Tutor presencial');
        $mediatorroleid = create_role('Mediador pedagógico', 'mod_medpdg', 'Mediador pedagógico');
        role_assign($tutorroleid, $user->id, context_course::instance($tutorcourse->id)->id);
        role_assign($mediatorroleid, $user->id, context_course::instance($mediatorcourse->id)->id);

        $perspectives = (new user_perspective_resolver())->resolve([$tutorcourse, $mediatorcourse], $user->id, false);

        self::assertSame([user_perspective_resolver::TUTOR], $this->keys($perspectives));
        self::assertSame([(int) $tutorcourse->id, (int) $mediatorcourse->id], $perspectives[0]['courseids']);
        self::assertSame(2, $perspectives[0]['count']);
    }

    /**
     * Teacher perspective uses standard and UEMS professor roles.
     */
    public function test_detects_teacher_perspective_from_teacher_roles(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $editingteachercourse = $generator->create_course(['fullname' => 'Editing teacher course']);
        $modprofcourse = $generator->create_course(['fullname' => 'Professor course']);
        $generator->enrol_user($user->id, $editingteachercourse->id, 'editingteacher');
        $modprofroleid = create_role('Professor mediador', 'mod_prof', 'Professor mediador');
        role_assign($modprofroleid, $user->id, context_course::instance($modprofcourse->id)->id);

        $perspectives = (new user_perspective_resolver())->resolve([$editingteachercourse, $modprofcourse], $user->id, false);

        self::assertSame([user_perspective_resolver::TEACHER], $this->keys($perspectives));
        self::assertSame([(int) $editingteachercourse->id, (int) $modprofcourse->id], $perspectives[0]['courseids']);
    }

    /**
     * Default perspective for non-admin users is the one with most courses, with teacher/tutor/student tie-break.
     */
    public function test_default_perspective_uses_count_and_tiebreak_for_non_admin(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $studentcourse = $generator->create_course([
            'fullname' => 'Student course',
            'customfield_ead_inicio' => make_timestamp(2026, 3, 1),
            'customfield_ead_final' => make_timestamp(2026, 4, 1),
        ]);
        $tutorcourse = $generator->create_course(['fullname' => 'Tutor course']);
        $teachercourse = $generator->create_course(['fullname' => 'Teacher course']);
        $generator->enrol_user($user->id, $studentcourse->id, 'student');
        $tutorroleid = create_role('Tutor presencial', 'mod_tutor', 'Tutor presencial');
        role_assign($tutorroleid, $user->id, context_course::instance($tutorcourse->id)->id);
        $generator->enrol_user($user->id, $teachercourse->id, 'editingteacher');

        $perspectives = (new user_perspective_resolver())->resolve(
            [$studentcourse, $tutorcourse, $teachercourse],
            $user->id,
            false
        );

        self::assertSame(
            [user_perspective_resolver::STUDENT, user_perspective_resolver::TUTOR, user_perspective_resolver::TEACHER],
            $this->keys($perspectives)
        );
        self::assertSame(user_perspective_resolver::TEACHER, $this->default_key($perspectives));
    }

    /**
     * Site admins get the admin perspective as the default while keeping personal perspectives.
     */
    public function test_admin_perspective_is_default_for_site_admin(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $generator = self::getDataGenerator();
        $admin = get_admin();
        $studentcourse = $generator->create_course([
            'fullname' => 'Student course',
            'customfield_ead_inicio' => make_timestamp(2026, 3, 1),
            'customfield_ead_final' => make_timestamp(2026, 4, 1),
        ]);
        $admincourse = $generator->create_course(['fullname' => 'Admin course']);
        $generator->enrol_user($admin->id, $studentcourse->id, 'student');

        $perspectives = (new user_perspective_resolver())->resolve([$studentcourse, $admincourse], $admin->id, true);

        self::assertContains(user_perspective_resolver::STUDENT, $this->keys($perspectives));
        self::assertContains(user_perspective_resolver::ADMIN, $this->keys($perspectives));
        self::assertSame(user_perspective_resolver::ADMIN, $this->default_key($perspectives));
        self::assertSame([(int) $studentcourse->id, (int) $admincourse->id], $this->by_key($perspectives, user_perspective_resolver::ADMIN)['courseids']);
    }

    /**
     * Conflicting role assignments are intentionally visible in multiple perspectives.
     */
    public function test_conflicting_roles_are_not_deduplicated_between_perspectives(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course([
            'fullname' => 'Conflicting course',
            'customfield_ead_inicio' => make_timestamp(2026, 3, 1),
            'customfield_ead_final' => make_timestamp(2026, 4, 1),
        ]);
        $generator->enrol_user($user->id, $course->id, 'student');
        $tutorroleid = create_role('Tutor presencial', 'mod_tutor', 'Tutor presencial');
        role_assign($tutorroleid, $user->id, context_course::instance($course->id)->id);

        $perspectives = (new user_perspective_resolver())->resolve([$course], $user->id, false);

        self::assertSame([(int) $course->id], $this->by_key($perspectives, user_perspective_resolver::STUDENT)['courseids']);
        self::assertSame([(int) $course->id], $this->by_key($perspectives, user_perspective_resolver::TUTOR)['courseids']);
    }

    /**
     * Creates the custom fields used by the plugin.
     */
    private function create_period_fields(): void {
        $generator = self::getDataGenerator();
        $categoryid = $generator->create_custom_field_category(['name' => 'EaD'])->get('id');
        $generator->create_custom_field(['categoryid' => $categoryid, 'type' => 'date', 'shortname' => 'ead_inicio']);
        $generator->create_custom_field(['categoryid' => $categoryid, 'type' => 'date', 'shortname' => 'ead_final']);
    }

    /**
     * Returns perspective keys.
     *
     * @param array $perspectives Perspective view models.
     * @return array
     */
    private function keys(array $perspectives): array {
        return array_values(array_map(static function(array $perspective): string {
            return $perspective['key'];
        }, $perspectives));
    }

    /**
     * Returns the default perspective key.
     *
     * @param array $perspectives Perspective view models.
     * @return string
     */
    private function default_key(array $perspectives): string {
        foreach ($perspectives as $perspective) {
            if (!empty($perspective['isdefault'])) {
                return $perspective['key'];
            }
        }

        return '';
    }

    /**
     * Returns a perspective by key.
     *
     * @param array $perspectives Perspective view models.
     * @param string $key Perspective key.
     * @return array
     */
    private function by_key(array $perspectives, string $key): array {
        foreach ($perspectives as $perspective) {
            if ($perspective['key'] === $key) {
                return $perspective;
            }
        }

        return [];
    }
}
