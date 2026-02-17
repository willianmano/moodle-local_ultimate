<?php

/**
 * Game changer services definition
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_ultimate_chooseavatar' => [
        'classname' => 'local_ultimate\external\avatar',
        'classpath' => 'local/ultimate/classes/external/avatar.php',
        'methodname' => 'choose',
        'description' => 'User can choose an avatar',
        'type' => 'write',
        'ajax' => true
    ],
    'local_ultimate_createskill' => [
        'classname' => 'local_ultimate\external\skill',
        'classpath' => 'local/ultimate/classes/external/skill.php',
        'methodname' => 'create',
        'description' => 'Creates a new skill',
        'type' => 'write',
        'ajax' => true
    ],
    'local_ultimate_deleteskill' => [
        'classname' => 'local_ultimate\external\skill',
        'classpath' => 'local/ultimate/classes/external/skill.php',
        'methodname' => 'delete',
        'description' => 'Delete a skill',
        'type' => 'write',
        'ajax' => true
    ],
    'local_ultimate_createbadge' => [
        'classname' => 'local_ultimate\external\badge',
        'classpath' => 'local/ultimate/classes/external/badge.php',
        'methodname' => 'create',
        'description' => 'Creates a new badge',
        'type' => 'write',
        'ajax' => true
    ],
    'local_ultimate_editbadge' => [
        'classname' => 'local_ultimate\external\badge',
        'classpath' => 'local/ultimate/classes/external/badge.php',
        'methodname' => 'edit',
        'description' => 'Edit a badge',
        'type' => 'write',
        'ajax' => true
    ],
    'local_ultimate_deletebadge' => [
        'classname' => 'local_ultimate\external\badge',
        'classpath' => 'local/ultimate/classes/external/badge.php',
        'methodname' => 'delete',
        'description' => 'Deletes a badge',
        'type' => 'write',
        'ajax' => true
    ],
    'local_ultimate_deliverbadge' => [
        'classname' => 'local_ultimate\external\badge',
        'classpath' => 'local/ultimate/classes/external/badge.php',
        'methodname' => 'deliver',
        'description' => 'Deliver a badge for users',
        'type' => 'write',
        'ajax' => true
    ],
    'local_ultimate_createbadgecriteria' => [
        'classname' => 'local_ultimate\external\badgecriteria',
        'classpath' => 'local/ultimate/classes/external/badgecriteria.php',
        'methodname' => 'create',
        'description' => 'Creates a new badge criteria',
        'type' => 'write',
        'ajax' => true
    ],
    'local_ultimate_deletebadgecriteria' => [
        'classname' => 'local_ultimate\external\badgecriteria',
        'classpath' => 'local/ultimate/classes/external/badgecriteria.php',
        'methodname' => 'delete',
        'description' => 'Deletes a badge criteria',
        'type' => 'write',
        'ajax' => true
    ],
];
