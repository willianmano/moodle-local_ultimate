<?php

namespace local_ultimate\output;

defined('MOODLE_INTERNAL') || die();

use local_ultimate\util\badge as badgeutil;
use renderable;
use templatable;
use renderer_base;

/**
 * Badge renderable class.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badges implements renderable, templatable {
    protected $course;
    protected $context;

    public function __construct($course, $context) {
        $this->course = $course;
        $this->context = $context;
    }

    public function export_for_template(renderer_base $output) {
        $badgeutil = new badgeutil();

        return [
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'badges' => $badgeutil->get_local_badges($this->course->id)
        ];
    }
}
