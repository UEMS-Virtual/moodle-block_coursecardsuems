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
 * Summary renderable for the Course Cards UEMS block.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\output;

defined('MOODLE_INTERNAL') || die();

use block_coursecardsuems\local\course_status_resolver;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Renderable that groups discipline view models into status sections for the summary template.
 */
class summary implements renderable, templatable {

    /** @var array List of course summaries for the current user. */
    private $courses;

    /** @var string Semester identifier, e.g. "2026/1". */
    private $semesterlabel;

    /** @var array Available user perspectives. */
    private $perspectives;

    /**
     * Constructor.
     *
     * @param array $courses List of course summaries.
     * @param string $semesterlabel Semester identifier shown in the styled header.
     * @param array $perspectives Available user perspectives.
     */
    public function __construct(array $courses = [], string $semesterlabel = '', array $perspectives = []) {
        $this->courses = $courses;
        $this->semesterlabel = $semesterlabel;
        $this->perspectives = $perspectives;
    }

    /**
     * Exports data for the Mustache template.
     *
     * @param renderer_base $output Renderer instance.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->sections = $this->get_sections();
        $data->hassections = true;
        $data->semesterlabel = $this->semesterlabel;
        $data->hassemesterlabel = trim($this->semesterlabel) !== '';
        $data->perspectives = $this->perspectives;
        $data->hasperspectives = count($this->perspectives) > 1;
        $data->filters = $this->get_filters();
        $data->hasfilters = !empty($data->filters);

        return $data;
    }

    /**
     * Builds dynamic filter definitions from the active course set.
     *
     * @return array Filter definitions.
     */
    private function get_filters(): array {
        if (!$this->should_show_filters()) {
            return [];
        }

        $definitions = [
            'level' => [
                'label' => get_string('filterlevel', 'block_coursecardsuems'),
                'field' => 'filterlevel',
            ],
            'course' => [
                'label' => get_string('filtercourse', 'block_coursecardsuems'),
                'field' => 'filtercourse',
            ],
            'group' => [
                'label' => get_string('filtergroup', 'block_coursecardsuems'),
                'field' => 'filtergroup',
            ],
            'offer' => [
                'label' => get_string('filteroffer', 'block_coursecardsuems'),
                'field' => 'filteroffer',
            ],
        ];

        $filters = [];
        foreach ($definitions as $key => $definition) {
            $values = [];
            foreach ($this->courses as $course) {
                $value = trim((string) ($course[$definition['field']] ?? ''));
                if ($value !== '') {
                    $values[$value] = $value;
                }
            }

            if (count($values) <= 1) {
                continue;
            }

            natcasesort($values);
            $options = [[
                'value' => '',
                'label' => get_string('filterall', 'block_coursecardsuems'),
                'selected' => true,
            ]];
            foreach ($values as $value) {
                $options[] = [
                    'value' => $value,
                    'label' => $value,
                    'selected' => false,
                ];
            }

            $filters[] = [
                'key' => $key,
                'label' => $definition['label'],
                'options' => $options,
            ];
        }

        return $filters;
    }

    /**
     * Returns whether dynamic filters should be shown for the selected perspective.
     *
     * @return bool
     */
    private function should_show_filters(): bool {
        $active = null;
        foreach ($this->perspectives as $perspective) {
            if (!empty($perspective['isactive'])) {
                $active = $perspective;
                break;
            }
        }

        if ($active === null) {
            foreach ($this->perspectives as $perspective) {
                if (!empty($perspective['isdefault'])) {
                    $active = $perspective;
                    break;
                }
            }
        }

        if ($active === null) {
            return false;
        }

        return in_array($active['key'], [
            \block_coursecardsuems\local\user_perspective_resolver::TUTOR,
            \block_coursecardsuems\local\user_perspective_resolver::TEACHER,
            \block_coursecardsuems\local\user_perspective_resolver::ADMIN,
        ], true);
    }

    /**
     * Groups course view models into product sections.
     *
     * @return array Section data for Mustache.
     */
    private function get_sections(): array {
        $definitions = [
            course_status_resolver::OPEN => [
                'title' => get_string('openplural', 'block_coursecardsuems'),
                'isopen' => true,
                'layout' => 'grid',
            ],
            course_status_resolver::COMINGSOON => [
                'title' => get_string('comingsoonplural', 'block_coursecardsuems'),
                'isopen' => false,
                'layout' => 'list',
            ],
            course_status_resolver::CLOSED => [
                'title' => get_string('closedplural', 'block_coursecardsuems'),
                'isopen' => false,
                'layout' => 'list',
            ],
            course_status_resolver::NODATE => [
                'title' => get_string('nodateplural', 'block_coursecardsuems'),
                'isopen' => false,
                'layout' => 'list',
                'hidewhenempty' => true,
            ],
        ];

        $grouped = array_fill_keys(array_keys($definitions), []);
        foreach ($this->courses as $course) {
            $status = $course['status'] ?? course_status_resolver::OPEN;
            if (!array_key_exists($status, $grouped)) {
                $status = course_status_resolver::OPEN;
            }
            $grouped[$status][] = $course;
        }

        $sections = [];
        foreach ($definitions as $status => $definition) {
            $courses = $grouped[$status];
            if (!empty($definition['hidewhenempty']) && empty($courses)) {
                continue;
            }

            $sections[] = [
                'key' => $status,
                'title' => $definition['title'],
                'isopen' => $definition['isopen'],
                'layout' => $definition['layout'],
                'isgrid' => $definition['layout'] === 'grid',
                'islist' => $definition['layout'] === 'list',
                'courses' => $courses,
                'hascourses' => !empty($courses),
                'count' => count($courses),
                'emptytext' => get_string('nocoursesinsection', 'block_coursecardsuems'),
            ];
        }

        return $sections;
    }
}
