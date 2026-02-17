<?php

/**
 * Coin util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

class point {
    public function get_data($userid = null)
    {
        global $DB, $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        return $DB->get_record('ultimate_points', ['userid' => $userid]);
    }

    public function get_user_points($userid = null) {
        $userpoints = $this->get_data($userid);

        if (!$userpoints) {
            return 0;
        }

        return (int) $userpoints->points;
    }

    public function add_points($userid, $points) {
        global $DB;

        if ($dbpoints = $DB->get_record('ultimate_points', ['userid' => $userid])) {
            $dbpoints->points += $points;
            $dbpoints->timemodified = time();

            $levelutil = new level();
            $updatedlevel = $levelutil->calculate_level($dbpoints);

            if ($updatedlevel != $dbpoints->level) {
                $dbpoints->level = $updatedlevel;
            }

            $DB->update_record('ultimate_points', $dbpoints);

            return $dbpoints;
        }

        $data = new \stdClass();
        $data->userid = $userid;
        $data->points = $points;
        $data->level = 1;
        $data->timecreated = time();
        $data->timemodified = time();

        $id = $DB->insert_record('ultimate_points', $data);

        $data->id = $id;

        return $data;
    }
}
