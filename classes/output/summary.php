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

use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Minimal renderable used by the reconstruction tracer bullet.
 */
class summary implements renderable, templatable {

    /** @var string Main title shown inside the block. */
    private $title;

    /** @var string Supporting message shown inside the block. */
    private $message;

    /**
     * Constructor.
     *
     * @param string $title Main title.
     * @param string $message Supporting message.
     */
    public function __construct($title, $message) {
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * Exports data for the Mustache template.
     *
     * @param renderer_base $output Renderer instance.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->title = $this->title;
        $data->message = $this->message;

        return $data;
    }
}
