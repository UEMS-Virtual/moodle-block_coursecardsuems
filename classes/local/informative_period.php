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
 * Informative period value object.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Período informativo da disciplina.
 */
class informative_period {

    /** @var int Start timestamp from ead_inicio. */
    public $startdate;

    /** @var int End timestamp from ead_final. */
    public $enddate;

    /**
     * Constructor.
     *
     * @param int $startdate Start timestamp.
     * @param int $enddate End timestamp.
     */
    public function __construct(int $startdate = 0, int $enddate = 0) {
        $this->startdate = $startdate;
        $this->enddate = $enddate;
    }

    /**
     * Returns whether at least one custom date exists.
     *
     * @return bool
     */
    public function has_any_date(): bool {
        return !empty($this->startdate) || !empty($this->enddate);
    }

    /**
     * Returns whether both custom dates exist.
     *
     * @return bool
     */
    public function has_complete_range(): bool {
        return !empty($this->startdate) && !empty($this->enddate);
    }
}
