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
 * Category parser.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursecardsuems\local;

defined('MOODLE_INTERNAL') || die();

use core_course_category;
use core_text;
use moodle_exception;

/**
 * Reads domain signals from Moodle's course category tree.
 */
class category_parser {

    /** @var array Cache of distance checks by category id. */
    private $distancecache = [];

    /** @var array Cache of filter metadata by category id. */
    private $filtermetadatacache = [];

    /**
     * Returns whether a category belongs to the EaD branch.
     *
     * A category is considered EaD when its own category path contains a category named
     * "Distância". Missing categories are treated as non-EaD.
     *
     * @param int $categoryid Course category id.
     * @return bool
     */
    public function is_distance_category($categoryid): bool {
        $categoryid = (int) $categoryid;
        if (array_key_exists($categoryid, $this->distancecache)) {
            return $this->distancecache[$categoryid];
        }

        $isdistance = false;

        try {
            $category = core_course_category::get($categoryid, IGNORE_MISSING, true);
            if (!$category) {
                $this->distancecache[$categoryid] = false;
                return false;
            }

            $pathids = array_values(array_filter(explode('/', trim($category->path, '/'))));
            foreach ($pathids as $pathid) {
                $pathcategory = core_course_category::get((int) $pathid, IGNORE_MISSING, true);
                if (!$pathcategory) {
                    continue;
                }

                $name = core_text::strtolower(trim(strip_tags($pathcategory->get_formatted_name())));
                if ($name === 'distância') {
                    $isdistance = true;
                    break;
                }
            }
        } catch (moodle_exception $exception) {
            $isdistance = false;
        }

        $this->distancecache[$categoryid] = $isdistance;
        return $isdistance;
    }

    /**
     * Returns category metadata used by dynamic filters.
     *
     * In the EaD category layout:
     * - level is the category immediately above "Distância";
     * - course is the first category below "Distância";
     * - group is the category below course whose name starts with "Turma".
     *
     * @param int $categoryid Course category id.
     * @return array{level:string,course:string,group:string}
     */
    public function get_filter_metadata($categoryid): array {
        $categoryid = (int) $categoryid;
        if (array_key_exists($categoryid, $this->filtermetadatacache)) {
            return $this->filtermetadatacache[$categoryid];
        }

        $metadata = [
            'level' => '',
            'course' => '',
            'group' => '',
        ];

        try {
            $category = core_course_category::get($categoryid, IGNORE_MISSING, true);
            if (!$category) {
                $this->filtermetadatacache[$categoryid] = $metadata;
                return $metadata;
            }

            $pathids = array_values(array_filter(explode('/', trim($category->path, '/'))));
            $pathnames = [];
            foreach ($pathids as $pathid) {
                $pathcategory = core_course_category::get((int) $pathid, IGNORE_MISSING, true);
                if (!$pathcategory) {
                    continue;
                }
                $pathnames[] = trim(strip_tags($pathcategory->get_formatted_name()));
            }

            foreach ($pathnames as $index => $name) {
                if (core_text::strtolower($name) !== 'distância') {
                    continue;
                }

                $metadata['level'] = $pathnames[$index - 1] ?? '';
                $metadata['course'] = $pathnames[$index + 1] ?? '';
                for ($i = $index + 2; $i < count($pathnames); $i++) {
                    if (preg_match('/^turma\b/iu', $pathnames[$i]) === 1) {
                        $metadata['group'] = $pathnames[$i];
                        break;
                    }
                }
                break;
            }
        } catch (moodle_exception $exception) {
            $metadata = [
                'level' => '',
                'course' => '',
                'group' => '',
            ];
        }

        $this->filtermetadatacache[$categoryid] = $metadata;
        return $metadata;
    }

    /**
     * Returns the formatted Série name for a course category.
     *
     * In the current category layout, the course category itself is the Série.
     *
     * @param int $categoryid Course category id.
     * @return string Series name or empty string when unavailable.
     */
    public function get_series_name($categoryid): string {
        try {
            $category = core_course_category::get((int) $categoryid, IGNORE_MISSING, true);
            if (!$category) {
                return '';
            }

            return trim(strip_tags($category->get_formatted_name()));
        } catch (moodle_exception $exception) {
            return '';
        }
    }
}
