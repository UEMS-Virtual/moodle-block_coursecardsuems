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
 * Tests for reading the Período informativo da disciplina.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class informative_period_reader_test extends \advanced_testcase {

    /**
     * The reader returns ead_inicio and ead_final from Moodle course custom fields.
     */
    public function test_reads_ead_inicio_and_ead_final_custom_fields(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $start = make_timestamp(2026, 2, 10);
        $end = make_timestamp(2026, 5, 20);
        $course = self::getDataGenerator()->create_course([
            'customfield_ead_inicio' => $start,
            'customfield_ead_final' => $end,
        ]);

        $period = (new informative_period_reader())->get_period($course->id);

        self::assertSame($start, $period->startdate);
        self::assertSame($end, $period->enddate);
        self::assertTrue($period->has_any_date());
        self::assertTrue($period->has_complete_range());
    }

    /**
     * Missing custom fields are represented as an empty period, not an exception.
     */
    public function test_returns_empty_period_when_custom_fields_are_missing(): void {
        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course([
            'startdate' => make_timestamp(2026, 1, 1),
            'enddate' => make_timestamp(2026, 6, 1),
        ]);

        $period = (new informative_period_reader())->get_period($course->id);

        self::assertSame(0, $period->startdate);
        self::assertSame(0, $period->enddate);
        self::assertFalse($period->has_any_date());
    }

    /**
     * Partial custom field data is kept so callers can decide how to handle it.
     */
    public function test_returns_partial_period_without_breaking(): void {
        $this->resetAfterTest(true);
        $this->create_period_fields();

        $start = make_timestamp(2026, 2, 10);
        $course = self::getDataGenerator()->create_course([
            'customfield_ead_inicio' => $start,
        ]);

        $period = (new informative_period_reader())->get_period($course->id);

        self::assertSame($start, $period->startdate);
        self::assertSame(0, $period->enddate);
        self::assertTrue($period->has_any_date());
        self::assertFalse($period->has_complete_range());
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
