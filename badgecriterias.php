<?php

/**
 * Configure course badge criterias.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');

// Course module id.
$id = required_param('id', PARAM_INT);

$gamebadge = $DB->get_record('ultimate_badges', ['id' => $id], '*', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $gamebadge->courseid], '*', MUST_EXIST);

require_course_login($course, true);

$context = context_course::instance($course->id);

$PAGE->set_url('/local/ultimate/badgecriterias.php', ['id' => $course->id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_ultimate');

$contentrenderable = new \local_ultimate\output\badgecriterias($course, $context, $gamebadge);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
