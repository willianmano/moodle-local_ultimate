<?php

/**
 * Plugin lib.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 * https://docs.moodle.org/dev/Callbacks
 */
function local_ultimate_coursemodule_standard_elements($formwrapper, $mform) {
    global $DB;

    $contextcourse = \core\context\course::instance($formwrapper->get_current()->course);
    $blockutil = new \local_ultimate\util\block();
    if (!$blockutil->context_has_block($contextcourse->id)) {
        return;
    }

    // Coins.
    $options = [0 => get_string('chooseavalue', 'local_ultimate')];
    foreach (range(1, 100) as $option) {
        $options[$option] = $option;
    }
    $mform->addElement('header', 'coinheader', get_string('coins', 'local_ultimate'));
    $mform->addElement('select', 'coins', get_string('coins', 'local_ultimate'), $options);
    $mform->setType('coins', PARAM_INT);

    $coinutil = new \local_ultimate\util\coinmodule();
    if ($formwrapper->get_coursemodule() && $coins = $coinutil->get_module_coins($formwrapper->get_coursemodule()->id)) {
        $mform->setDefault('coins', $coins);
    }

    // Skills.
    $skillutil = new \local_ultimate\util\skill();
    $skills = $skillutil->get_course_skills($formwrapper->get_current()->course);

    $mform->addElement('header', 'skillsheader', get_string('skills', 'local_ultimate'));

    $url = new moodle_url('/local/ultimate/skills.php', ['id' => $formwrapper->get_current()->course]);

    $alert = '<div class="alert alert-info">';
    $alert .= '<p>Pontos de habilidade são dados aos estudantes após a conclusão da atividade.</p>';
    $alert .= '<p class="mb-0"><a href="'.$url.'">Gerenciar habilidades do curso</a></p>';
    $alert .= '</div>';

    $mform->addElement('html', $alert);

    if (!$skills) {
        return $formwrapper;
    }

    $dbpoints = [];
    if ($formwrapper->get_coursemodule()) {
        $dbpoints = $DB->get_records('ultimate_skills_modules', ['cmid' => $formwrapper->get_coursemodule()->id], '', 'skillid as id, value');
    }

    foreach ($skills as $skill) {
        $fieldname = "ultimateskills[$skill->id]" ;
        $points = json_decode($skill->points);

        $options = [0 => get_string('chooseavalue', 'local_ultimate')];
        foreach ($points as $point) {
            $options[(int) $point] = $point;
        }

        $mform->addElement('select', $fieldname, $skill->name, $options);
        $mform->setType($fieldname, PARAM_INT);

        if (isset($dbpoints[$skill->id])) {
            $mform->setDefault($fieldname, $dbpoints[$skill->id]->value);
        }
    }
}

/**
 * Saves the data of custom fields elements of all moodle module settings forms.
 *
 * @param object $moduleinfo the module info
 * @param object $course the course of the module
 */
function local_ultimate_coursemodule_edit_post_actions($moduleinfo, $course) {
    $contextcourse = \core\context\course::instance($course->id);
    $blockutil = new \local_ultimate\util\block();
    if (!$blockutil->context_has_block($contextcourse->id)) {
        return $moduleinfo;
    }

    $ultimateskills = $moduleinfo->ultimateskills ?? [];
    $coins = $moduleinfo->coins ?? null;

    $skillutil = new \local_ultimate\util\skillmodule();
    $skillutil->sync_module_skills($moduleinfo->coursemodule, $ultimateskills);

    $coinutil = new \local_ultimate\util\coinmodule();
    $coinutil->sync_module_coins($moduleinfo->coursemodule, $coins);

    return $moduleinfo;
}

function local_ultimate_extend_navigation_course($navigation, $course, $context) {
    $blockutil = new \local_ultimate\util\block();

    if (has_capability('moodle/course:update', $context) && $blockutil->context_has_block($context->id)) {
        $url = new moodle_url('/local/ultimate/badges.php', array('id' => $course->id));

        $navigation->add(
            get_string('badgessettings', 'local_ultimate'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'ultimatebadgessettings',
            new pix_icon('t/award', '')
        );

        $url = new moodle_url('/local/ultimate/skills.php', array('id' => $course->id));

        $navigation->add(
            get_string('skillssettings', 'local_ultimate'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'ultimateskills',
            new pix_icon('i/course', '')
        );
    }
}

/**
 * Returns choose avatar form fragment.
 *
 * @param $args
 * @return string
 */
function local_ultimate_output_fragment_chooseavatar_form($args) {
    $avatarutil = new \local_ultimate\util\avatar();

    $o = html_writer::start_div('chooseavatar-form');

    foreach ($avatarutil->get_avatars() as $avatar) {
        $o .= html_writer::img($avatar['url'], 'avatar', ['class' => 'avatar', 'data-id' => $avatar['id']]);
    }

    $o .= html_writer::end_div();

    return $o;
}

/**
 * Serves the custom avatars uploaded by the admin through the local_ultimate/avatars setting.
 *
 * @param stdClass $course
 * @param stdClass|null $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return void
 */
function local_ultimate_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM || $filearea !== 'avatars') {
        send_file_not_found();
    }

    $itemid = (int) array_shift($args);
    $filename = array_pop($args);

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_ultimate', 'avatars', $itemid, '/', $filename);

    if (!$file) {
        send_file_not_found();
    }

    if (!array_key_exists('cacheability', $options)) {
        $options['cacheability'] = 'public';
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Returns create skill form fragment.
 *
 * @param $args
 * @return string
 */
function local_ultimate_output_fragment_skill_form($args) {
    $args = (object) $args;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        $formdata = (array)$serialiseddata;
    }

    $mform = new \local_ultimate\forms\skill($formdata, [
        'courseid' => $serialiseddata->courseid,
    ]);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}

function local_ultimate_output_fragment_badge_form($args) {
    $args = (object) $args;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        $formdata = (array)$serialiseddata;
    }

    $mform = new \local_ultimate\forms\badge($formdata, [
        'id' => $serialiseddata->id,
        'name' => $serialiseddata->name,
        'description' => $serialiseddata->description,
        'badgeid' => $serialiseddata->badgeid,
        'courseid' => $serialiseddata->courseid,
    ]);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}

function local_ultimate_output_fragment_badgecriteria_form($args) {
    $args = (object) $args;
    $o = '';

    $serialiseddata = json_decode($args->jsonformdata);
    $formdata = (array)$serialiseddata;

    $criteriaformclass = '\ultimatebadgecriteria_'.$serialiseddata->criteria.'\criteriaform';
    $mform = new $criteriaformclass($formdata, [
        'courseid' => $serialiseddata->courseid,
        'badgeid' => $serialiseddata->badgeid,
        'method' => $serialiseddata->criteria,
    ]);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}
