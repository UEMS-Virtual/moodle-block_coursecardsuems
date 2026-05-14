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
        $customwindow = self::get_custom_window();
        if ($customwindow !== null) {
            return $customwindow['label'];
        }

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
        $year = (int) $year;
        $month = (int) $month;

        if ($month === 1) {
            return ($year - 1) . '/2';
        }

        if ($month >= 2 && $month <= 8) {
            return $year . '/1';
        }

        return $year . '/2';
    }

    /**
     * Returns Unix timestamp bounds for a semester label.
     *
     * @param string $semesterlabel Semester label in YYYY/S format.
     * @return array{0:int,1:int} Start and end timestamps.
     */
    public static function bounds_from_label(string $semesterlabel): array {
        $customwindow = self::get_custom_window();
        if ($customwindow !== null && $customwindow['label'] === $semesterlabel) {
            return [$customwindow['start'], $customwindow['end']];
        }

        if (!preg_match('/^(\\d{4})\\/([12])$/', $semesterlabel, $matches)) {
            throw new \invalid_argument_exception('Invalid semester label: ' . $semesterlabel);
        }

        $year = (int) $matches[1];
        $semester = (int) $matches[2];
        $startmonth = $semester === 1 ? 2 : 9;
        $endmonth = $semester === 1 ? 9 : 2;
        $endyear = $semester === 1 ? $year : $year + 1;

        $start = make_timestamp($year, $startmonth, 1, 0, 0, 0);
        $end = make_timestamp($endyear, $endmonth, 1, 0, 0, 0) - 1;

        return [$start, $end];
    }

    /**
     * Returns whether the custom semester window setting is enabled but invalid.
     *
     * @return bool
     */
    public static function has_invalid_custom_window(): bool {
        $config = get_config('block_coursecardsuems');
        if (empty($config->customsemesterenabled)) {
            return false;
        }

        return self::get_custom_window() === null;
    }

    /**
     * Returns the configured custom semester window when enabled and valid.
     *
     * @return array{label:string,start:int,end:int}|null
     */
    private static function get_custom_window(): ?array {
        $config = get_config('block_coursecardsuems');
        if (empty($config->customsemesterenabled)) {
            return null;
        }

        $label = trim((string) ($config->customsemesterlabel ?? ''));
        $start = self::parse_config_date((string) ($config->customsemesterstart ?? ''));
        $end = self::parse_config_date((string) ($config->customsemesterend ?? ''), true);

        if (!preg_match('/^\d{4}\/[12]$/', $label) || $start === 0 || $end === 0 || $start > $end) {
            return null;
        }

        return [
            'label' => $label,
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Parses a YYYY-MM-DD config date into a timestamp.
     *
     * @param string $value Date value.
     * @param bool $endofday Whether to return the end of the day.
     * @return int Timestamp or zero when invalid.
     */
    private static function parse_config_date(string $value, bool $endofday = false): int {
        $value = trim($value);
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
            return 0;
        }

        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];
        if (!checkdate($month, $day, $year)) {
            return 0;
        }

        if ($endofday) {
            return make_timestamp($year, $month, $day, 23, 59, 59);
        }

        return make_timestamp($year, $month, $day, 0, 0, 0);
    }
}
