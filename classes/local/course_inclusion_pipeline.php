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
 * Shared course inclusion pipeline.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

/**
 * Selects card courses and exposes the same decisions for diagnostics.
 */
class course_inclusion_pipeline {

    /** @var course_repository Course source. */
    private $repository;

    /** @var course_filter Product-scope filter. */
    private $coursefilter;

    /** @var user_perspective_resolver Perspective resolver. */
    private $perspectiveresolver;

    /** @var course_access_filter Student access filter. */
    private $accessfilter;

    /**
     * Constructor.
     *
     * @param course_repository|null $repository Course source.
     * @param course_filter|null $coursefilter Product-scope filter.
     * @param user_perspective_resolver|null $perspectiveresolver Perspective resolver.
     * @param course_access_filter|null $accessfilter Student access filter.
     */
    public function __construct(
        ?course_repository $repository = null,
        ?course_filter $coursefilter = null,
        ?user_perspective_resolver $perspectiveresolver = null,
        ?course_access_filter $accessfilter = null
    ) {
        $this->repository = $repository ?? new course_repository();
        $this->coursefilter = $coursefilter ?? new course_filter();
        $this->perspectiveresolver = $perspectiveresolver ?? new user_perspective_resolver();
        $this->accessfilter = $accessfilter ?? new course_access_filter();
    }

    /**
     * Runs the production course-selection pipeline for a target user.
     *
     * @param int $userid Target user id.
     * @param string $requestedperspective Requested perspective key.
     * @return array Pipeline state.
     */
    public function select_for_user(int $userid, string $requestedperspective = ''): array {
        $issiteadmin = is_siteadmin($userid);
        $semesterlabel = current_semester::from_timestamp();
        $sourcecourses = $issiteadmin ? $this->repository->get_all_courses() :
            $this->repository->get_perspective_candidate_courses_for_user($userid);
        $scopedcourses = $this->coursefilter->filter_current_semester_distance_courses(
            $sourcecourses,
            $semesterlabel,
            true
        );
        $perspectives = $this->perspectiveresolver->resolve($scopedcourses, $userid, $issiteadmin);
        $selectedperspective = $this->perspectiveresolver->get_selected_perspective_key(
            $perspectives,
            $requestedperspective
        );
        $selectedcourses = $this->filter_courses_by_perspective(
            $scopedcourses,
            $perspectives,
            $selectedperspective
        );
        if ($selectedperspective === user_perspective_resolver::STUDENT) {
            $selectedcourses = $this->accessfilter->filter_courses_for_user($selectedcourses, $userid, false);
        }

        return [
            'issiteadmin' => $issiteadmin,
            'semesterlabel' => $semesterlabel,
            'sourcecourses' => $sourcecourses,
            'scopedcourses' => $scopedcourses,
            'perspectives' => $perspectives,
            'selectedperspective' => $selectedperspective,
            'courses' => $selectedcourses,
        ];
    }

    /**
     * Diagnoses the first stage that excludes one course for a target user.
     *
     * @param object $course Moodle course record.
     * @param int $userid Target user id.
     * @param string $requestedperspective Requested perspective key.
     * @return array Diagnostic data.
     */
    public function diagnose_course(object $course, int $userid, string $requestedperspective = ''): array {
        $state = $this->select_for_user($userid, $requestedperspective);
        $evaluation = $this->coursefilter->evaluate_current_semester_distance_course(
            $course,
            $state['semesterlabel'],
            true
        );
        $courseid = (int) $course->id;
        $selectedperspective = $requestedperspective ?: $state['selectedperspective'];
        $accessapplicable = $selectedperspective === user_perspective_resolver::STUDENT;
        $perspectiveaccepted = $this->perspective_contains_course(
            $state['perspectives'],
            $selectedperspective,
            $courseid
        );
        if ($requestedperspective === user_perspective_resolver::STUDENT) {
            $perspectiveaccepted = $evaluation['periodcomplete'];
        }
        $gates = [
            'repository' => $this->contains_course($state['sourcecourses'], $courseid),
            'category' => $evaluation['category'],
            'shortname' => $evaluation['shortname'],
            'period' => $evaluation['period'],
            'perspective' => $perspectiveaccepted,
            'access' => !$accessapplicable || $this->accessfilter->can_user_view_course($course, $userid, false),
            'accessapplicable' => $accessapplicable,
        ];
        $excludedat = null;
        foreach (['repository', 'category', 'shortname', 'period', 'perspective', 'access'] as $gate) {
            if (!$gates[$gate]) {
                $excludedat = $gate;
                break;
            }
        }
        $included = $excludedat === null;

        return [
            'semesterlabel' => $state['semesterlabel'],
            'semesterstart' => $evaluation['semesterstart'],
            'semesterend' => $evaluation['semesterend'],
            'periodstart' => $evaluation['periodstart'],
            'periodend' => $evaluation['periodend'],
            'periodcomplete' => $evaluation['periodcomplete'],
            'perspectives' => $state['perspectives'],
            'selectedperspective' => $selectedperspective,
            'gates' => $gates,
            'included' => $included,
            'excludedat' => $excludedat,
        ];
    }

    /**
     * Keeps only courses listed by the selected perspective.
     *
     * @param array $courses Course records.
     * @param array $perspectives Available perspectives.
     * @param string $selected Selected perspective key.
     * @return array
     */
    private function filter_courses_by_perspective(array $courses, array $perspectives, string $selected): array {
        $selectedids = [];
        foreach ($perspectives as $perspective) {
            if ($perspective['key'] === $selected) {
                $selectedids = array_flip(array_map('intval', $perspective['courseids']));
                break;
            }
        }

        if (empty($selectedids)) {
            return [];
        }

        return array_values(array_filter($courses, static function($course) use ($selectedids): bool {
            return !empty($course->id) && array_key_exists((int) $course->id, $selectedids);
        }));
    }

    /**
     * Returns whether a course record list contains an id.
     *
     * @param array $courses Course records.
     * @param int $courseid Course id.
     * @return bool
     */
    private function contains_course(array $courses, int $courseid): bool {
        foreach ($courses as $course) {
            if (!empty($course->id) && (int) $course->id === $courseid) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether one perspective contains a course id.
     *
     * @param array $perspectives Available perspectives.
     * @param string $selected Selected perspective key.
     * @param int $courseid Course id.
     * @return bool
     */
    private function perspective_contains_course(array $perspectives, string $selected, int $courseid): bool {
        foreach ($perspectives as $perspective) {
            if ($perspective['key'] === $selected) {
                return in_array($courseid, array_map('intval', $perspective['courseids']), true);
            }
        }

        return false;
    }
}
