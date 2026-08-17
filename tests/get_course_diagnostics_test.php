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

namespace block_coursecardsuems\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the course inclusion diagnostic external function.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_course_diagnostics_test extends \externallib_advanced_testcase {

    /**
     * Users without the diagnostic capability cannot inspect course eligibility.
     */
    public function test_rejects_user_without_diagnostic_capability(): void {
        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $this->setUser(self::getDataGenerator()->create_user());

        $this->expectException(\required_capability_exception::class);
        get_course_diagnostics::execute((int) $course->id);
    }

    /**
     * Site administrators can inspect a course included in their default admin perspective.
     */
    public function test_admin_can_diagnose_included_course(): void {
        $this->resetAfterTest(true);
        $course = $this->create_distance_course();
        $this->setAdminUser();

        $result = get_course_diagnostics::execute((int) $course->id);

        self::assertTrue($result['included']);
        self::assertNull($result['excludedat']);
        self::assertSame('admin', $result['selectedperspective']);
        self::assertSame([
            'repository' => true,
            'category' => true,
            'shortname' => true,
            'period' => true,
            'perspective' => true,
            'access' => true,
            'accessapplicable' => false,
        ], $result['gates']);
    }

    /**
     * A structurally valid student course reports the access capability gate separately.
     */
    public function test_reports_student_access_exclusion(): void {
        global $DB;

        $this->resetAfterTest(true);
        $course = $this->create_distance_course();
        $student = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        assign_capability(
            'block/coursecardsuems:viewcontent',
            CAP_PROHIBIT,
            $studentroleid,
            \context_course::instance($course->id)->id,
            true
        );
        $this->setAdminUser();

        $result = get_course_diagnostics::execute((int) $course->id, (int) $student->id, 'student');

        self::assertTrue($result['gates']['perspective']);
        self::assertFalse($result['gates']['access']);
        self::assertFalse($result['included']);
        self::assertSame('access', $result['excludedat']);
    }

    /**
     * A course outside an explicitly requested perspective reports the perspective gate.
     */
    public function test_reports_perspective_exclusion(): void {
        $this->resetAfterTest(true);
        $course = $this->create_distance_course();

        $result = $this->diagnose_for_enrolled_student($course, 'tutor');

        self::assertSame('tutor', $result['selectedperspective']);
        self::assertFalse($result['included']);
        self::assertSame('perspective', $result['excludedat']);
        self::assertFalse($result['gates']['perspective']);
    }

    /**
     * A course outside the current semester reports the period gate.
     */
    public function test_reports_period_exclusion(): void {
        $this->resetAfterTest(true);
        $course = $this->create_distance_course([
            'customfield_ead_inicio' => make_timestamp(2026, 9, 1),
            'customfield_ead_final' => make_timestamp(2026, 10, 1),
        ]);

        $result = $this->diagnose_for_enrolled_student($course);

        self::assertFalse($result['included']);
        self::assertSame('period', $result['excludedat']);
        self::assertFalse($result['gates']['period']);
    }

    /**
     * A Distance course with an unknown shortname reports the shortname gate.
     */
    public function test_reports_shortname_exclusion(): void {
        $this->resetAfterTest(true);
        $course = $this->create_distance_course(['shortname' => 'curso_manual']);

        $result = $this->diagnose_for_enrolled_student($course);

        self::assertFalse($result['included']);
        self::assertSame('shortname', $result['excludedat']);
        self::assertFalse($result['gates']['shortname']);
    }

    /**
     * A course outside the Distance branch reports the category gate.
     */
    public function test_reports_category_exclusion(): void {
        $this->resetAfterTest(true);
        $onsitecategory = self::getDataGenerator()->create_category(['name' => 'Presencial']);
        $course = $this->create_distance_course(['category' => $onsitecategory->id]);

        $result = $this->diagnose_for_enrolled_student($course);

        self::assertFalse($result['included']);
        self::assertSame('category', $result['excludedat']);
        self::assertFalse($result['gates']['category']);
    }

    /**
     * A course outside the target user's source reports the repository gate first.
     */
    public function test_reports_repository_exclusion(): void {
        $this->resetAfterTest(true);
        $course = $this->create_distance_course();
        $student = self::getDataGenerator()->create_user();
        $this->setAdminUser();

        $result = get_course_diagnostics::execute((int) $course->id, (int) $student->id, 'student');

        self::assertFalse($result['included']);
        self::assertSame('repository', $result['excludedat']);
        self::assertFalse($result['gates']['repository']);
    }

    /**
     * Administrators can diagnose another user without changing the active session.
     */
    public function test_diagnoses_target_student_without_changing_current_user(): void {
        global $USER;

        $this->resetAfterTest(true);
        $course = $this->create_distance_course();
        $student = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setAdminUser();
        $callerid = (int) $USER->id;

        $result = get_course_diagnostics::execute((int) $course->id, (int) $student->id, 'student');

        self::assertSame($callerid, (int) $USER->id);
        self::assertSame((int) $student->id, $result['target']['userid']);
        self::assertSame('student', $result['selectedperspective']);
        self::assertTrue($result['gates']['accessapplicable']);
        self::assertTrue($result['included']);
    }

    /**
     * Diagnoses a course for an enrolled student while calling as site administrator.
     *
     * @param object $course Course record.
     * @param string $perspective Requested perspective.
     * @return array Diagnostic result.
     */
    private function diagnose_for_enrolled_student(object $course, string $perspective = 'student'): array {
        $student = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setAdminUser();

        return get_course_diagnostics::execute((int) $course->id, (int) $student->id, $perspective);
    }

    /**
     * Creates a current-semester course below the Distance category branch.
     *
     * @param array $overrides Course field overrides.
     * @return object Course record.
     */
    private function create_distance_course(array $overrides = []): object {
        $generator = self::getDataGenerator();
        $fieldcategoryid = $generator->create_custom_field_category(['name' => 'EaD'])->get('id');
        $generator->create_custom_field(['categoryid' => $fieldcategoryid, 'type' => 'date', 'shortname' => 'ead_inicio']);
        $generator->create_custom_field(['categoryid' => $fieldcategoryid, 'type' => 'date', 'shortname' => 'ead_final']);

        $distancecategory = $generator->create_category(['name' => 'Distância']);
        $series = $generator->create_category(['name' => '3ª Série', 'parent' => $distancecategory->id]);
        set_config('customsemesterenabled', '1', 'block_coursecardsuems');
        set_config('customsemesterlabel', '2026/1', 'block_coursecardsuems');
        set_config('customsemesterstart', '2026-02-01', 'block_coursecardsuems');
        set_config('customsemesterend', '2026-08-31', 'block_coursecardsuems');

        return $generator->create_course($overrides + [
            'category' => $series->id,
            'fullname' => '[PEDG-24-3S-ESEI] Estágio Supervisionado na Educação Infantil',
            'shortname' => 'PEDG_24_3S_ESEI',
            'startdate' => make_timestamp(2026, 3, 1),
            'enddate' => make_timestamp(2026, 4, 30),
            'customfield_ead_inicio' => make_timestamp(2026, 3, 1),
            'customfield_ead_final' => make_timestamp(2026, 4, 30),
        ]);
    }
}
