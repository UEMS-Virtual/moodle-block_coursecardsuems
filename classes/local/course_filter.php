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
 * Course filter.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Filters Moodle course records according to the current product scope.
 */
class course_filter {

    /** @var category_parser Category parser. */
    private $categoryparser;

    /** @var course_shortname_parser Course shortname parser. */
    private $shortnameparser;

    /**
     * Constructor.
     *
     * @param category_parser|null $categoryparser Category parser.
     * @param course_shortname_parser|null $shortnameparser Course shortname parser.
     */
    public function __construct(?category_parser $categoryparser = null, ?course_shortname_parser $shortnameparser = null) {
        $this->categoryparser = $categoryparser ?? new category_parser();
        $this->shortnameparser = $shortnameparser ?? new course_shortname_parser();
    }

    /**
     * Keeps only Disciplina EaD records overlapping the requested Semestre vigente.
     *
     * This slice uses Moodle's native start/end dates to determine the semester window.
     * The next Período informativo slice will make custom fields ead_inicio/ead_final
     * authoritative for discipline dates.
     *
     * @param array $courses Course records.
     * @param string $semesterlabel Semester label in YYYY/S format.
     * @return array Filtered course records, preserving original order.
     */
    public function filter_current_semester_distance_courses(array $courses, string $semesterlabel): array {
        [$semesterstart, $semesterend] = current_semester::bounds_from_label($semesterlabel);

        return array_values(array_filter($courses, function($course) use ($semesterstart, $semesterend): bool {
            if (empty($course->category) || !$this->categoryparser->is_distance_category((int) $course->category)) {
                return false;
            }

            if (empty($course->shortname) || !$this->shortnameparser->is_discipline_shortname($course->shortname)) {
                return false;
            }

            $coursestart = $course->startdate ?? 0;
            $courseend = $course->enddate ?? 0;
            if (empty($coursestart) && empty($courseend)) {
                return false;
            }

            $rangestart = $coursestart ?: $courseend;
            $rangeend = $courseend ?: $coursestart;

            return $rangestart <= $semesterend && $rangeend >= $semesterstart;
        }));
    }
}
