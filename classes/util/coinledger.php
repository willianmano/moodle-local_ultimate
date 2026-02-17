<?php

/**
 * Coin ledger util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

defined('MOODLE_INTERNAL') || die;

class coinledger {
    public function add_entry($courseid, $userid, $source, $sourcetype, $sourceid, $coins, $action) {
        global $DB;

        if ($this->in_transaction_exists($courseid, $userid, $source, $sourcetype, $sourceid)) {
            return false;
        }

        $data = new \stdClass();
        $data->courseid = $courseid;
        $data->userid = $userid;
        $data->source = $source;
        $data->sourcetype = $sourcetype;
        $data->sourceid = $sourceid;
        $data->coins = $coins;
        $data->action = $action;
        $data->timecreated = time();

        $id = $DB->insert_record('ultimate_coins_ledger', $data);

        $data->id = $id;

        return $data;
    }

    private function in_transaction_exists($courseid, $userid, $source, $sourcetype, $sourceid) {
        global $DB;

        $record = $DB->get_record('ultimate_coins_ledger', [
            'courseid' => $courseid,
            'userid' => $userid,
            'source' => $source,
            'sourcetype' => $sourcetype,
            'sourceid' => $sourceid,
            'action' => 'in'
        ]);

        if ($record) {
            return true;
        }

        return false;
    }
}
