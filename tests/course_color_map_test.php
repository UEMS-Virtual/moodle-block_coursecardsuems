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
 * Tests for configured course stripe colors.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_color_map_test extends \advanced_testcase {

    /**
     * Configured colors are found using normalized compact group keys.
     */
    public function test_returns_color_for_normalized_group_key(): void {
        $map = new course_color_map('{"PEDG24":"#ec407a","CISOL23":"#ff8c00"}');

        self::assertSame('#ec407a', $map->get_color('PEDG-24', false));
        self::assertSame('#ff8c00', $map->get_color('cisol_23', false));
    }

    /**
     * Reoferta courses prefer the REO color and fall back to the base course color.
     */
    public function test_reoferta_prefers_reo_key_and_falls_back_to_base_key(): void {
        $map = new course_color_map('{"PEDG24":"#ec407a","PEDG24-REO":"#f8bbd0","TGP24":"#9bb287"}');

        self::assertSame('#f8bbd0', $map->get_color('PEDG-24', true));
        self::assertSame('#9bb287', $map->get_color('TGP-24', true));
    }

    /**
     * REO2 configuration keys are treated as the generic REO key.
     */
    public function test_reo2_keys_are_normalized_to_reo(): void {
        $map = new course_color_map('{"PEDG24-REO2":"#f7b0c9"}');

        self::assertSame('#f7b0c9', $map->get_color('PEDG-24', true));
    }

    /**
     * Invalid JSON, invalid colors and missing keys return null so CSS can use fallback blue.
     */
    public function test_invalid_or_missing_configuration_returns_null(): void {
        self::assertNull((new course_color_map('{invalid json'))->get_color('PEDG-24', false));
        self::assertNull((new course_color_map('{"PEDG24":"red"}'))->get_color('PEDG-24', false));
        self::assertNull((new course_color_map('{"ADMP21":"#8db2ff"}'))->get_color('PEDG-24', false));
    }
}
