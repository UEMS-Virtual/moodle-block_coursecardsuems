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
 * Course status resolver.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Resolves Status da disciplina from the Período informativo da disciplina.
 */
class course_status_resolver {

    /** Status: Em breve. */
    public const COMINGSOON = 'comingsoon';

    /** Status: Aberta. */
    public const OPEN = 'open';

    /** Status: Encerrada. */
    public const CLOSED = 'closed';

    /**
     * Resolves the current status.
     *
     * The Período informativo da disciplina is authoritative. Moodle native dates are used only
     * as a status fallback when ead_inicio/ead_final are absent.
     *
     * @param informative_period $period Período informativo da disciplina.
     * @param object|null $course Moodle course record for fallback dates.
     * @param int|null $now Current timestamp. Uses current time when null.
     * @return string One of the status constants.
     */
    public function resolve(informative_period $period, ?object $course = null, ?int $now = null): string {
        if ($now === null) {
            $now = time();
        }

        [$startdate, $enddate] = $this->get_effective_range($period, $course);

        if (!empty($startdate) && $startdate > $now) {
            return self::COMINGSOON;
        }

        if (!empty($enddate) && $enddate < $now) {
            return self::CLOSED;
        }

        return self::OPEN;
    }

    /**
     * Returns the ordering key for a course inside its status group.
     *
     * Product rules:
     * - Abertas: opened most recently first.
     * - Em breve: opening soonest first.
     * - Encerradas: closed most recently first.
     *
     * @param string $status Status constant.
     * @param informative_period $period Período informativo da disciplina.
     * @param object|null $course Moodle course record for fallback dates.
     * @return int Sort key.
     */
    public function get_sort_key(string $status, informative_period $period, ?object $course = null): int {
        [$startdate, $enddate] = $this->get_effective_range($period, $course);

        if ($status === self::OPEN) {
            return -($startdate ?: 0);
        }

        if ($status === self::COMINGSOON) {
            return $startdate ?: PHP_INT_MAX;
        }

        return -($enddate ?: 0);
    }

    /**
     * Returns the display label for a status.
     *
     * @param string $status Status constant.
     * @return string Human-readable label.
     */
    public function get_label(string $status): string {
        if ($status === self::COMINGSOON) {
            return get_string('comingsoon', 'block_coursecardsuems');
        }

        if ($status === self::CLOSED) {
            return get_string('closed', 'block_coursecardsuems');
        }

        return get_string('open', 'block_coursecardsuems');
    }

    /**
     * Returns the period dates, falling back to Moodle dates only when custom dates are absent.
     *
     * @param informative_period $period Período informativo da disciplina.
     * @param object|null $course Moodle course record for fallback dates.
     * @return array{0:int,1:int} Start and end timestamps.
     */
    private function get_effective_range(informative_period $period, ?object $course = null): array {
        if ($period->has_any_date()) {
            return [$period->startdate, $period->enddate];
        }

        if ($course !== null) {
            return [(int) ($course->startdate ?? 0), (int) ($course->enddate ?? 0)];
        }

        return [0, 0];
    }
}
