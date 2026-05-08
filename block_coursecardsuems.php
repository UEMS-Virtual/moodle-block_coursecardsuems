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
 * Course Cards UEMS block.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/enrollib.php');

/**
 * Displays the user's enrolled courses as compact UEMS cards.
 */
class block_coursecardsuems extends block_base {
    /**
     * Initialises the block title.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_coursecardsuems');
    }

    /**
     * The block may be used on the dashboard and other pages during the MVP.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Allow more than one instance while the design is being validated.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Builds the block content.
     *
     * @return stdClass
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            $this->content->text = html_writer::div(get_string('nocourses', 'block_coursecardsuems'), 'coursecardsuems-empty');
            return $this->content;
        }

        $courses = enrol_get_my_courses(
            'id, category, shortname, fullname, startdate, enddate, visible',
            'startdate ASC, fullname ASC'
        );

        if (empty($courses)) {
            $this->content->text = html_writer::div(get_string('nocourses', 'block_coursecardsuems'), 'coursecardsuems-empty');
            return $this->content;
        }

        $cards = [];
        foreach ($courses as $course) {
            $cards[] = $this->render_course_card($course);
        }

        $this->content->text = html_writer::div(implode('', $cards), 'coursecardsuems-grid');
        return $this->content;
    }

    /**
     * Renders one course card.
     *
     * @param stdClass $course Course record.
     * @return string HTML.
     */
    private function render_course_card($course) {
        $context = context_course::instance($course->id);
        $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
        $coursename = format_string(get_course_display_name_for_list($course), true, ['context' => $context]);
        $categoryname = $this->get_category_name($course);
        $period = $this->get_period_label($course);
        [$statusclass, $statuslabel] = $this->get_status($course);
        $teachers = $this->get_teachers($context);

        $badges = html_writer::span(get_string('offer', 'block_coursecardsuems'), 'coursecardsuems-pill coursecardsuems-pill-offer');
        if (empty($course->visible)) {
            $badges .= html_writer::span(get_string('hidden', 'block_coursecardsuems'), 'coursecardsuems-pill coursecardsuems-pill-hidden');
        }

        $body = html_writer::tag('h3', $coursename, ['class' => 'coursecardsuems-title']);
        $body .= $this->render_teachers($teachers);
        $body .= html_writer::div(
            html_writer::div($badges, 'coursecardsuems-badges') .
            $this->render_course_dates($course),
            'coursecardsuems-footerline'
        );

        $side = html_writer::div(s($categoryname), 'coursecardsuems-category-label');
        $side .= html_writer::span(s($period), 'coursecardsuems-period');

        $card = html_writer::div($side, 'coursecardsuems-side');
        $card .= html_writer::div($body, 'coursecardsuems-main');
        $card .= html_writer::span($statuslabel, 'coursecardsuems-status coursecardsuems-status-' . $statusclass);

        return html_writer::link($courseurl, $card, [
            'class' => 'coursecardsuems-card',
            'aria-label' => $coursename,
        ]);
    }

    /**
     * Returns a provisional category label for the vertical band.
     *
     * @param stdClass $course Course record.
     * @return string
     */
    private function get_category_name($course) {
        try {
            $category = core_course_category::get($course->category, IGNORE_MISSING, true);
            if ($category) {
                return $category->get_formatted_name();
            }
        } catch (moodle_exception $exception) {
            // Fall through to the course shortname fallback.
        }

        return $course->shortname;
    }

    /**
     * Calculates a simple semester label from the course start date.
     *
     * @param stdClass $course Course record.
     * @return string
     */
    private function get_period_label($course) {
        if (empty($course->startdate)) {
            return get_string('periodunknown', 'block_coursecardsuems');
        }

        $year = userdate($course->startdate, '%Y');
        $month = (int) userdate($course->startdate, '%m');
        $semester = $month <= 6 ? 1 : 2;

        return $year . '/' . $semester;
    }

    /**
     * Returns the status class and label for the course.
     *
     * @param stdClass $course Course record.
     * @return array{0:string,1:string}
     */
    private function get_status($course) {
        $now = time();

        if (!empty($course->startdate) && $course->startdate > $now) {
            return ['comingsoon', get_string('comingsoon', 'block_coursecardsuems')];
        }

        if (!empty($course->enddate) && $course->enddate < $now) {
            return ['closed', get_string('closed', 'block_coursecardsuems')];
        }

        return ['open', get_string('open', 'block_coursecardsuems')];
    }

    /**
     * Renders course date range compactly.
     *
     * @param stdClass $course Course record.
     * @return string HTML.
     */
    private function render_course_dates($course) {
        $datehtml = html_writer::tag('i', '', [
            'class' => 'fa fa-calendar-o coursecardsuems-date-icon',
            'aria-hidden' => 'true',
        ]);
        if (!empty($course->startdate)) {
            $datehtml .= html_writer::span($this->format_course_date($course->startdate), 'coursecardsuems-date-value');
        }
        if (!empty($course->enddate)) {
            $datehtml .= html_writer::span('→', 'coursecardsuems-date-separator');
            $datehtml .= html_writer::span($this->format_course_date($course->enddate), 'coursecardsuems-date-value');
        }

        if (empty($course->startdate) && empty($course->enddate)) {
            return '';
        }

        return html_writer::div($datehtml, 'coursecardsuems-dates');
    }

    /**
     * Formats a course date for display.
     *
     * @param int|null $timestamp Date timestamp.
     * @return string
     */
    private function format_course_date($timestamp) {
        if (empty($timestamp)) {
            return get_string('dateunknown', 'block_coursecardsuems');
        }

        return userdate($timestamp, '%d/%m/%Y');
    }

    /**
     * Loads a small list of likely teachers for the course.
     *
     * @param context_course $context Course context.
     * @return array
     */
    private function get_teachers($context) {
        return get_enrolled_users(
            $context,
            'moodle/course:manageactivities',
            0,
            'u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename, u.email',
            'u.lastname ASC, u.firstname ASC',
            0,
            3
        );
    }

    /**
     * Renders teacher initials and names.
     *
     * @param array $teachers Teacher users.
     * @return string HTML.
     */
    private function render_teachers($teachers) {
        if (empty($teachers)) {
            return '';
        }

        $teacher = reset($teachers);
        $initials = $this->get_initials($teacher);
        $teachername = fullname($teacher);

        $label = count($teachers) > 1 ? get_string('teachers', 'block_coursecardsuems') : get_string('teacher', 'block_coursecardsuems');
        $extra = count($teachers) > 1 ? html_writer::div(get_string('others', 'block_coursecardsuems', count($teachers) - 1), 'coursecardsuems-teacher-extra') : '';

        return html_writer::div(
            html_writer::span(s($initials), 'coursecardsuems-avatar') .
            html_writer::div(
                html_writer::div($label, 'coursecardsuems-teacher-label') .
                html_writer::div(s($teachername), 'coursecardsuems-teacher-name') .
                $extra,
                'coursecardsuems-teacher-text'
            ),
            'coursecardsuems-teacher'
        );
    }

    /**
     * Builds initials for a user.
     *
     * @param stdClass $user User record.
     * @return string
     */
    private function get_initials($user) {
        $first = trim((string) $user->firstname);
        $last = trim((string) $user->lastname);
        $initials = '';

        if ($first !== '') {
            $initials .= core_text::substr($first, 0, 1);
        }
        if ($last !== '') {
            $initials .= core_text::substr($last, 0, 1);
        }

        return core_text::strtoupper($initials ?: '?');
    }
}
