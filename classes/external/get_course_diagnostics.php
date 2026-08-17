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
 * External function for course inclusion diagnostics.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\external;

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Reports whether a course belongs to the cards shown to a target user.
 */
class get_course_diagnostics extends external_api {

    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course id.', VALUE_REQUIRED),
            'userid' => new external_value(PARAM_INT, 'Target user id; zero uses the caller.', VALUE_DEFAULT, 0),
            'perspective' => new external_value(PARAM_ALPHA, 'Requested perspective or empty for default.', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Executes the diagnostic.
     *
     * @param int $courseid Course id.
     * @param int $userid Target user id or zero.
     * @param string $perspective Requested perspective.
     * @return array
     */
    public static function execute(int $courseid, int $userid = 0, string $perspective = ''): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'userid' => $userid,
            'perspective' => $perspective,
        ]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('block/coursecardsuems:viewdiagnostics', $context);

        return [];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([]);
    }
}
