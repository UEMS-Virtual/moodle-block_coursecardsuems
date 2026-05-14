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
 * Tests for the current semester service.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class current_semester_test extends \advanced_testcase {

    /**
     * First semester covers February through August in the default academic calendar.
     *
     * @dataProvider first_semester_months_provider
     * @param int $month Month number.
     */
    public function test_first_semester_covers_february_through_august(int $month): void {
        self::assertSame('2026/1', current_semester::from_year_month(2026, $month));
    }

    /**
     * Second semester covers September through December in the same year.
     *
     * @dataProvider second_semester_months_provider
     * @param int $month Month number.
     */
    public function test_second_semester_covers_september_through_december(int $month): void {
        self::assertSame('2026/2', current_semester::from_year_month(2026, $month));
    }

    /**
     * January still belongs to the previous academic year's second semester.
     */
    public function test_january_belongs_to_previous_second_semester(): void {
        self::assertSame('2025/2', current_semester::from_year_month(2026, 1));
    }

    /**
     * The block title label uses the domain format YYYY/S.
     */
    public function test_current_semester_label_uses_domain_format(): void {
        $timestamp = make_timestamp(2026, 5, 9, 12, 0, 0);

        self::assertSame('2026/1', current_semester::from_timestamp($timestamp));
    }

    /**
     * Default bounds follow the academic calendar window.
     */
    public function test_default_bounds_use_academic_window(): void {
        [$firststart, $firstend] = current_semester::bounds_from_label('2026/1');
        [$secondstart, $secondend] = current_semester::bounds_from_label('2026/2');

        self::assertSame(make_timestamp(2026, 2, 1, 0, 0, 0), $firststart);
        self::assertSame(make_timestamp(2026, 9, 1, 0, 0, 0) - 1, $firstend);
        self::assertSame(make_timestamp(2026, 9, 1, 0, 0, 0), $secondstart);
        self::assertSame(make_timestamp(2027, 2, 1, 0, 0, 0) - 1, $secondend);
    }

    /**
     * A valid custom semester window overrides the default label and bounds.
     */
    public function test_valid_custom_window_overrides_default_calendar(): void {
        $this->resetAfterTest(true);
        set_config('customsemesterenabled', '1', 'block_coursecardsuems');
        set_config('customsemesterlabel', '2026/1', 'block_coursecardsuems');
        set_config('customsemesterstart', '2026-02-10', 'block_coursecardsuems');
        set_config('customsemesterend', '2026-08-05', 'block_coursecardsuems');

        self::assertSame('2026/1', current_semester::from_timestamp(make_timestamp(2026, 1, 15)));
        self::assertFalse(current_semester::has_invalid_custom_window());

        [$start, $end] = current_semester::bounds_from_label('2026/1');
        self::assertSame(make_timestamp(2026, 2, 10, 0, 0, 0), $start);
        self::assertSame(make_timestamp(2026, 8, 5, 23, 59, 59), $end);
    }

    /**
     * Invalid custom settings are flagged and the default calendar is used.
     */
    public function test_invalid_custom_window_falls_back_to_default_calendar(): void {
        $this->resetAfterTest(true);
        set_config('customsemesterenabled', '1', 'block_coursecardsuems');
        set_config('customsemesterlabel', '2026/1', 'block_coursecardsuems');
        set_config('customsemesterstart', '', 'block_coursecardsuems');
        set_config('customsemesterend', '2026-08-05', 'block_coursecardsuems');

        self::assertTrue(current_semester::has_invalid_custom_window());
        self::assertSame('2025/2', current_semester::from_timestamp(make_timestamp(2026, 1, 15)));

        [$start, $end] = current_semester::bounds_from_label('2026/1');
        self::assertSame(make_timestamp(2026, 2, 1, 0, 0, 0), $start);
        self::assertSame(make_timestamp(2026, 9, 1, 0, 0, 0) - 1, $end);
    }

    /**
     * Data provider for first semester months.
     *
     * @return array
     */
    public static function first_semester_months_provider(): array {
        return [[2], [3], [4], [5], [6], [7], [8]];
    }

    /**
     * Data provider for second semester months.
     *
     * @return array
     */
    public static function second_semester_months_provider(): array {
        return [[9], [10], [11], [12]];
    }
}
