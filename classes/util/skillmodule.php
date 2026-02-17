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

class skillmodule {
    public function save($cmid, $skillid, $value) {
        global $DB;

        $data = new \stdClass();
        $data->cmid = $cmid;
        $data->skillid = $skillid;
        $data->value = $value;
        $data->timecreated = time();
        $data->timemodified = time();

        return $DB->insert_record('ultimate_skills_modules', $data);
    }

    public function sync_module_skills($cmid, $skills = []) {
        global $DB;

        if (empty($skills)) {
            return $DB->delete_records('ultimate_skills_modules', ['cmid' => $cmid]);
        }

        $dbpoints = $DB->get_records('ultimate_skills_modules', ['cmid' => $cmid]);

        foreach ($skills as $skillid => $value) {
            if (!$value) {
                continue;
            }

            if (!$dbpoints) {
                $this->save($cmid, $skillid, $value);

                continue;
            }

            foreach ($dbpoints as $key => $dbpoint) {
                if ($dbpoint->skillid == $skillid) {
                    if ($dbpoint->value != $value) {
                        $dbpoint->value = $value;
                        $dbpoint->timemodified = time();

                        $DB->update_record('ultimate_skills_modules', $dbpoint);
                    }

                    unset($dbpoints[$key]);

                    continue 2;
                }
            }

            $this->save($cmid, $skillid, $value);
        }

        if ($dbpoints) {
            foreach ($dbpoints as $dbpoint) {
                $DB->delete_records('ultimate_skills_modules', ['id' => $dbpoint->id]);
            }
        }
    }

    public function get_module_skills($cmid) {
        global $DB;

        if ($skills = $DB->get_records('ultimate_skills_modules', ['cmid' => $cmid])) {
            return array_values($skills);
        }

        return false;
    }

    public function get_skills_points_sum($skillsids) {
        global $DB;

        list($insql, $inparams) = $DB->get_in_or_equal($skillsids);

        $sql = 'SELECT skillid, SUM(value) as value FROM {ultimate_skills_modules} WHERE skillid '. $insql .' GROUP BY skillid';

        return $DB->get_records_sql($sql, $inparams);
    }
}
