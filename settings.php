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
 *  Local ultimate config form definition class
 *
 * @package     local_ultimate
 * @copyright   2026 Willian Mano {@link https://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_ultimate', 'Ultimate');

    // Options controlling level up.
    $leveloptions = [];
    for ($i = 1; $i <= 15; $i++) {
        $leveloptions[$i] = $i;
    }

    $settings->add(new admin_setting_configselect(
        'local_ultimate/level_number',
        get_string('config_level_number', 'local_ultimate'),
        '',
        15,
        $leveloptions
    ));

    $leveluppoints = [2 => 300, 3 => 500, 4 => 1000, 5 => 2000,
        6 => 4000, 7 => 6000, 8 => 10000, 9 => 20000,
        10 => 30000, 11 => 50000, 12 => 70000, 13 => 100000, 14 => 150000, 15 => 250000];
    for ($i = 2; $i <= 15; $i++) {
        $settings->add(new admin_setting_configtext(
            'local_ultimate/level_up' . $i,
            get_string('config_level_up' . $i, 'local_ultimate'),
            '',
            $leveluppoints[$i],
            PARAM_INT
        ));
    }

    $ADMIN->add('localplugins', $settings);
}