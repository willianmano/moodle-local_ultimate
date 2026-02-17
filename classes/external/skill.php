<?php

namespace local_ultimate\external;

use core\context;
use core_external\external_api;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_ultimate\forms\skill as skillform;

/**
 * Badge criteria external api class.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class skill extends external_api {
    /**
     * Create badge parameters
     *
     * @return external_function_parameters
     */
    public static function create_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'courseid' => new external_value(PARAM_INT, 'The course id'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the badge form, encoded as a json array')
        ]);
    }

    /**
     * Create badge method
     *
     * @param int $contextid
     * @param int $course
     * @param string $jsonformdata
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function create($contextid, $courseid, $jsonformdata) {
        global $DB;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::create_parameters(),
            ['contextid' => $contextid, 'courseid' => $courseid, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = [];
        parse_str($serialiseddata, $data);

        $mform = new skillform($data, $data);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        $pointsoptions = preg_split('/\n|\r\n?/', $validateddata->points);

        foreach ($pointsoptions as $option) {
            $value = (int) $option;

            if (empty($option) || empty($value)) {
                continue;
            }

            $pointsarr[] = $option;
        }

        // TODO: Validar quando pointsarr for vazio.

        $now = time();

        $skill = new \stdClass();
        $skill->courseid = $courseid;
        $skill->name = $validateddata->name;
        $skill->points = json_encode($pointsarr);
        $skill->timecreated = $now;
        $skill->timemodified = $now;

        $insertedid = $DB->insert_record('ultimate_skills', $skill);

        $skill->id = $insertedid;

        return [
            'status' => 'ok',
            'message' => get_string('createskill_success', 'local_ultimate'),
            'data' => json_encode($skill)
        ];
    }

    /**
     * Create badge return fields
     *
     * @return external_single_structure
     */
    public static function create_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_RAW, 'Return message'),
                'data' => new external_value(PARAM_RAW, 'Return data')
            )
        );
    }

    /**
     * Delete skill parameters
     *
     * @return external_function_parameters
     */
    public static function delete_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'The skill id', VALUE_REQUIRED)
        ]);
    }

    /**
     * Delete skill method
     *
     * @param array $skill
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function delete($id) {
        self::validate_parameters(self::delete_parameters(), ['id' => $id]);

        $skillutil = new \local_ultimate\util\skill();
        $skillutil->delete($id);

        return [
            'status' => 'ok',
            'message' => get_string('deleteskill_success', 'local_ultimate')
        ];
    }

    /**
     * Delete badge return fields
     *
     * @return external_single_structure
     */
    public static function delete_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_TEXT, 'Return message')
            )
        );
    }
}