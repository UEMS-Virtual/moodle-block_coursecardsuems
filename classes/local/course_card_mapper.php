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
 * Course card mapper.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

use context_course;
use moodle_url;

/**
 * Maps Moodle course records into template-ready discipline view models.
 */
class course_card_mapper {

    /** @var informative_period_reader Informative period reader. */
    private $periodreader;

    /** @var course_status_resolver Status resolver. */
    private $statusresolver;

    /** @var category_parser Category parser. */
    private $categoryparser;

    /** @var course_shortname_parser Shortname parser. */
    private $shortnameparser;

    /** @var int|null Timestamp used to resolve status. */
    private $now;

    /**
     * Constructor.
     *
     * @param informative_period_reader|null $periodreader Informative period reader.
     * @param course_status_resolver|null $statusresolver Status resolver.
     * @param category_parser|null $categoryparser Category parser.
     * @param int|null $now Timestamp used to resolve status.
     * @param course_shortname_parser|null $shortnameparser Shortname parser.
     */
    public function __construct(
        ?informative_period_reader $periodreader = null,
        ?course_status_resolver $statusresolver = null,
        ?category_parser $categoryparser = null,
        ?int $now = null,
        ?course_shortname_parser $shortnameparser = null
    ) {
        $this->periodreader = $periodreader ?? new informative_period_reader();
        $this->statusresolver = $statusresolver ?? new course_status_resolver();
        $this->categoryparser = $categoryparser ?? new category_parser();
        $this->shortnameparser = $shortnameparser ?? new course_shortname_parser();
        $this->now = $now;
    }

    /**
     * Maps a Moodle course record to a discipline view model.
     *
     * @param object $course Moodle course record.
     * @return array Template-ready data without pre-rendered HTML.
     */
    public function map(object $course): array {
        $period = $this->periodreader->get_period((int) $course->id);
        $status = $this->statusresolver->resolve($period, $course, $this->now);
        [$code, $title] = $this->split_course_title(get_course_display_name_for_list($course));

        return [
            'id' => (int) $course->id,
            'url' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
            'code' => $code,
            'hascode' => $code !== '',
            'title' => format_string($title, true, ['context' => context_course::instance($course->id)]),
            'group' => $this->shortnameparser->get_compact_group($course->shortname ?? ''),
            'series' => $this->categoryparser->get_series_name($course->category ?? 0),
            'period' => [
                'start' => $period->startdate,
                'end' => $period->enddate,
                'hasperiod' => $period->has_any_date(),
                'label' => $this->format_period($period),
            ],
            'status' => $status,
            'statuslabel' => $this->statusresolver->get_label($status),
            'sortkey' => $this->statusresolver->get_sort_key($status, $period, $course),
            'teachers' => $this->get_teachers(context_course::instance($course->id)),
        ];
    }

    /**
     * Splits `[CODE] Name` into code and title.
     *
     * @param string $fullname Course display name.
     * @return array{0:string,1:string}
     */
    private function split_course_title(string $fullname): array {
        if (preg_match('/^\s*\[([^\]]+)\]\s*(.+)$/u', $fullname, $matches) !== 1) {
            return ['', trim($fullname)];
        }

        return [trim($matches[1]), trim($matches[2])];
    }

    /**
     * Formats the informative period for provisional template output.
     *
     * @param informative_period $period Informative period.
     * @return string
     */
    private function format_period(informative_period $period): string {
        $startdate = $period->startdate ? userdate($period->startdate, get_string('strftimedateshort')) :
            get_string('dateunknown', 'block_coursecardsuems');
        $enddate = $period->enddate ? userdate($period->enddate, get_string('strftimedateshort')) :
            get_string('dateunknown', 'block_coursecardsuems');

        return $startdate . ' – ' . $enddate;
    }

    /**
     * Returns teacher users assigned to standard Moodle teacher roles.
     *
     * @param context_course $context Course context.
     * @return array
     */
    private function get_teachers(context_course $context): array {
        global $DB;

        $roles = $DB->get_records_list('role', 'shortname', ['editingteacher', 'teacher']);
        if (empty($roles)) {
            return [];
        }

        $teachers = [];
        foreach ($roles as $role) {
            $users = get_role_users($role->id, $context, false, 'u.id, u.firstname, u.lastname, u.firstnamephonetic, ' .
                'u.lastnamephonetic, u.middlename, u.alternatename', 'u.lastname ASC, u.firstname ASC');
            foreach ($users as $user) {
                $teachers[(int) $user->id] = [
                    'id' => (int) $user->id,
                    'name' => fullname($user),
                ];
            }
        }

        return array_values($teachers);
    }
}
