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

namespace block_coursecardsuems\external;

/**
 * Tests for the course inclusion diagnostic external function.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_course_diagnostics_test extends \externallib_advanced_testcase {

    /**
     * Users without the diagnostic capability cannot inspect course eligibility.
     */
    public function test_rejects_user_without_diagnostic_capability(): void {
        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $this->setUser(self::getDataGenerator()->create_user());

        $this->expectException(\required_capability_exception::class);
        get_course_diagnostics::execute((int) $course->id);
    }
}
