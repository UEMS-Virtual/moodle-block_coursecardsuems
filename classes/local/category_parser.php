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
}
