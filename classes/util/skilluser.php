<?php

/**
 * Skill util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

defined('MOODLE_INTERNAL') || die;

class skilluser {
    public function find($skillid, $userid) {
        global $DB;

        return $DB->get_record('ultimate_skills_users', ['skillid' => $skillid, 'userid' => $userid]);
    }

    public function save($skillid, $userid, $value) {
        global $DB;

        $data = new \stdClass();
        $data->skillid = $skillid;
        $data->userid = $userid;
        $data->value = $value;
        $data->timecreated = time();
        $data->timemodified = time();

        $id = $DB->insert_record('ultimate_skills_users', $data);
        $data->id = $id;

        return $data;
    }

    public function update($data, $value) {
        global $DB;

        $data->value += $value;
        $data->timemodified = time();

        $DB->update_record('ultimate_skills_users', $data);

        return $data;
    }

    public function add_skills($courseid, $cmid, $userid) {
        $skillmoduleutil = new skillmodule();
        $skillsmodule = $skillmoduleutil->get_module_skills($cmid);

        if (!$skillsmodule) {
            return false;
        }

        foreach ($skillsmodule as $skillmodule) {
            if ($skilluser = $this->find($skillmodule->skillid, $userid)) {
                $eventdata = $this->update($skilluser, $skillmodule->value);
            } else {
                $eventdata = $this->save($skillmodule->skillid, $userid, $skillmodule->value);
            }

            $this->trigger_skills_added_event($courseid, $eventdata->id, $userid);
        }
    }

    private function trigger_skills_added_event($courseid, $skilluserid, $userid) {
        $context = \context_course::instance($courseid);

        $params = array(
            'context' => $context,
            'objectid' => $skilluserid,
            'courseid' => $courseid,
            'relateduserid' => $userid
        );

        $event = \local_ultimate\event\skills_added::create($params);
        $event->trigger();
    }

    public function get_user_skills_points($userid, $skillsids) {
        global $DB;

        list($insql, $params) = $DB->get_in_or_equal($skillsids, SQL_PARAMS_NAMED);

        $sql = 'SELECT skillid as id, value
                FROM {ultimate_skills_users}
                WHERE userid = :userid AND skillid ' . $insql;

        $params['userid'] = $userid;

        return $DB->get_records_sql($sql, $params);
    }
}
