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

use block_coursecardsuems\local\course_inclusion_pipeline;
use block_coursecardsuems\local\user_perspective_resolver;
use context_course;
use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
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
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'userid' => $userid,
            'perspective' => $perspective,
        ]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('block/coursecardsuems:viewdiagnostics', $context);

        $validperspectives = [
            user_perspective_resolver::STUDENT,
            user_perspective_resolver::TUTOR,
            user_perspective_resolver::TEACHER,
            user_perspective_resolver::ADMIN,
        ];
        if ($params['perspective'] !== '' && !in_array($params['perspective'], $validperspectives, true)) {
            throw new \invalid_parameter_exception('Invalid perspective.');
        }

        $targetuserid = $params['userid'] ?: (int) $USER->id;
        $DB->get_record('user', ['id' => $targetuserid, 'deleted' => 0], 'id', MUST_EXIST);
        $course = $DB->get_record(
            'course',
            ['id' => $params['courseid']],
            'id, category, shortname, fullname, startdate, enddate, visible',
            MUST_EXIST
        );
        self::validate_context(context_course::instance((int) $course->id));
        $diagnostic = (new course_inclusion_pipeline())->diagnose_course(
            $course,
            $targetuserid,
            $params['perspective']
        );
        $perspectives = array_map(static function(array $item): array {
            return [
                'key' => $item['key'],
                'count' => $item['count'],
                'isdefault' => $item['isdefault'],
            ];
        }, $diagnostic['perspectives']);

        return [
            'course' => [
                'id' => (int) $course->id,
                'shortname' => $course->shortname,
                'categoryid' => (int) $course->category,
                'visible' => (bool) $course->visible,
            ],
            'target' => [
                'userid' => $targetuserid,
                'issiteadmin' => is_siteadmin($targetuserid),
            ],
            'semester' => [
                'label' => $diagnostic['semesterlabel'],
                'startdate' => $diagnostic['semesterstart'],
                'enddate' => $diagnostic['semesterend'],
            ],
            'informativeperiod' => [
                'ead_inicio' => $diagnostic['periodstart'],
                'ead_final' => $diagnostic['periodend'],
                'complete' => $diagnostic['periodcomplete'],
            ],
            'perspectives' => $perspectives,
            'selectedperspective' => $diagnostic['selectedperspective'] ?: null,
            'gates' => $diagnostic['gates'],
            'included' => $diagnostic['included'],
            'excludedat' => $diagnostic['excludedat'],
        ];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'course' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Course id.'),
                'shortname' => new external_value(PARAM_RAW, 'Course shortname.'),
                'categoryid' => new external_value(PARAM_INT, 'Course category id.'),
                'visible' => new external_value(PARAM_BOOL, 'Whether the course is visible.'),
            ]),
            'target' => new external_single_structure([
                'userid' => new external_value(PARAM_INT, 'Target user id.'),
                'issiteadmin' => new external_value(PARAM_BOOL, 'Whether the target user is a site admin.'),
            ]),
            'semester' => new external_single_structure([
                'label' => new external_value(PARAM_RAW, 'Current semester label.'),
                'startdate' => new external_value(PARAM_INT, 'Current semester start timestamp.'),
                'enddate' => new external_value(PARAM_INT, 'Current semester end timestamp.'),
            ]),
            'informativeperiod' => new external_single_structure([
                'ead_inicio' => new external_value(PARAM_INT, 'Informative period start timestamp.'),
                'ead_final' => new external_value(PARAM_INT, 'Informative period end timestamp.'),
                'complete' => new external_value(PARAM_BOOL, 'Whether both informative dates exist.'),
            ]),
            'perspectives' => new external_multiple_structure(new external_single_structure([
                'key' => new external_value(PARAM_ALPHA, 'Perspective key.'),
                'count' => new external_value(PARAM_INT, 'Course count.'),
                'isdefault' => new external_value(PARAM_BOOL, 'Whether this is the default perspective.'),
            ])),
            'selectedperspective' => new external_value(
                PARAM_ALPHA,
                'Selected perspective key.',
                VALUE_REQUIRED,
                null,
                NULL_ALLOWED
            ),
            'gates' => new external_single_structure([
                'repository' => new external_value(PARAM_BOOL, 'Course belongs to the target source.'),
                'category' => new external_value(PARAM_BOOL, 'Course belongs to the Distance branch.'),
                'shortname' => new external_value(PARAM_BOOL, 'Course shortname is recognised.'),
                'period' => new external_value(PARAM_BOOL, 'Course overlaps the current semester.'),
                'perspective' => new external_value(PARAM_BOOL, 'Course belongs to the selected perspective.'),
                'access' => new external_value(PARAM_BOOL, 'Target user passes the student access gate.'),
                'accessapplicable' => new external_value(PARAM_BOOL, 'Whether student access applies.'),
            ]),
            'included' => new external_value(PARAM_BOOL, 'Whether the course is included.'),
            'excludedat' => new external_value(
                PARAM_ALPHA,
                'First exclusion stage.',
                VALUE_REQUIRED,
                null,
                NULL_ALLOWED
            ),
        ]);
    }
}
