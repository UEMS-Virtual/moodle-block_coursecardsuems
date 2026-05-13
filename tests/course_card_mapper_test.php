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
 * Tests for mapping Moodle courses to discipline card view models.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_card_mapper_test extends \advanced_testcase {

    /**
     * A Moodle course is mapped to the domain view model used by templates.
     */
    public function test_maps_course_to_discipline_view_model(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $generator = self::getDataGenerator();
        $distancecategory = $generator->create_category(['name' => 'Distância']);
        $series = $generator->create_category(['name' => '2ª Série', 'parent' => $distancecategory->id]);
        $start = make_timestamp(2026, 3, 1);
        $end = make_timestamp(2026, 4, 1);
        $course = $generator->create_course([
            'category' => $series->id,
            'fullname' => '[CISOL-23-2S-EP-(REO)] Economia Política',
            'shortname' => 'CISOL_23_2S_EP_(REO)_df970',
            'customfield_ead_inicio' => $start,
            'customfield_ead_final' => $end,
        ]);
        $teacher = $generator->create_user(['firstname' => 'Maria', 'lastname' => 'Docente']);
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');

        $viewmodel = (new course_card_mapper(null, null, null, make_timestamp(2026, 3, 15)))->map($course);

        self::assertSame((int) $course->id, $viewmodel['id']);
        self::assertSame('CISOL-23-2S-EP-(REO)', $viewmodel['code']);
        self::assertSame('Economia Política', $viewmodel['title']);
        self::assertSame('CISOL-23', $viewmodel['group']);
        self::assertSame('2ª Série', $viewmodel['series']);
        self::assertSame(course_status_resolver::OPEN, $viewmodel['status']);
        self::assertSame(get_string('open', 'block_coursecardsuems'), $viewmodel['statuslabel']);
        self::assertSame('coursecardsuems-status-open', $viewmodel['statusclass']);
        self::assertFalse($viewmodel['isclosed']);
        self::assertSame($start, $viewmodel['period']['start']);
        self::assertSame($end, $viewmodel['period']['end']);
        self::assertTrue($viewmodel['period']['showdates']);
        self::assertSame(userdate($start, get_string('strftimedateshort')), $viewmodel['period']['startlabel']);
        self::assertSame(userdate($end, get_string('strftimedateshort')), $viewmodel['period']['endlabel']);
        self::assertSame('Maria Docente', $viewmodel['teachers'][0]['name']);
        self::assertSame('Maria D.', $viewmodel['teachers'][0]['shortname']);
        self::assertSame('MD', $viewmodel['teachers'][0]['initials']);
        self::assertFalse($viewmodel['teachers'][0]['hasavatarurl']);
        self::assertSame('coursecardsuems-avatar-color-' . ($teacher->id % 10), $viewmodel['teachers'][0]['avatarcolorclass']);
        self::assertTrue($viewmodel['hasteachers']);
        self::assertTrue($viewmodel['hasdisplayteachers']);
        self::assertCount(1, $viewmodel['displayteachers']);
        self::assertSame('Maria Docente', $viewmodel['teacherdisplayname']);
        self::assertStringContainsString('/course/view.php', $viewmodel['url']);
        self::assertTrue($viewmodel['hasurl']);
        self::assertTrue($viewmodel['isavailable']);
        self::assertSame(course_status_resolver::OPEN, $viewmodel['temporalstatus']);
        self::assertStringNotContainsString('<', $viewmodel['title']);
    }

    /**
     * A hidden discipline that is open by dates is presented as Em breve without link.
     */
    public function test_hidden_open_course_is_presented_as_comingsoon_without_link(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $course = self::getDataGenerator()->create_course([
            'fullname' => '[CISOL-23-2S-EP] Economia Política',
            'shortname' => 'CISOL_23_2S_EP_df970',
            'visible' => 0,
            'customfield_ead_inicio' => make_timestamp(2026, 3, 1),
            'customfield_ead_final' => make_timestamp(2026, 4, 1),
        ]);

        $viewmodel = (new course_card_mapper(null, null, null, make_timestamp(2026, 3, 15)))->map($course);

        self::assertSame(course_status_resolver::OPEN, $viewmodel['temporalstatus']);
        self::assertSame(course_status_resolver::COMINGSOON, $viewmodel['status']);
        self::assertSame(get_string('comingsoon', 'block_coursecardsuems'), $viewmodel['statuslabel']);
        self::assertFalse($viewmodel['hasurl']);
        self::assertFalse($viewmodel['isavailable']);
        self::assertTrue($viewmodel['ispreparing']);
        self::assertSame(get_string('availablecomingsoon', 'block_coursecardsuems'), $viewmodel['period']['label']);
        self::assertFalse($viewmodel['period']['showdates']);
    }

    /**
     * Admin inspection keeps the student-facing status but allows hidden courses to be linked.
     */
    public function test_hidden_open_course_can_be_linked_for_admin_inspection(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $course = self::getDataGenerator()->create_course([
            'fullname' => '[CISOL-23-2S-EP] Economia Política',
            'shortname' => 'CISOL_23_2S_EP_df970',
            'visible' => 0,
            'customfield_ead_inicio' => make_timestamp(2026, 3, 1),
            'customfield_ead_final' => make_timestamp(2026, 4, 1),
        ]);

        $viewmodel = (new course_card_mapper(null, null, null, make_timestamp(2026, 3, 15)))->map($course, true);

        self::assertSame(course_status_resolver::COMINGSOON, $viewmodel['status']);
        self::assertTrue($viewmodel['hasurl']);
        self::assertTrue($viewmodel['ispreparing']);
    }

    /**
     * A hidden closed discipline remains Encerrada but has no link.
     */
    public function test_hidden_closed_course_remains_closed_without_link(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $course = self::getDataGenerator()->create_course([
            'fullname' => '[CISOL-23-2S-EP] Economia Política',
            'shortname' => 'CISOL_23_2S_EP_df970',
            'visible' => 0,
            'customfield_ead_inicio' => make_timestamp(2026, 3, 1),
            'customfield_ead_final' => make_timestamp(2026, 4, 1),
        ]);

        $viewmodel = (new course_card_mapper(null, null, null, make_timestamp(2026, 4, 2)))->map($course);

        self::assertSame(course_status_resolver::CLOSED, $viewmodel['temporalstatus']);
        self::assertSame(course_status_resolver::CLOSED, $viewmodel['status']);
        self::assertFalse($viewmodel['hasurl']);
        self::assertFalse($viewmodel['isavailable']);
        self::assertFalse($viewmodel['ispreparing']);
        self::assertTrue($viewmodel['period']['showdates']);
    }

    /**
     * Custom UEMS professor roles are accepted as card teachers.
     */
    public function test_maps_mod_prof_role_as_teacher(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_user(['firstname' => 'Mediador', 'lastname' => 'Professor']);
        $roleid = create_role('Professor mediador', 'mod_prof', 'Professor mediador');
        role_assign($roleid, $teacher->id, \context_course::instance($course->id)->id);

        $viewmodel = (new course_card_mapper())->map($course);

        self::assertSame('Mediador Professor', $viewmodel['teacherdisplayname']);
        self::assertSame('MP', $viewmodel['displayteachers'][0]['initials']);
    }

    /**
     * Custom UEMS professor roles assigned in parent contexts are accepted as card teachers.
     */
    public function test_maps_mod_prof_role_from_parent_context_as_teacher(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $category = $generator->create_category();
        $course = $generator->create_course(['category' => $category->id]);
        $teacher = $generator->create_user(['firstname' => 'Categoria', 'lastname' => 'Professor']);
        $roleid = create_role('Professor mediador', 'mod_prof', 'Professor mediador');
        role_assign($roleid, $teacher->id, \context_coursecat::instance($category->id)->id);

        $viewmodel = (new course_card_mapper())->map($course);

        self::assertSame('Categoria Professor', $viewmodel['teacherdisplayname']);
        self::assertSame('CP', $viewmodel['displayteachers'][0]['initials']);
    }

    /**
     * Two teachers are shown with two display avatars and abbreviated names.
     */
    public function test_maps_two_teachers_to_avatar_stack_and_abbreviated_names(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $firstteacher = $generator->create_user(['firstname' => 'Ana', 'lastname' => 'Lima']);
        $secondteacher = $generator->create_user(['firstname' => 'Maria', 'lastname' => 'Santos']);
        $generator->enrol_user($firstteacher->id, $course->id, 'editingteacher');
        $generator->enrol_user($secondteacher->id, $course->id, 'editingteacher');

        $viewmodel = (new course_card_mapper())->map($course);

        self::assertCount(2, $viewmodel['displayteachers']);
        self::assertSame('Ana L.', $viewmodel['displayteachers'][0]['shortname']);
        self::assertSame('Maria S.', $viewmodel['displayteachers'][1]['shortname']);
        self::assertSame('Ana L., Maria S.', $viewmodel['teacherdisplayname']);
    }

    /**
     * Three or more teachers show the first two and keep the existing others suffix.
     */
    public function test_maps_three_teachers_to_first_two_plus_others(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $firstteacher = $generator->create_user(['firstname' => 'Ana', 'lastname' => 'Lima']);
        $secondteacher = $generator->create_user(['firstname' => 'João', 'lastname' => 'Rocha']);
        $thirdteacher = $generator->create_user(['firstname' => 'Maria', 'lastname' => 'Santos']);
        $generator->enrol_user($firstteacher->id, $course->id, 'editingteacher');
        $generator->enrol_user($secondteacher->id, $course->id, 'editingteacher');
        $generator->enrol_user($thirdteacher->id, $course->id, 'editingteacher');

        $viewmodel = (new course_card_mapper())->map($course);

        self::assertCount(2, $viewmodel['displayteachers']);
        self::assertSame(
            'Ana L., João R. ' . get_string('others', 'block_coursecardsuems', 1),
            $viewmodel['teacherdisplayname']
        );
    }

    /**
     * Moodle profile pictures are used only when the user has a custom profile picture.
     */
    public function test_uses_profile_picture_only_when_user_has_custom_picture(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_user(['firstname' => 'Foto', 'lastname' => 'Perfil', 'picture' => 1]);
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');

        $viewmodel = (new course_card_mapper())->map($course);

        self::assertTrue($viewmodel['displayteachers'][0]['hasavatarurl']);
        self::assertNotSame('', $viewmodel['displayteachers'][0]['avatarurl']);
    }

    /**
     * Courses without a bracketed code keep the full name as title.
     */
    public function test_maps_title_without_bracketed_code(): void {
        $this->resetAfterTest(true);
        $course = self::getDataGenerator()->create_course([
            'fullname' => 'Nome simples',
            'shortname' => 'CISOL_23_2S_EP_df970',
        ]);

        $viewmodel = (new course_card_mapper())->map($course);

        self::assertSame('', $viewmodel['code']);
        self::assertSame('Nome simples', $viewmodel['title']);
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
}
