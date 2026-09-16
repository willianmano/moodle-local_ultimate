<?php

/**
 * User util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

defined('MOODLE_INTERNAL') || die;

class user {
    public function get_user_avatar_or_image($user = null) {
        global $USER, $PAGE, $CFG, $DB;

        if (!$user) {
            $user = $USER;
        }

        if (!is_object($user)) {
            $user = $DB->get_record('user', ['id' => $user], '*', MUST_EXIST);
        }

        $useravatar = get_user_preferences('ultimate_avatarid', null, $user);

        if ($useravatar) {
            $avatarutil = new avatar();
            $avatarurl = $avatarutil->get_avatar_url($useravatar);

            if ($avatarurl) {
                return $avatarurl;
            }
        }

        $userpicture = new \user_picture($user);
        $userpicture->size = 1;

        return $userpicture->get_url($PAGE);
    }
}
