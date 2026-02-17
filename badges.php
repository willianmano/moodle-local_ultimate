<?php

/**
 * Configure course badges.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');

// Course module id.
$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_course_login($course);

$context = context_course::instance($course->id);

$blockutil = new \local_ultimate\util\block();

if (!$blockutil->context_has_block($context->id)) {
    redirect(new moodle_url('/course/view.php', ['id' => $course->id]), \core\notification::error('Illegal access!'));
}

$PAGE->set_url('/local/ultimate/badges.php', ['id' => $course->id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_ultimate');

$contentrenderable = new \local_ultimate\output\badges($course, $context);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
