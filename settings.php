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
 * Admin settings for the Course Cards UEMS block.
 *
 * @package    block_coursecardsuems
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtextarea(
        'block_coursecardsuems/coursecolormap',
        get_string('coursecolormap', 'block_coursecardsuems'),
        get_string('coursecolormap_desc', 'block_coursecardsuems'),
        '',
        PARAM_RAW,
        60,
        16
    ));

    $settings->add(new admin_setting_configcheckbox(
        'block_coursecardsuems/customsemesterenabled',
        get_string('customsemesterenabled', 'block_coursecardsuems'),
        get_string('customsemesterenabled_desc', 'block_coursecardsuems'),
        0
    ));

    $settings->add(new admin_setting_configtext(
        'block_coursecardsuems/customsemesterlabel',
        get_string('customsemesterlabel', 'block_coursecardsuems'),
        get_string('customsemesterlabel_desc', 'block_coursecardsuems'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'block_coursecardsuems/customsemesterstart',
        get_string('customsemesterstart', 'block_coursecardsuems'),
        get_string('customsemesterstart_desc', 'block_coursecardsuems'),
        '',
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new admin_setting_configtext(
        'block_coursecardsuems/customsemesterend',
        get_string('customsemesterend', 'block_coursecardsuems'),
        get_string('customsemesterend_desc', 'block_coursecardsuems'),
        '',
        PARAM_ALPHANUMEXT
    ));
}
