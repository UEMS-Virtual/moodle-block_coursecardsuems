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
        self::assertSame(userdate($start, get_string('strftimedateshort')), $viewmodel['period']['startlabel']);
        self::assertSame(userdate($end, get_string('strftimedateshort')), $viewmodel['period']['endlabel']);
        self::assertSame('Maria Docente', $viewmodel['teachers'][0]['name']);
        self::assertSame('MD', $viewmodel['teachers'][0]['initials']);
        self::assertTrue($viewmodel['hasteachers']);
        self::assertSame('Maria Docente', $viewmodel['firstteachername']);
        self::assertSame('MD', $viewmodel['firstteacherinitials']);
        self::assertStringContainsString('/course/view.php', $viewmodel['url']);
        self::assertStringNotContainsString('<', $viewmodel['title']);
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
