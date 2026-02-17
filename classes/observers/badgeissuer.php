<?php

/**
 * Event listener for dispatched event
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\observers;

defined('MOODLE_INTERNAL') || die;

use core\event\base as baseevent;

class badgeissuer {
    public static function observer(baseevent $event) {
        global $DB;

        if (!is_enrolled($event->get_context(), $event->relateduserid)) {
            return;
        }

        // Avoid add points for teachers, admins, anyone who can edit course.
        if (has_capability('moodle/course:update', $event->get_context(), $event->relateduserid)) {
            return;
        }

        $userid = $event->relateduserid;

        if (get_class($event) === 'core\event\course_viewed') {
            $userid = $event->userid;
        }

        $gamebadges = $DB->get_records('ultimate_badges', ['courseid' => $event->courseid]);

        if (!$gamebadges) {
            return;
        }

        foreach ($gamebadges as $gamebadge) {
            if (self::user_already_have_badge($userid, $gamebadge->badgeid)) {
                continue;
            }

            $badgecriterias = $DB->get_records('ultimate_badges_criterias', ['badgeid' => $gamebadge->id]);

            if (!$badgecriterias) {
                continue;
            }

            if (self::check_if_user_can_receive_badge($userid, $event->courseid, $badgecriterias)) {
                self::deliver_badge($userid, $gamebadge);
            }
        }
    }

    public static function user_already_have_badge($userid, $badgerid) {
        $badge = new \core_badges\badge($badgerid);

        return $badge->is_issued($userid);
    }

    public static function check_if_user_can_receive_badge($userid, $courseid, $badgecriterias): bool {
        foreach ($badgecriterias as $badgecriteria) {
            $criteriaclass = '\ultimatebadgecriteria_' . $badgecriteria->method . '\badgecriteria';

            if (!class_exists($criteriaclass)) {
                return false;
            }

            $criteria = new $criteriaclass($userid, $courseid, $badgecriteria);

            if (!$criteria->user_achieved_criteria()) {
                return false;
            }
        }

        return true;
    }

    public static function deliver_badge($userid, $gamebadge) {
        global $CFG;

        require_once($CFG->libdir . '/badgeslib.php');
        require_once($CFG->dirroot . '/badges/lib/awardlib.php');

        // Admin userid.
        $issuerid = 2;
        // Admin role.
        $issuerrole = 3;

        $badge = new \core_badges\badge($gamebadge->badgeid);

        $badgeadded = process_manual_award($userid, $issuerid, $issuerrole, $gamebadge->badgeid);

        if ($badgeadded) {
            $badge->issue($userid);

            $notification = new \local_ultimate\notification\badge($userid);

            $notification->notify($gamebadge->id);
        }
    }
}
