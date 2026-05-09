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

namespace block_coursecardsuems\local;

/**
 * Tests for the course shortname parser.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_shortname_parser_test extends \advanced_testcase {

    /**
     * Known UEMS discipline shortname shapes are accepted.
     *
     * @dataProvider valid_shortname_provider
     * @param string $shortname Shortname.
     */
    public function test_accepts_known_discipline_shortname_shapes(string $shortname): void {
        self::assertTrue((new course_shortname_parser())->is_discipline_shortname($shortname));
    }

    /**
     * Unknown shortname shapes are not treated as Disciplina EaD.
     */
    public function test_rejects_unknown_shortname_shape(): void {
        self::assertFalse((new course_shortname_parser())->is_discipline_shortname('teste'));
    }

    /**
     * Valid shortname examples.
     *
     * @return array
     */
    public static function valid_shortname_provider(): array {
        return [
            ['CISOL_23_2S_EP_df970'],
            ['PEDG_24_2S_D_(REO)_d74cd'],
            ['CISOL_20_4S_TEA_(REO2)_f1c8e'],
        ];
    }
}
