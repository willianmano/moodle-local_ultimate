<?php

/**
 * Badges util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

defined('MOODLE_INTERNAL') || die;

use moodle_url;

class badge {
    public function __construct() {
        global $CFG;

        require_once($CFG->libdir . '/badgeslib.php');
    }

    public function get_awarded_course_badges($userid, $courseid, $contextid) {
        $badges = $this->get_course_badges_with_user_award($userid, $courseid, $contextid);

        if (!$badges) {
            return false;
        }

        $mybadges = [];
        foreach ($badges as $badge) {
            if ($badge['awarded']) {
                $mybadges[] = $badge;
            }
        }

        return $mybadges;
    }

    public function get_course_badges_with_user_award($userid, $courseid, $contextid) {
        $coursebadges = $this->get_course_badges($courseid);

        if (!$coursebadges) {
            return false;
        }

        $badges = [];
        foreach ($coursebadges as $coursebadge) {
            $badges[] = [
                'id' => $coursebadge->id,
                'badgeid' => $coursebadge->badgeid,
                'name' => $coursebadge->name,
                'description' => $coursebadge->description,
                'badgeimage' => $this->get_badge_image_url($contextid, $coursebadge->badgeid),
                'awarded' => false
            ];
        }

        $userbadges = $this->get_user_course_badges($userid, $courseid);

        if (!$userbadges) {
            return $badges;
        }

        foreach ($badges as $key => $badge) {
            foreach ($userbadges as $userbadge) {
                if ($badge['badgeid'] == $userbadge->id) {
                    $badges[$key]['awarded'] = true;
                    continue 2;
                }
            }
        }

        return $badges;
    }

    public function get_course_badges($courseid) {
        global $DB;

        $sql = 'SELECT eb.*, b.description
                FROM {ultimate_badges} eb
                INNER JOIN {badge} b ON b.id = eb.badgeid
                WHERE b.courseid = :courseid';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }

    public function get_user_course_badges($userid, $courseid) {
        // Get badges from badgelib.
        $userbadges = badges_get_user_badges($userid, $courseid);

        if ($userbadges) {
            return $userbadges;
        }

        return false;
    }

    public function get_badge_image_url($contextid, $badgeid) {
        $imageurl = moodle_url::make_pluginfile_url($contextid, 'badges', 'badgeimage', $badgeid, '/', 'f1', false);

        $imageurl->param('refresh', rand(1, 10000));

        return $imageurl;
    }

    public function get_local_badges($courseid) {
        global $DB;

        $sql = 'SELECT gcb.id, gcb.name, gcb.badgeid, b.description
                FROM {ultimate_badges} gcb
                INNER JOIN {badge} b ON b.id = gcb.badgeid
                WHERE gcb.courseid = :courseid';

        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }

    public function get_user_course_badges_with_criterias($userid, $courseid, $contextid) {
        $badgecriteria = new badgecriteria();

        $badges = $this->get_course_badges_with_user_award($userid, $courseid, $contextid);

        if (!$badges) {
            return [];
        }

        foreach ($badges as $key => $badge) {
            $badgecriterias = $badgecriteria->get_game_badge_criterias($badge['id']);

            if (!$badgecriterias) {
                unset($badges[$key]);

                continue;
            }

            $criteriasprogress = [];
            foreach ($badgecriterias as $criteria) {
                $criteriaclass = '\ultimatebadgecriteria_' . $criteria->method . '\badgecriteria';

                if (!class_exists($criteriaclass)) {
                    continue;
                }

                $criteriamethod = new $criteriaclass($userid, $courseid, $criteria);

                $criteriasprogress[] = $criteriamethod->get_user_criteria_progress_html();
            }

            $badges[$key]['criteriasprogress'] = $criteriasprogress;
        }

        return array_values($badges);
    }
}
