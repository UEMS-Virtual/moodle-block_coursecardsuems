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
 * Course activity resolver.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Detects whether a Moodle course still has dated activities to happen or close.
 */
class course_activity_resolver {

    /** @var array Supported Moodle activity date fields by module table. */
    private const DATE_FIELDS_BY_MODULE = [
        'assign' => ['allowsubmissionsfromdate', 'duedate', 'cutoffdate'],
        'quiz' => ['timeopen', 'timeclose'],
        'choice' => ['timeopen', 'timeclose'],
        'lesson' => ['available', 'deadline'],
        'feedback' => ['timeopen', 'timeclose'],
        'data' => ['timeavailablefrom', 'timeavailableto', 'timeviewfrom', 'timeviewto'],
        'workshop' => ['submissionstart', 'submissionend', 'assessmentstart', 'assessmentend'],
    ];

    /**
     * Returns whether the course has any visible activity with a relevant date still pending.
     *
     * An activity is considered pending when any supported date field is in the future or
     * still open at the given timestamp. Modules without dated fields are ignored.
     *
     * @param int $courseid Course id.
     * @param int|null $now Current timestamp. Uses current time when null.
     * @return bool
     */
    public function has_pending_activity(int $courseid, ?int $now = null): bool {
        global $DB;

        $now = $now ?? time();
        foreach (self::DATE_FIELDS_BY_MODULE as $modulename => $configuredfields) {
            $fields = $this->get_existing_date_fields($modulename, $configuredfields);
            if (empty($fields)) {
                continue;
            }

            $records = $DB->get_records_sql(
                "SELECT activity.id, " . implode(', ', array_map(static function(string $field): string {
                    return 'activity.' . $field;
                }, $fields)) . "
                   FROM {{$modulename}} activity
                   JOIN {modules} m ON m.name = :modulename
                   JOIN {course_modules} cm ON cm.module = m.id AND cm.instance = activity.id
                  WHERE activity.course = :courseid
                    AND cm.course = :courseidcm
                    AND cm.visible = 1
                    AND cm.deletioninprogress = 0",
                [
                    'modulename' => $modulename,
                    'courseid' => $courseid,
                    'courseidcm' => $courseid,
                ]
            );

            foreach ($records as $record) {
                foreach ($fields as $field) {
                    $timestamp = (int) ($record->{$field} ?? 0);
                    if ($timestamp > 0 && $timestamp >= $now) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns configured date fields that exist in this Moodle installation.
     *
     * @param string $tablename Module table name.
     * @param array $fields Candidate date fields.
     * @return array Existing fields.
     */
    private function get_existing_date_fields(string $tablename, array $fields): array {
        global $DB;

        $dbman = $DB->get_manager();
        if (!$dbman->table_exists($tablename)) {
            return [];
        }

        $columns = $DB->get_columns($tablename);

        return array_values(array_filter($fields, static function(string $field) use ($columns): bool {
            return array_key_exists($field, $columns);
        }));
    }
}
