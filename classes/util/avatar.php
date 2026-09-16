<?php

/**
 * Avatar util class
 *
 * @package     local_ultimate
 * @copyright   2026 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

defined('MOODLE_INTERNAL') || die;

class avatar {
    /**
     * File area used by the admin_setting_configstoredfile setting to store the custom avatars.
     */
    const FILEAREA = 'avatars';

    /**
     * Amount of default avatars shipped in pix/avatars.
     */
    const DEFAULT_AVATARS_COUNT = 18;

    /**
     * Returns the custom avatars uploaded by the admin, if any.
     *
     * @return \stored_file[]
     */
    protected function get_custom_avatar_files() {
        $fs = get_file_storage();
        $context = \context_system::instance();

        $files = $fs->get_area_files($context->id, 'local_ultimate', self::FILEAREA, 0, 'sortorder, filepath, filename', false);

        return array_values($files);
    }

    /**
     * Whether the admin has uploaded any custom avatar.
     *
     * @return bool
     */
    public function has_custom_avatars() {
        return !empty($this->get_custom_avatar_files());
    }

    /**
     * Returns the list of avatars available for users to choose from.
     *
     * If the admin has uploaded custom avatars, only those are returned, otherwise the default
     * avatars shipped with the plugin are returned.
     *
     * @return array Each item has an 'id' (opaque identifier to be stored as user preference) and an 'url'.
     */
    public function get_avatars() {
        global $CFG;

        $customfiles = $this->get_custom_avatar_files();

        if ($customfiles) {
            $avatars = [];

            foreach ($customfiles as $file) {
                $avatars[] = [
                    'id' => 'custom:' . $file->get_filename(),
                    'url' => \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        'local_ultimate',
                        self::FILEAREA,
                        0,
                        '/',
                        $file->get_filename()
                    )->out(false),
                ];
            }

            return $avatars;
        }

        $avatars = [];
        for ($i = 1; $i <= self::DEFAULT_AVATARS_COUNT; $i++) {
            $avatars[] = [
                'id' => 'default:' . $i,
                'url' => $CFG->wwwroot . '/local/ultimate/pix/avatars/' . $i . '.png',
            ];
        }

        return $avatars;
    }

    /**
     * Resolves the url of a previously chosen avatar.
     *
     * @param string $avatarid Value stored as the 'ultimate_avatarid' user preference.
     * @return string|null Null when the avatar can no longer be resolved (e.g. it was removed by the admin).
     */
    public function get_avatar_url($avatarid) {
        global $CFG;

        // Older preferences stored just the default avatar number, keep supporting them.
        if (is_numeric($avatarid)) {
            $avatarid = 'default:' . $avatarid;
        }

        [$type, $value] = array_pad(explode(':', $avatarid, 2), 2, null);

        if ($type === 'custom') {
            $fs = get_file_storage();
            $context = \context_system::instance();

            $file = $fs->get_file($context->id, 'local_ultimate', self::FILEAREA, 0, '/', $value);

            if (!$file) {
                return null;
            }

            return \moodle_url::make_pluginfile_url(
                $context->id,
                'local_ultimate',
                self::FILEAREA,
                0,
                '/',
                $value
            )->out(false);
        }

        if ($type === 'default' && $value && is_numeric($value)) {
            return $CFG->wwwroot . '/local/ultimate/pix/avatars/' . $value . '.png';
        }

        return null;
    }
}
