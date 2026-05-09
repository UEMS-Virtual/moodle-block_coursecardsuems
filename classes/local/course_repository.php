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
}
