<?php

namespace local_ultimate\output;

defined('MOODLE_INTERNAL') || die();

use local_ultimate\util\course;
use local_ultimate\util\skill;
use local_ultimate\util\badge;
use renderable;
use templatable;
use renderer_base;
use local_ultimate\util\user;

/**
 * Profile renderable class.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class profile implements renderable, templatable {
    protected $course;
    protected $context;
    protected $user;

    public function __construct($context, $course, $user) {
        $this->course = $course;
        $this->context = $context;
        $this->user = $user;
    }

    public function export_for_template(renderer_base $output) {
        global $USER;

        $userutil = new user();

        $data = [
            'id' => $this->user->id,
            'firstname' => $this->user->firstname,
            'lastname' => $this->user->lastname,
            'email' => $this->user->email,
            'useravatar' => $userutil->get_user_avatar_or_image($this->user),
            'itsme' => $USER->id == $this->user->id,
            'contextid' => $this->context->id,
        ];

        if ($this->course->id == 1) {
            $courseutil = new course();

            $data['courses'] = $courseutil->get_user_courses_with_progress();
            $data['hascourses'] = $data['courses'] != false;

            return $data;
        }

        $badgeutil = new badge();
        $data['badges'] = $badgeutil->get_user_course_badges_with_criterias($this->user->id, $this->course->id, $this->context->id, 1);
        $data['hasbadges'] = !empty($data['badges']);

        $skillutil = new skill();
        $data['skills'] = $skillutil->get_course_skills_set($this->course->id, $this->user->id);
        $data['hasskills'] = $data['skills'] != false;

        return $data;
    }
}
