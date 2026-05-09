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
 * Tests for Status da disciplina resolution.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_status_resolver_test extends \advanced_testcase {

    /**
     * A discipline before ead_inicio is Em breve.
     */
    public function test_resolves_comingsoon_before_informative_period_start(): void {
        $period = new informative_period(make_timestamp(2026, 3, 1), make_timestamp(2026, 4, 1));
        $now = make_timestamp(2026, 2, 20);

        self::assertSame(course_status_resolver::COMINGSOON, (new course_status_resolver())->resolve($period, null, $now));
    }

    /**
     * A discipline during ead_inicio/ead_final is Aberta.
     */
    public function test_resolves_open_during_informative_period(): void {
        $period = new informative_period(make_timestamp(2026, 3, 1), make_timestamp(2026, 4, 1));
        $now = make_timestamp(2026, 3, 15);

        self::assertSame(course_status_resolver::OPEN, (new course_status_resolver())->resolve($period, null, $now));
    }

    /**
     * A discipline after ead_final is Encerrada.
     */
    public function test_resolves_closed_after_informative_period_end(): void {
        $period = new informative_period(make_timestamp(2026, 3, 1), make_timestamp(2026, 4, 1));
        $now = make_timestamp(2026, 4, 2);

        self::assertSame(course_status_resolver::CLOSED, (new course_status_resolver())->resolve($period, null, $now));
    }

    /**
     * Moodle dates are used only as status fallback when Período informativo is absent.
     */
    public function test_uses_moodle_dates_as_fallback_when_informative_period_is_absent(): void {
        $period = new informative_period();
        $course = (object) [
            'startdate' => make_timestamp(2026, 3, 1),
            'enddate' => make_timestamp(2026, 4, 1),
        ];
        $now = make_timestamp(2026, 3, 15);

        self::assertSame(course_status_resolver::OPEN, (new course_status_resolver())->resolve($period, $course, $now));
    }

    /**
     * Status groups are sorted according to product rules.
     */
    public function test_sort_key_orders_status_groups_by_product_rules(): void {
        $resolver = new course_status_resolver();
        $period = new informative_period(make_timestamp(2026, 3, 1), make_timestamp(2026, 4, 1));
        $course = (object) ['startdate' => make_timestamp(2026, 1, 1), 'enddate' => make_timestamp(2026, 6, 1)];

        self::assertSame(-make_timestamp(2026, 3, 1), $resolver->get_sort_key(course_status_resolver::OPEN, $period, $course));
        self::assertSame(make_timestamp(2026, 3, 1), $resolver->get_sort_key(course_status_resolver::COMINGSOON, $period, $course));
        self::assertSame(-make_timestamp(2026, 4, 1), $resolver->get_sort_key(course_status_resolver::CLOSED, $period, $course));
    }
}
