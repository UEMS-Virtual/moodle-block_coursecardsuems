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
 * Course color map.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Parses configured stripe colors for course groups.
 */
class course_color_map {

    /** Setting name used by the block configuration. */
    public const CONFIG_NAME = 'coursecolormap';

    /** @var array Normalized key to validated hex color. */
    private $colors = [];

    /**
     * Constructor.
     *
     * @param string $json JSON object mapping course keys to colors.
     */
    public function __construct(string $json = '') {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return;
        }

        foreach ($decoded as $key => $color) {
            if (!is_string($key) || !is_string($color)) {
                continue;
            }

            $normalizedkey = $this->normalize_key($key);
            $normalizedcolor = $this->normalize_color($color);
            if ($normalizedkey === '' || $normalizedcolor === null) {
                continue;
            }

            $this->colors[$normalizedkey] = $normalizedcolor;
        }
    }

    /**
     * Builds the map from Moodle plugin configuration.
     *
     * @return self Configured color map.
     */
    public static function from_config(): self {
        return new self((string) get_config('block_coursecardsuems', self::CONFIG_NAME));
    }

    /**
     * Returns the configured color for a compact group.
     *
     * @param string $compactgroup Compact group, e.g. PEDG-24.
     * @param bool $isreoferta Whether the course is a reoferta, including REO2.
     * @return string|null Hex color or null when fallback should be used.
     */
    public function get_color(string $compactgroup, bool $isreoferta): ?string {
        $basekey = $this->normalize_key($compactgroup);
        if ($basekey === '') {
            return null;
        }

        if ($isreoferta && isset($this->colors[$basekey . '-REO'])) {
            return $this->colors[$basekey . '-REO'];
        }

        return $this->colors[$basekey] ?? null;
    }

    /**
     * Normalizes map keys so admin input can use PEDG24, PEDG-24 or PEDG_24.
     *
     * @param string $key Raw key.
     * @return string Normalized key.
     */
    private function normalize_key(string $key): string {
        $key = strtoupper(trim($key));
        $key = preg_replace('/[^A-Z0-9]/', '', $key);
        if ($key === null || $key === '') {
            return '';
        }

        if (preg_match('/^(.*)REO\d?$/', $key, $matches) === 1) {
            return $matches[1] . '-REO';
        }

        return $key;
    }

    /**
     * Validates and normalizes a configured color.
     *
     * @param string $color Raw color.
     * @return string|null Normalized hex color.
     */
    private function normalize_color(string $color): ?string {
        $color = trim($color);
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color) !== 1) {
            return null;
        }

        return strtolower($color);
    }
}
