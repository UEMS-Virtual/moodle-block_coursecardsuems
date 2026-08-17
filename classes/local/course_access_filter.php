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
 * Course access filter.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

use context_course;

/**
 * Keeps only courses that belong to the block audience for the current user.
 */
class course_access_filter {

    /** Capability that marks student-facing block content access. */
    private const VIEW_CONTENT_CAPABILITY = 'block/coursecardsuems:viewcontent';

    /**
     * Keeps courses visible to the current user in this student-facing block.
     *
     * Site admins can inspect every candidate course. Other users must have the
     * student-facing capability in the course context; doanything is disabled so
     * broad manager permissions do not turn the block into a staff view.
     *
     * @param array $courses Course records.
     * @param bool $issiteadmin Whether the current user is a site admin.
     * @return array Filtered course records, preserving original order.
     */
    public function filter_courses_for_current_user(array $courses, bool $issiteadmin = false): array {
        return $this->filter_courses_for_user($courses, null, $issiteadmin);
    }

    /**
     * Keeps courses available to a target user without changing the Moodle session.
     *
     * @param array $courses Course records.
     * @param int|null $userid Target user id; null means current user.
     * @param bool $issiteadmin Whether the target user is a site admin.
     * @return array Filtered course records.
     */
    public function filter_courses_for_user(array $courses, ?int $userid, bool $issiteadmin = false): array {
        return array_values(array_filter($courses, function($course) use ($userid, $issiteadmin): bool {
            return $this->can_user_view_course($course, $userid, $issiteadmin);
        }));
    }

    /**
     * Returns whether one course belongs to the student-facing audience for a target user.
     *
     * @param object $course Course record.
     * @param int|null $userid Target user id; null means current user.
     * @param bool $issiteadmin Whether the target user is a site admin.
     * @return bool
     */
    public function can_user_view_course(object $course, ?int $userid, bool $issiteadmin = false): bool {
        if ($issiteadmin) {
            return true;
        }

        if (empty($course->id)) {
            return false;
        }

        $context = context_course::instance((int) $course->id);

        return has_capability(self::VIEW_CONTENT_CAPABILITY, $context, $userid, false);
    }
}
