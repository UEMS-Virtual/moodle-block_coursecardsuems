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
 * Tests for filtering Disciplina EaD by Semestre vigente.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_filter_test extends \advanced_testcase {

    /**
     * Only Disciplina EaD from the requested semester remains in the list.
     */
    public function test_filter_keeps_only_distance_courses_from_current_semester(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $distancecategory = $generator->create_category(['name' => 'Distância']);
        $distanceseries = $generator->create_category(['name' => '1ª Série', 'parent' => $distancecategory->id]);
        $onsitecategory = $generator->create_category(['name' => 'Presencial']);
        $onsiteseries = $generator->create_category(['name' => '1ª Série', 'parent' => $onsitecategory->id]);

        $distancecurrent = $generator->create_course([
            'category' => $distanceseries->id,
            'fullname' => 'EaD atual',
            'shortname' => 'EAD_26_1S_CURRENT_abc12',
            'startdate' => make_timestamp(2026, 3, 1),
            'enddate' => make_timestamp(2026, 4, 1),
        ]);
        $onsitecurrent = $generator->create_course([
            'category' => $onsiteseries->id,
            'fullname' => 'Presencial atual',
            'shortname' => 'ONSITE_26_1S_CURRENT_abc12',
            'startdate' => make_timestamp(2026, 3, 1),
            'enddate' => make_timestamp(2026, 4, 1),
        ]);
        $distancenextsemester = $generator->create_course([
            'category' => $distanceseries->id,
            'fullname' => 'EaD próximo semestre',
            'shortname' => 'EAD_26_2S_NEXT_abc12',
            'startdate' => make_timestamp(2026, 8, 1),
            'enddate' => make_timestamp(2026, 9, 1),
        ]);

        $filtered = (new course_filter())->filter_current_semester_distance_courses(
            [$distancecurrent, $onsitecurrent, $distancenextsemester],
            '2026/1'
        );

        self::assertSame([(int) $distancecurrent->id], $this->course_ids($filtered));
    }

    /**
     * A course overlapping the semester window is part of that semester.
     */
    public function test_filter_keeps_distance_course_that_overlaps_semester_window(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $distancecategory = $generator->create_category(['name' => 'Distância']);
        $distanceseries = $generator->create_category(['name' => '2ª Série', 'parent' => $distancecategory->id]);
        $course = $generator->create_course([
            'category' => $distanceseries->id,
            'fullname' => 'EaD sobreposta',
            'shortname' => 'EAD_26_1S_OVERLAP_abc12',
            'startdate' => make_timestamp(2025, 12, 1),
            'enddate' => make_timestamp(2026, 1, 15),
        ]);

        $filtered = (new course_filter())->filter_current_semester_distance_courses([$course], '2026/1');

        self::assertSame([(int) $course->id], $this->course_ids($filtered));
    }

    /**
     * Distance courses with unknown shortname shape are excluded as incomplete discipline data.
     */
    public function test_filter_excludes_distance_course_with_unknown_shortname_shape(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $distancecategory = $generator->create_category(['name' => 'Distância']);
        $distanceseries = $generator->create_category(['name' => '3ª Série', 'parent' => $distancecategory->id]);
        $course = $generator->create_course([
            'category' => $distanceseries->id,
            'fullname' => 'EaD shortname desconhecido',
            'shortname' => 'teste',
            'startdate' => make_timestamp(2026, 3, 1),
            'enddate' => make_timestamp(2026, 4, 1),
        ]);

        $filtered = (new course_filter())->filter_current_semester_distance_courses([$course], '2026/1');

        self::assertSame([], $filtered);
    }

    /**
     * Courses without a date range are excluded until the informative period slice exists.
     */
    public function test_filter_excludes_course_without_dates(): void {
        $this->resetAfterTest(true);

        $generator = self::getDataGenerator();
        $distancecategory = $generator->create_category(['name' => 'Distância']);
        $distanceseries = $generator->create_category(['name' => '3ª Série', 'parent' => $distancecategory->id]);
        $course = $generator->create_course([
            'category' => $distanceseries->id,
            'fullname' => 'EaD sem datas',
            'shortname' => 'EAD_26_1S_NODATES_abc12',
            'startdate' => 0,
            'enddate' => 0,
        ]);

        $filtered = (new course_filter())->filter_current_semester_distance_courses([$course], '2026/1');

        self::assertSame([], $filtered);
    }

    /**
     * Returns course ids with stable integer comparison.
     *
     * @param array $courses Course records.
     * @return array
     */
    private function course_ids(array $courses): array {
        return array_values(array_map(static function($course): int {
            return (int) $course->id;
        }, $courses));
    }
}
