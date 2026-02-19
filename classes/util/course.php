<?php

/**
 * Course util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

defined('MOODLE_INTERNAL') || die;

use moodle_url;

class course {
    /**
     * Returns all user enrolled courses with progress
     *
     * @param \stdClass $user
     *
     * @return array
     */
    public function get_user_courses_with_progress($user = null) {
        global $USER, $CFG;

        if (!$user) {
            $user = $USER;
        }

        require_once($CFG->dirroot.'/course/renderer.php');

        $chelper = new \coursecat_helper();

        $courses = enrol_get_users_courses($user->id, true, '*', 'visible DESC, fullname ASC, sortorder ASC');

        if (!$courses) {
            return false;
        }

        $data = [];
        foreach ($courses as $course) {
            $course->fullname = strip_tags($chelper->get_course_formatted_name($course));

            $courseobj = new \core_course_list_element($course);
            $completion = new \completion_info($course);

            $completed = false;
            $progress  = 0;

            // First, let's make sure completion is enabled.
            if ($completionenabled = $completion->is_enabled()) {
                $percentage = \core_completion\progress::get_course_progress_percentage($course, $user->id);

                if (!is_null($percentage)) {
                    $percentage = floor($percentage);
                }

                if (is_null($percentage)) {
                    $percentage = 0;
                }

                $completed = $completion->is_course_complete($user->id);
                $progress  = $percentage;
            }

            $data[] = [
                'id' => $course->id,
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
                'startdate' => $course->startdate,
                'completed' => $completed,
                'completionenabled' => $completionenabled,
                'courseprogress' => $progress,
                'hascourseprogress' => !empty($progress),
                'link' => new \moodle_url('/course/view.php', ['id' => $course->id]),
                'image' => $this->get_summary_image($courseobj)
            ];
        }

        return $data;
    }

    /**
     * Returns the first course's summary image url
     *
     * @return string
     */
    public function get_summary_image($course) {
        global $CFG, $OUTPUT;

        foreach ($course->get_course_overviewfiles() as $file) {
            if ($file->is_valid_image()) {
                $url = moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$file->is_valid_image());

                return $url->out();
            }
        }

        return $OUTPUT->get_generated_image_for_id($course->id);
    }
}
