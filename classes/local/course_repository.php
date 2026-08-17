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
 * Course repository.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Reads Moodle courses relevant to the current user.
 */
class course_repository {

    /**
     * Returns courses enrolled for the current user.
     *
     * This layer intentionally returns Moodle course records only. Filtering by EaD,
     * Semestre vigente, status and visual mapping belongs to later domain slices.
     *
     * @return array Course records indexed by course id.
     */
    public function get_enrolled_courses_for_current_user(): array {
        if (!isloggedin() || isguestuser()) {
            return [];
        }

        require_once($GLOBALS['CFG']->libdir . '/enrollib.php');

        return enrol_get_my_courses(
            'id, category, shortname, fullname, startdate, enddate, visible',
            'fullname ASC'
        );
    }

    /**
     * Returns courses that can belong to any non-admin perspective for the current user.
     *
     * @return array Course records indexed by course id.
     */
    public function get_perspective_candidate_courses_for_current_user(): array {
        global $USER;

        if (!isloggedin() || isguestuser()) {
            return [];
        }

        return $this->add_direct_staff_courses(
            $this->get_enrolled_courses_for_current_user(),
            (int) $USER->id
        );
    }

    /**
     * Returns courses that can belong to a target user's non-admin perspectives.
     *
     * Unlike the current-user method, this does not change the Moodle session while an
     * administrator diagnoses another user's course list.
     *
     * @param int $userid Target user id.
     * @return array Course records indexed by course id.
     */
    public function get_perspective_candidate_courses_for_user(int $userid): array {
        require_once($GLOBALS['CFG']->libdir . '/enrollib.php');

        $courses = enrol_get_users_courses(
            $userid,
            true,
            'id, category, shortname, fullname, startdate, enddate, visible',
            'fullname ASC'
        );

        return $this->add_direct_staff_courses($courses, $userid);
    }

    /**
     * Adds direct tutor and teacher role assignments to an enrolled-course source.
     *
     * @param array $courses Course records indexed by id.
     * @param int $userid Target user id.
     * @return array Candidate courses sorted by full name.
     */
    private function add_direct_staff_courses(array $courses, int $userid): array {
        global $DB;

        $roles = ['mod_tutor', 'mod_medpdg', 'editingteacher', 'teacher', 'mod_prof'];
        [$roleinsql, $roleparams] = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'role');
        $params = ['userid' => $userid, 'contextlevel' => CONTEXT_COURSE] + $roleparams;
        $sql = "SELECT c.id, c.category, c.shortname, c.fullname, c.startdate, c.enddate, c.visible
                  FROM {course} c
                  JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel
                  JOIN {role_assignments} ra ON ra.contextid = ctx.id AND ra.userid = :userid
                  JOIN {role} r ON r.id = ra.roleid
                 WHERE r.shortname $roleinsql
              ORDER BY c.fullname ASC";

        foreach ($DB->get_records_sql($sql, $params) as $course) {
            $courses[(int) $course->id] = $course;
        }

        uasort($courses, static function($a, $b): int {
            return strnatcasecmp($a->fullname ?? '', $b->fullname ?? '');
        });

        return $courses;
    }

    /**
     * Returns all Moodle course records that can be evaluated by product filters.
     *
     * Site admins use this broader source so they can inspect every EaD discipline
     * in the current semester, even without enrolment.
     *
     * @return array Course records indexed by course id.
     */
    public function get_all_courses(): array {
        global $DB;

        return $DB->get_records(
            'course',
            null,
            'fullname ASC',
            'id, category, shortname, fullname, startdate, enddate, visible'
        );
    }
}
