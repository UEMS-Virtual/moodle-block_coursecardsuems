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

    /** @var informative_period_reader Informative period reader. */
    private $periodreader;

    /**
     * Constructor.
     *
     * @param category_parser|null $categoryparser Category parser.
     * @param course_shortname_parser|null $shortnameparser Course shortname parser.
     * @param informative_period_reader|null $periodreader Informative period reader.
     */
    public function __construct(
        ?category_parser $categoryparser = null,
        ?course_shortname_parser $shortnameparser = null,
        ?informative_period_reader $periodreader = null
    ) {
        $this->categoryparser = $categoryparser ?? new category_parser();
        $this->shortnameparser = $shortnameparser ?? new course_shortname_parser();
        $this->periodreader = $periodreader ?? new informative_period_reader();
    }

    /**
     * Keeps only Disciplina EaD records overlapping the requested Semestre vigente.
     *
     * The Período informativo da disciplina, read from ead_inicio/ead_final, is authoritative.
     * Moodle's native start/end dates are not used to decide whether the discipline belongs
     * to the semester.
     *
     * @param array $courses Course records.
     * @param string $semesterlabel Semester label in YYYY/S format.
     * @param bool $includeundated Whether to keep matching courses without informative period for admin audit.
     * @return array Filtered course records, preserving original order.
     */
    public function filter_current_semester_distance_courses(
        array $courses,
        string $semesterlabel,
        bool $includeundated = false
    ): array {
        return array_values(array_filter($courses, function($course) use ($semesterlabel, $includeundated): bool {
            if (!$this->is_distance_course($course) || !$this->has_discipline_shortname($course)) {
                return false;
            }

            return $this->evaluate_current_semester_distance_course($course, $semesterlabel, $includeundated)['period'];
        }));
    }

    /**
     * Evaluates each product-scope gate for one Moodle course.
     *
     * @param object $course Course record.
     * @param string $semesterlabel Semester label in YYYY/S format.
     * @param bool $includeundated Whether Moodle dates may place an incomplete schedule in the semester.
     * @return array Gate results and date values used by the filter.
     */
    public function evaluate_current_semester_distance_course(
        object $course,
        string $semesterlabel,
        bool $includeundated = false
    ): array {
        [$semesterstart, $semesterend] = current_semester::bounds_from_label($semesterlabel);
        $categoryaccepted = $this->is_distance_course($course);
        $shortnameaccepted = $this->has_discipline_shortname($course);
        $period = $this->periodreader->get_period((int) $course->id);
        $periodaccepted = $period->has_complete_range() ?
            $period->startdate <= $semesterend && $period->enddate >= $semesterstart :
            $includeundated && $this->course_dates_overlap_semester($course, $semesterstart, $semesterend);

        return [
            'category' => $categoryaccepted,
            'shortname' => $shortnameaccepted,
            'period' => $periodaccepted,
            'periodstart' => $period->startdate,
            'periodend' => $period->enddate,
            'periodcomplete' => $period->has_complete_range(),
            'semesterstart' => $semesterstart,
            'semesterend' => $semesterend,
        ];
    }

    /**
     * Returns whether a course belongs to the Distance category branch.
     *
     * @param object $course Course record.
     * @return bool
     */
    private function is_distance_course(object $course): bool {
        return !empty($course->category) &&
            $this->categoryparser->is_distance_category((int) $course->category);
    }

    /**
     * Returns whether a course has a recognised discipline shortname.
     *
     * @param object $course Course record.
     * @return bool
     */
    private function has_discipline_shortname(object $course): bool {
        return !empty($course->shortname) &&
            $this->shortnameparser->is_discipline_shortname($course->shortname);
    }

    /**
     * Returns whether Moodle's native course date window overlaps the semester.
     *
     * @param object $course Course record.
     * @param int $semesterstart Semester start timestamp.
     * @param int $semesterend Semester end timestamp.
     * @return bool
     */
    private function course_dates_overlap_semester(object $course, int $semesterstart, int $semesterend): bool {
        $startdate = (int) ($course->startdate ?? 0);
        $enddate = (int) ($course->enddate ?? 0);
        if (empty($startdate) || empty($enddate)) {
            return false;
        }

        return $startdate <= $semesterend && $enddate >= $semesterstart;
    }
}
