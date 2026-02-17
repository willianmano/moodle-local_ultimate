<?php

namespace local_ultimate\output;

use local_ultimate\util\badge;
use local_ultimate\util\coin;
use local_ultimate\util\leaderboard;
use local_ultimate\util\level;
use local_ultimate\util\point;
use local_ultimate\util\user;
use renderable;
use templatable;
use renderer_base;

class block implements renderable, templatable {
    protected $course;
    protected $context;
    protected $config;

    public function __construct($course, $context, $config) {
        $this->course = $course;
        $this->context = $context;
        $this->config = $config;
    }

    public function export_for_template(renderer_base $output) {
        global $USER;

        $userutil = new user();
        $coinutil = new coin();
        $pointutil = new point();

        $data = [
            'contextid' => $this->context->id,
            'course' => $this->course->id,
            'firstname' => $USER->firstname,
            'avatar' => $userutil->get_user_avatar_or_image($USER),
            'coins' => $coinutil->get_user_coins($USER->id),
            'points' => $pointutil->get_user_points($USER->id),
        ];

        if (isset($this->config['showxp']) && $this->config['showxp'] == true) {
            $pointsdata = $pointutil->get_data($USER->id);

            $level = 1;
            $points = 0;
            if ($pointsdata) {
                $level = $pointsdata->level;
                $points = $pointsdata->points;
            }

            $levelutil = new level();
            $nextlevelpoints = $levelutil->get_next_level_points($level);

            $progress = (int)(($points / $nextlevelpoints) * 100);

            $data += [
                'showxp' => true,
                'level' => $level,
                'points' => $points,
                'nextlevelpoints' => $nextlevelpoints,
                'progress' => $progress,
            ];
        }

        if (isset($this->config['showbadges']) && $this->config['showbadges'] == true) {
            $badgeutil = new badge();
            $badges = $badgeutil->get_user_course_badges_with_criterias($USER->id, $this->course->id, $this->context->id);

            $data += [
                'badges' => $badges,
                'showbadges' => !empty($badges),
            ];
        }

        if (isset($this->config['showleaderboard']) && $this->config['showleaderboard'] == true) {
            $weekstart = strtotime(date('d-m-Y', strtotime('-'.date('w').' days')));
            $monthstart = strtotime(date('Y-m-01'));

            $leaderboardutil = new leaderboard($this->course, $this->context);

            $data += [
                'showleaderboard' => true,
                'leaderboard_week' => ['data' => $leaderboardutil->get_leaderboard_by_period($weekstart, time(), 10, $this->course->id)],
                'leaderboard_month' => ['data' => $leaderboardutil->get_leaderboard_by_period($monthstart, time(), 10, $this->course->id)],
                'leaderboard_general' => ['data' => $leaderboardutil->get_leaderboard()],
            ];
        }

        return $data;
    }
}
