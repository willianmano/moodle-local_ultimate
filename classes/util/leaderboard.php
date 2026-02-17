<?php

namespace local_ultimate\util;

class leaderboard {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function get_leaderboard($limitnum = 10) {
        global $DB;

        $userfieldsapi = \core_user\fields::for_userpic();
        $userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

        $sql = "SELECT $userfields, points.points as coins
                FROM {user} u
                INNER JOIN {ultimate_points} points ON u.id = points.userid
                ORDER BY coins DESC, u.firstname ASC";

        $records = $DB->get_records_sql($sql, null, 0, $limitnum);

        if (!$records) {
            return false;
        }

        $records = array_values($records);

        $this->fill_with_userinfo($records);

        $this->fill_with_position($records);

        return $records;
    }

    public function get_leaderboard_by_period($datestart, $dateend, $limitnum = 10, $courseid = null) {
        global $DB;

        $userfieldsapi = \core_user\fields::for_userpic();
        $userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

        $sql = "SELECT DISTINCT $userfields, SUM(coins.coins) as coins
                FROM {ultimate_coins_ledger} coins
                INNER JOIN {user} u ON u.id = coins.userid
                WHERE coins.action = 'in' AND coins.timecreated >= :datestart AND coins.timecreated <= :dateend";

        $params = [
            'datestart' => $datestart,
            'dateend' => $dateend,
        ];

        if ($courseid) {
            $sql .= " AND coins.courseid = :courseid";
            $params['courseid'] = $courseid;
        }

        $sql .= " GROUP BY " . $userfields . " ORDER BY coins DESC, u.firstname ASC LIMIT " . $limitnum;

        $records = $DB->get_records_sql($sql, $params);

        if (!$records) {
            return false;
        }

        $records = array_values($records);

        $this->fill_with_userinfo($records);

        $this->fill_with_position($records);

        return $records;
    }

    private function fill_with_userinfo($data) {
        foreach ($data as $user) {
            $userpicture = new user();
            $user->userpicture = $userpicture->get_user_avatar_or_image($user);
            $user->coins = (int) $user->coins;
        }


//        echo "<pre>";print_r($data);exit;
    }

    /**
     * Get the users position data.
     *
     * @param array $data
     *
     * @return string|array
     *
     * @throws \coding_exception
     */
    protected function fill_with_position($data) {
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]->position = $i + 1;
        }
    }
}