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
     * The block may be used on the dashboard and other pages during reconstruction.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Allow more than one instance while the reconstruction is being validated.
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
        global $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $semesterlabel = \block_coursecardsuems\local\current_semester::from_timestamp();
        $repository = new \block_coursecardsuems\local\course_repository();
        $periodreader = new \block_coursecardsuems\local\informative_period_reader();
        $mapper = new \block_coursecardsuems\local\course_card_mapper($periodreader);
        $coursefilter = new \block_coursecardsuems\local\course_filter(null, null, $periodreader);
        $courses = $coursefilter->filter_current_semester_distance_courses(
            $repository->get_enrolled_courses_for_current_user(),
            $semesterlabel
        );
        $courses = array_map(static function($course) use ($mapper): array {
            return $mapper->map($course);
        }, $courses);
        usort($courses, static function(array $a, array $b): int {
            if ($a['status'] !== $b['status']) {
                $order = [
                    \block_coursecardsuems\local\course_status_resolver::OPEN => 0,
                    \block_coursecardsuems\local\course_status_resolver::COMINGSOON => 1,
                    \block_coursecardsuems\local\course_status_resolver::CLOSED => 2,
                ];
                return $order[$a['status']] <=> $order[$b['status']];
            }

            return $a['sortkey'] <=> $b['sortkey'];
        });

        $renderer = $PAGE->get_renderer('block_coursecardsuems');
        $summary = new \block_coursecardsuems\output\summary('', '', $courses);

        $this->content->text = $renderer->render($summary);

        return $this->content;
    }
}
