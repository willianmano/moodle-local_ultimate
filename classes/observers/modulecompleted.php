<?php

/**
 * Event listener for course module completion event
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\observers;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;
use local_ultimate\util\coin;
use local_ultimate\util\coinledger;
use local_ultimate\util\point;
use local_ultimate\util\skilluser;

class modulecompleted {
    public static function observer(baseevent $event) {
        if (!self::is_completion_completed($event->objectid)) {
            return;
        }

        $cmid = $event->contextinstanceid;
        $userid = $event->relateduserid;

        if (!is_enrolled($event->get_context(), $userid)) {
            return;
        }

        // Avoid add points for teachers, admins, anyone who can edit course.
        if (has_capability('moodle/course:update', $event->get_context(), $userid)) {
            return;
        }

        $skilluserutil = new skilluser();
        $skilluserutil->add_skills($event->courseid, $cmid, $userid);

        $coinsutil = new coin();
        $coinsadded = $coinsutil->add_coins($event->courseid, $cmid, $userid);

        $pointutil = new point();
        $pointutil->add_points($userid, $coinsadded);

        if ($coinsadded) {
            $ledger = new coinledger();
            $ledger->add_entry(
                $event->courseid,
                $userid,
                'module',
                $event->target,
                $event->contextinstanceid,
                $coinsadded,
                'in'
            );
        }
    }

    /**
     * Verify if the completion is completed
     *
     * @param int $cmcid
     *
     * @return boolean
     */
    protected static function is_completion_completed($cmcid) {
        global $DB;

        $cmc = $DB->get_record('course_modules_completion', ['id' => $cmcid], '*');

        if ($cmc) {
            return (bool) $cmc->completionstate;
        }

        return false;
    }
}
