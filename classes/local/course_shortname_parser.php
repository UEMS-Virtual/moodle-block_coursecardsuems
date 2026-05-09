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
 * Course shortname parser.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Reads discipline signals from Moodle course shortnames.
 */
class course_shortname_parser {

    /**
     * Returns whether a shortname follows the UEMS discipline pattern.
     *
     * Examples accepted: `CISOL_23_2S_EP_df970`, `PEDG_24_2S_D_(REO)_d74cd`.
     *
     * @param string $shortname Moodle course shortname.
     * @return bool
     */
    public function is_discipline_shortname(string $shortname): bool {
        return preg_match('/^[A-Z]+_\d{2}_\d+S_[A-Z0-9]+(?:_\(REO\d?\))?_/i', $shortname) === 1;
    }
}
