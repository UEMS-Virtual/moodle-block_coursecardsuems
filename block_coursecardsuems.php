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

/**
 * Displays the UEMS course cards block.
 */
class block_coursecardsuems extends block_base {

    /**
     * Initialises the block title.
     */
    public function init() {
        $this->title = get_string(
            'currentsemester',
            'block_coursecardsuems',
            \block_coursecardsuems\local\current_semester::from_timestamp()
        );
    }

    /**
     * The block is available on all page types.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Multiple instances are allowed per page.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Declares that this block has site-level settings.
     *
     * Moodle only loads blocks/{plugin}/settings.php when has_config() returns true.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Adds active state and URLs to perspective options.
     *
     * @param array $perspectives Available perspectives.
     * @param string $selected Selected perspective key.
     * @return array
     */
    private function prepare_perspective_links(array $perspectives, string $selected): array {
        global $PAGE;

        foreach ($perspectives as &$perspective) {
            $perspective['isactive'] = $perspective['key'] === $selected;
            $url = new moodle_url($PAGE->url, ['coursecardsuemsview' => $perspective['key']]);
            $perspective['url'] = $url->out(false);
        }
        unset($perspective);

        return $perspectives;
    }

    /**
     * Returns whether the selected perspective should link this course.
     *
     * Staff perspectives intentionally link their assigned rooms even when Moodle may later
     * deny access. The Moodle denial page is clearer feedback than hiding a potentially
     * valid room from the staff-facing workload view.
     *
     * @param object $course Course record.
     * @param string $selectedperspective Selected perspective key.
     * @param bool $issiteadmin Whether the user is site admin.
     * @return bool
     */
    private function can_link_course_for_perspective(object $course, string $selectedperspective, bool $issiteadmin): bool {
        if (empty($course->id)) {
            return false;
        }

        if ($issiteadmin && $selectedperspective === \block_coursecardsuems\local\user_perspective_resolver::ADMIN) {
            return true;
        }

        return $selectedperspective === \block_coursecardsuems\local\user_perspective_resolver::TUTOR ||
            $selectedperspective === \block_coursecardsuems\local\user_perspective_resolver::TEACHER;
    }

    /**
     * Builds the block content.
     *
     * @return stdClass
     */
    public function get_content() {
        global $OUTPUT, $PAGE, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $periodreader = new \block_coursecardsuems\local\informative_period_reader();
        $mapper = new \block_coursecardsuems\local\course_card_mapper($periodreader);
        $pipeline = new \block_coursecardsuems\local\course_inclusion_pipeline(
            null,
            new \block_coursecardsuems\local\course_filter(null, null, $periodreader),
            new \block_coursecardsuems\local\user_perspective_resolver($periodreader)
        );
        $selection = $pipeline->select_for_user(
            (int) $USER->id,
            optional_param('coursecardsuemsview', '', PARAM_ALPHA)
        );
        $semesterlabel = $selection['semesterlabel'];
        $issiteadmin = $selection['issiteadmin'];
        $selectedperspective = $selection['selectedperspective'];
        $perspectives = $this->prepare_perspective_links($selection['perspectives'], $selectedperspective);
        $courses = $selection['courses'];

        if (empty($courses) && !$issiteadmin) {
            $this->content->text = '';
            return $this->content;
        }

        $courses = array_map(function($course) use ($mapper, $issiteadmin, $selectedperspective): array {
            $canlinkcourse = $this->can_link_course_for_perspective($course, $selectedperspective, $issiteadmin);
            return $mapper->map($course, $canlinkcourse);
        }, $courses);
        usort($courses, static function(array $a, array $b): int {
            if ($a['status'] !== $b['status']) {
                $order = [
                    \block_coursecardsuems\local\course_status_resolver::OPEN => 0,
                    \block_coursecardsuems\local\course_status_resolver::COMINGSOON => 1,
                    \block_coursecardsuems\local\course_status_resolver::CLOSED => 2,
                    \block_coursecardsuems\local\course_status_resolver::NODATE => 3,
                ];
                return $order[$a['status']] <=> $order[$b['status']];
            }

            if ($a['status'] === \block_coursecardsuems\local\course_status_resolver::CLOSED) {
                $pendingcomparison = (int) !empty($b['haspendingactivity']) <=> (int) !empty($a['haspendingactivity']);
                if ($pendingcomparison !== 0) {
                    return $pendingcomparison;
                }
            }

            $datecomparison = $a['sortkey'] <=> $b['sortkey'];
            if ($datecomparison !== 0) {
                return $datecomparison;
            }

            return strnatcasecmp($a['shortname'] ?? '', $b['shortname'] ?? '');
        });

        $renderer = $PAGE->get_renderer('block_coursecardsuems');
        $summary = new \block_coursecardsuems\output\summary($courses, $semesterlabel, $perspectives);

        $this->content->text = '';
        if ($issiteadmin && \block_coursecardsuems\local\current_semester::has_invalid_custom_window()) {
            $this->content->text .= $OUTPUT->notification(
                get_string('invalidcustomsemester', 'block_coursecardsuems'),
                'notifyproblem'
            );
        }
        $this->content->text .= $renderer->render($summary);

        $PAGE->requires->js_call_amd('block_coursecardsuems/section_tabs', 'init');

        return $this->content;
    }
}
