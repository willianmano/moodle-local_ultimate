<?php

namespace local_ultimate\output;

defined('MOODLE_INTERNAL') || die();

use local_ultimate\util\skill;
use renderable;
use templatable;
use renderer_base;

/**
 * Ranking renderable class.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class skills implements renderable, templatable {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function export_for_template(renderer_base $output) {
        $skillutil = new skill();

        return [
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'skills' => $skillutil->get_course_skills($this->course->id)
        ];
    }
}
