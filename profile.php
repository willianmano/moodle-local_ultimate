<?php

/**
 * Prints local_ultimate profile page.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');

$courseid = required_param('course', PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

$urlparams = ['id' => $course->id];

$context = context_course::instance($course->id);

if (is_guest($context)) {
    redirect(new moodle_url('/'));
}

$user = $USER;

require_course_login($course, false);

$PAGE->set_url('/local/ultimate/profile.php', $urlparams);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_ultimate');

$contentrenderable = new \local_ultimate\output\profile($context, $course, $user);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
