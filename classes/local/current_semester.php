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

/**
 * Current semester service.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Calculates the Semestre vigente label used by the block.
 */
class current_semester {

    /**
     * Returns the current semester label in YYYY/S format.
     *
     * @param int|null $timestamp Unix timestamp. Uses the current time when null.
     * @return string Semester label.
     */
    public static function from_timestamp($timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }

        $year = (int) userdate($timestamp, '%Y');
        $month = (int) userdate($timestamp, '%m');

        return self::from_year_month($year, $month);
    }

    /**
     * Returns a semester label for a calendar year and month.
     *
     * @param int $year Calendar year.
     * @param int $month Month number, from 1 to 12.
     * @return string Semester label.
     */
    public static function from_year_month($year, $month) {
        $semester = $month <= 6 ? 1 : 2;

        return $year . '/' . $semester;
    }

    /**
     * Returns Unix timestamp bounds for a semester label.
     *
     * @param string $semesterlabel Semester label in YYYY/S format.
     * @return array{0:int,1:int} Start and end timestamps.
     */
    public static function bounds_from_label(string $semesterlabel): array {
        if (!preg_match('/^(\\d{4})\\/([12])$/', $semesterlabel, $matches)) {
            throw new \invalid_argument_exception('Invalid semester label: ' . $semesterlabel);
        }

        $year = (int) $matches[1];
        $semester = (int) $matches[2];
        $startmonth = $semester === 1 ? 1 : 7;
        $endmonth = $semester === 1 ? 7 : 1;
        $endyear = $semester === 1 ? $year : $year + 1;

        $start = make_timestamp($year, $startmonth, 1, 0, 0, 0);
        $end = make_timestamp($endyear, $endmonth, 1, 0, 0, 0) - 1;

        return [$start, $end];
    }
}
