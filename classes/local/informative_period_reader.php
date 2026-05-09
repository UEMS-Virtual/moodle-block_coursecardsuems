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
 * Informative period reader.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

use core_course\customfield\course_handler;
use moodle_exception;

/**
 * Reads ead_inicio and ead_final from Moodle course custom fields.
 */
class informative_period_reader {

    /**
     * Returns the Período informativo da disciplina for a course.
     *
     * Moodle native start/end dates are intentionally not read here. They represent the
     * Janela de acesso Moodle and may only be used by later status fallback rules.
     *
     * @param int $courseid Course id.
     * @return informative_period
     */
    public function get_period(int $courseid): informative_period {
        $startdate = 0;
        $enddate = 0;

        try {
            $handler = course_handler::create();
            $data = $handler->get_instance_data($courseid, true);
        } catch (moodle_exception $exception) {
            return new informative_period();
        }

        foreach ($data as $fielddata) {
            $shortname = $fielddata->get_field()->get('shortname');
            if ($shortname === 'ead_inicio') {
                $startdate = (int) $fielddata->get_value();
                continue;
            }
            if ($shortname === 'ead_final') {
                $enddate = (int) $fielddata->get_value();
            }
        }

        return new informative_period($startdate, $enddate);
    }
}
