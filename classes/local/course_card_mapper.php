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
use core_text;
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
     * @param bool $canaccesshidden Whether hidden/unavailable courses should still be linked.
     * @return array Template-ready data without pre-rendered HTML.
     */
    public function map(object $course, bool $canaccesshidden = false): array {
        $period = $this->periodreader->get_period((int) $course->id);
        $temporalstatus = $this->statusresolver->resolve($period, $course, $this->now);
        $isvisible = !property_exists($course, 'visible') || (bool) $course->visible;
        $status = $this->get_display_status($temporalstatus, $isvisible);
        $isclickable = $this->is_clickable($status, $isvisible, $canaccesshidden);
        $ispreparing = !$isvisible && $temporalstatus === course_status_resolver::OPEN;
        $context = context_course::instance($course->id);
        [$code, $title] = $this->extract_display_title(get_course_display_name_for_list($course));
        $group = $this->shortnameparser->get_compact_group($course->shortname ?? '');
        $series = $this->categoryparser->get_series_name($course->category ?? 0);
        $isreoferta = $this->shortnameparser->is_reoferta($course->shortname ?? '');
        $supertitleparts = array_filter([$group, $series]);
        $supertitle = implode(' · ', $supertitleparts);
        $teachers = $this->get_teachers($context);
        $displayteachers = array_slice($teachers, 0, 2);

        return [
            'id' => (int) $course->id,
            'url' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
            'hasurl' => $isclickable,
            'linklabel' => get_string('opencourse', 'block_coursecardsuems', format_string($title, true, ['context' => $context])),
            'isavailable' => $isvisible,
            'temporalstatus' => $temporalstatus,
            'ispreparing' => $ispreparing,
            'code' => $code,
            'hascode' => $code !== '',
            'title' => format_string($title, true, ['context' => $context]),
            'group' => $group,
            'hasgroup' => $group !== '',
            'series' => $series,
            'hasseries' => $series !== '',
            'isreoferta' => $isreoferta,
            'supertitle' => $supertitle,
            'hassupertitle' => $supertitle !== '',
            'period' => [
                'hasperiod' => $period->has_any_date(),
                'start' => $period->startdate,
                'end' => $period->enddate,
                'label' => $ispreparing ? get_string('availablecomingsoon', 'block_coursecardsuems') :
                    $this->format_period($period),
                'showdates' => $period->has_any_date() && !$ispreparing,
                'startlabel' => $this->format_date($period->startdate),
                'endlabel' => $this->format_date($period->enddate),
            ],
            'status' => $status,
            'statuslabel' => $this->statusresolver->get_label($status),
            'statusclass' => 'coursecardsuems-status-' . $status,
            'isclosed' => $status === course_status_resolver::CLOSED,
            'sortkey' => $this->statusresolver->get_sort_key($status, $period, $course),
            'teachers' => $teachers,
            'hasteachers' => !empty($teachers),
            'displayteachers' => $displayteachers,
            'hasdisplayteachers' => !empty($displayteachers),
            'teacherdisplayname' => $this->get_teacher_display_name($teachers),
        ];
    }

    /**
     * Returns the status presented to students after Moodle availability rules.
     *
     * @param string $temporalstatus Status resolved from dates.
     * @param bool $isvisible Whether Moodle course is visible/available.
     * @return string Display status.
     */
    private function get_display_status(string $temporalstatus, bool $isvisible): string {
        if (!$isvisible && $temporalstatus === course_status_resolver::OPEN) {
            return course_status_resolver::COMINGSOON;
        }

        return $temporalstatus;
    }

    /**
     * Returns whether the course card should link to the Moodle room.
     *
     * @param string $status Display status.
     * @param bool $isvisible Whether Moodle course is visible/available.
     * @param bool $canaccesshidden Whether hidden courses should still be linked.
     * @return bool
     */
    private function is_clickable(string $status, bool $isvisible, bool $canaccesshidden = false): bool {
        if ($canaccesshidden) {
            return true;
        }

        if (!$isvisible) {
            return false;
        }

        return $status === course_status_resolver::OPEN || $status === course_status_resolver::CLOSED;
    }

    /**
     * Strips the bracketed code prefix from a course display name.
     *
     * Returns [code, title]; code is discarded at the call site.
     *
     * @param string $fullname Course display name.
     * @return array{0:string,1:string}
     */
    private function extract_display_title(string $fullname): array {
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
        return $this->format_date($period->startdate) . ' – ' . $this->format_date($period->enddate);
    }

    /**
     * Formats a timestamp for display.
     *
     * @param int $timestamp Timestamp or zero.
     * @return string
     */
    private function format_date(int $timestamp): string {
        return $timestamp ? userdate($timestamp, get_string('strftimedateshort')) :
            get_string('dateunknown', 'block_coursecardsuems');
    }

    /**
     * Returns teacher users assigned to standard Moodle teacher roles.
     *
     * @param context_course $context Course context.
     * @return array
     */
    private function get_teachers(context_course $context): array {
        global $DB, $PAGE;

        $roles = $DB->get_records_list('role', 'shortname', ['editingteacher', 'teacher', 'mod_prof']);
        if (empty($roles)) {
            return [];
        }

        $teachers = [];
        foreach ($roles as $role) {
            $users = get_role_users($role->id, $context, false, 'u.id, u.firstname, u.lastname, u.firstnamephonetic, ' .
                'u.lastnamephonetic, u.middlename, u.alternatename, u.picture, u.imagealt, u.email',
                'u.lastname ASC, u.firstname ASC');
            foreach ($users as $user) {
                $name = fullname($user);
                $hasavatarurl = !empty($user->picture);
                $avatarurl = '';
                if ($hasavatarurl) {
                    $userpicture = new \user_picture($user);
                    $userpicture->size = 50;
                    $avatarurl = $userpicture->get_url($PAGE)->out(false);
                }

                $teachers[(int) $user->id] = [
                    'id' => (int) $user->id,
                    'name' => $name,
                    'shortname' => $this->get_short_name($user, $name),
                    'initials' => $this->get_initials($name),
                    'hasavatarurl' => $hasavatarurl,
                    'avatarurl' => $avatarurl,
                    'avatarcolorclass' => $this->get_avatar_color_class((int) $user->id),
                ];
            }
        }

        return array_values($teachers);
    }

    /**
     * Returns the teacher text shown next to the avatar stack.
     *
     * @param array $teachers Teacher view models.
     * @return string Display label.
     */
    private function get_teacher_display_name(array $teachers): string {
        $teachercount = count($teachers);
        if ($teachercount === 0) {
            return get_string('teacherunknown', 'block_coursecardsuems');
        }

        if ($teachercount === 1) {
            return $teachers[0]['name'];
        }

        $names = array_map(static function(array $teacher): string {
            return $teacher['shortname'];
        }, array_slice($teachers, 0, 2));
        $label = implode(', ', $names);

        if ($teachercount > 2) {
            $label .= ' ' . get_string('others', 'block_coursecardsuems', $teachercount - 2);
        }

        return $label;
    }

    /**
     * Returns a compact teacher name: firstname plus lastname initial.
     *
     * @param object $user Moodle user record.
     * @param string $fallback Full name fallback.
     * @return string Compact name.
     */
    private function get_short_name(object $user, string $fallback): string {
        $firstname = trim($user->firstname ?? '');
        $lastname = trim($user->lastname ?? '');
        if ($firstname === '') {
            return $fallback;
        }

        if ($lastname === '') {
            return $firstname;
        }

        return $firstname . ' ' . core_text::strtoupper(core_text::substr($lastname, 0, 1)) . '.';
    }

    /**
     * Returns the deterministic avatar color class for a user id.
     *
     * @param int $userid Moodle user id.
     * @return string CSS class.
     */
    private function get_avatar_color_class(int $userid): string {
        return 'coursecardsuems-avatar-color-' . ($userid % 10);
    }

    /**
     * Returns up to two initials from a display name.
     *
     * @param string $name Full name.
     * @return string Initials.
     */
    private function get_initials(string $name): string {
        $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($parts)) {
            return '?';
        }

        $first = core_text::substr($parts[0], 0, 1);
        $last = count($parts) > 1 ? core_text::substr(end($parts), 0, 1) : '';

        return core_text::strtoupper($first . $last);
    }
}
