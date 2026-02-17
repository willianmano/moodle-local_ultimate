<?php

/**
 * Coin util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

class coin {
    public function get_user_coins($userid = null) {
        global $DB, $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $usercoins = $DB->get_record('ultimate_coins', ['userid' => $userid]);

        if (!$usercoins) {
            return 0;
        }

        return (int) $usercoins->coins;
    }

    public function add_coins($courseid, $cmid, $userid) {
        global $DB;

        $coinmoduleutil = new coinmodule();
        if (!$modulecoins = $coinmoduleutil->get_module_coins($cmid)) {
            return false;
        }

        if ($dbcoins = $DB->get_record('ultimate_coins', ['userid' => $userid])) {
            $dbcoins->coins += $modulecoins;
            $dbcoins->timemodified = time();

            $DB->update_record('ultimate_coins', $dbcoins);

            $this->trigger_coins_added_event($courseid, $dbcoins->id, $userid, $modulecoins);

            return $modulecoins;
        }

        $data = new \stdClass();
        $data->userid = $userid;
        $data->coins = $modulecoins;
        $data->timecreated = time();
        $data->timemodified = time();

        $id = $DB->insert_record('ultimate_coins', $data);

        $this->trigger_coins_added_event($courseid, $id, $userid, $modulecoins);

        return $modulecoins;
    }

    public function decrease_coins($userid, $coins) {
        global $DB;

        if ($dbcoins = $DB->get_record('ultimate_coins', ['userid' => $userid])) {
            $dbcoins->coins -= $coins;
            $dbcoins->timemodified = time();

            $DB->update_record('ultimate_coins', $dbcoins);

            return $dbcoins;
        }

        return false;
    }

    private function trigger_coins_added_event($courseid, $coinid, $userid, $modulecoins) {
        $context = \context_course::instance($courseid);

        $params = array(
            'context' => $context,
            'objectid' => $coinid,
            'courseid' => $courseid,
            'relateduserid' => $userid,
            'other' => $modulecoins
        );

        $event = \local_ultimate\event\coins_added::create($params);
        $event->trigger();
    }
}
