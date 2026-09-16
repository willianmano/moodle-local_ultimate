<?php

namespace local_ultimate\external;

use core\context;
use core_external\external_api;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_function_parameters;

/**
 * Avatar external api class.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class avatar extends external_api {
    /**
     * Choose avatar parameters
     *
     * @return external_function_parameters
     */
    public static function choose_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'avatarid' => new external_value(PARAM_TEXT, 'The avatar id'),
        ]);
    }

    /**
     * Choose avatar method
     *
     * @param int $contextid
     * @param string $avatarid
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function choose($contextid, $avatarid) {
        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::choose_parameters(),
            ['contextid' => $contextid, 'avatarid' => $avatarid]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $avatarutil = new \local_ultimate\util\avatar();

        $chosen = null;
        foreach ($avatarutil->get_avatars() as $avatar) {
            if ($avatar['id'] === $params['avatarid']) {
                $chosen = $avatar;
                break;
            }
        }

        if (!$chosen) {
            throw new \moodle_exception('invalidavatar', 'local_ultimate');
        }

        set_user_preference('ultimate_avatarid', $chosen['id']);

        return [
            'status' => 'ok',
            'message' => get_string('chooseavatar_success', 'local_ultimate'),
            'data' => json_encode($chosen['url'])
        ];
    }

    /**
     * Choose avatar return fields
     *
     * @return external_single_structure
     */
    public static function choose_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_RAW, 'Return message'),
                'data' => new external_value(PARAM_RAW, 'Return data')
            )
        );
    }
}