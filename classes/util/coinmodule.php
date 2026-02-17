<?php

/**
 * Coin module util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

defined('MOODLE_INTERNAL') || die;

class coinmodule {
    public function get_module_coins($cmid) {
        global $DB;

        if ($dbcoins = $DB->get_record('ultimate_coins_modules', ['cmid' => $cmid])) {
            return $dbcoins->value;
        }

        return false;
    }

    public function sync_module_coins($cmid, $coins = null) {
        global $DB;

        if (!$coins) {
            return $DB->delete_records('ultimate_coins_modules', ['cmid' => $cmid]);
        }

        if ($dbcoins = $DB->get_record('ultimate_coins_modules', ['cmid' => $cmid])) {
            $dbcoins->value = $coins;
            $dbcoins->timemodified = time();

            return $DB->update_record('ultimate_coins_modules', $dbcoins);
        }

        $data = new \stdClass();
        $data->cmid = $cmid;
        $data->value = $coins;
        $data->timecreated = time();
        $data->timemodified = time();

        return $DB->insert_record('ultimate_coins_modules', $data);
    }
}
