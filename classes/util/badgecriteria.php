<?php

/**
 * Badge criteria util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

defined('MOODLE_INTERNAL') || die;

class badgecriteria {
    public function get_game_badge_criterias($badgeid) {
        global $DB;

        $records = $DB->get_records('ultimate_badges_criterias', ['badgeid' => $badgeid]);

        if (!$records) {
            return false;
        }

        return array_values($records);
    }

    public function get_game_badge_criterias_with_skill_name($badge) {
        global $DB;

        $criterias = $this->get_game_badge_criterias($badge->id);

        if (!$criterias) {
            return false;
        }

        $courseskills = $DB->get_records('ultimate_skills', ['courseid' => $badge->courseid]);

        foreach ($criterias as $criteria) {
            if ($criteria->method == 'skillpoints') {
                $criteria->target = $courseskills[$criteria->target]->name;
            }

            if ($criteria->method == 'skillpointsaggregation') {
                $skills = explode(',', $criteria->target);

                $skillnames = array_map(function($skillid) use ($courseskills) {
                    return $courseskills[$skillid]->name;
                }, $skills);

                $criteria->target = implode(', ', $skillnames);
            }
        }

        return array_values($criterias);
    }

}
