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
 * Renderer for the Course Cards UEMS block.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Block renderer.
 */
class renderer extends plugin_renderer_base {

    /**
     * Renders the reconstruction summary.
     *
     * @param summary $summary Summary renderable.
     * @return string HTML.
     */
    public function render_summary(summary $summary) {
        return $this->render_from_template('block_coursecardsuems/summary', $summary->export_for_template($this));
    }
}
