<?php

/**
 * Game changer events definition
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => '\local_ultimate\observers\modulecompleted::observer',
        'internal' => false
    ],
    [
        'eventname' => '\local_ultimate\event\skills_added',
        'callback' => '\local_ultimate\observers\badgeissuer::observer',
        'internal' => false
    ],
    [
        'eventname' => '\local_ultimate\event\coins_added',
        'callback' => '\local_ultimate\observers\badgeissuer::observer',
        'internal' => false
    ],
    [
        'eventname' => '\core\event\course_viewed',
        'callback' => '\local_ultimate\observers\badgeissuer::observer',
        'internal' => false
    ],
];
