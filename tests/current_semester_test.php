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
     * First semester covers January through June.
     *
     * @dataProvider first_semester_months_provider
     * @param int $month Month number.
     */
    public function test_first_semester_covers_january_through_june(int $month): void {
        self::assertSame('2026/1', current_semester::from_year_month(2026, $month));
    }

    /**
     * Second semester covers July through December.
     *
     * @dataProvider second_semester_months_provider
     * @param int $month Month number.
     */
    public function test_second_semester_covers_july_through_december(int $month): void {
        self::assertSame('2026/2', current_semester::from_year_month(2026, $month));
    }

    /**
     * The block title label uses the domain format YYYY/S.
     */
    public function test_current_semester_label_uses_domain_format(): void {
        $timestamp = gmmktime(12, 0, 0, 5, 9, 2026);

        self::assertSame('2026/1', current_semester::from_timestamp($timestamp));
    }

    /**
     * Data provider for first semester months.
     *
     * @return array
     */
    public static function first_semester_months_provider(): array {
        return [[1], [2], [3], [4], [5], [6]];
    }

    /**
     * Data provider for second semester months.
     *
     * @return array
     */
    public static function second_semester_months_provider(): array {
        return [[7], [8], [9], [10], [11], [12]];
    }
}
