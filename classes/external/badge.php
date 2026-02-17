<?php

namespace local_ultimate\external;

use core\context;
use core_external\external_api;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_ultimate\forms\badge as badgeform;
use local_ultimate\observers\badgeissuer;

/**
 * Badge external api class.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badge extends external_api {
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
     * @param int $courseid
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
        global $DB, $PAGE, $CFG, $USER, $SITE;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::create_parameters(),
            ['contextid' => $contextid, 'courseid' => $courseid, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = [];
        parse_str($serialiseddata, $data);

        $mform = new badgeform($data);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        $now = time();

        // Creates Moodle Badge.
        require_once($CFG->libdir . '/badgeslib.php');

        $mdlbadge = new \stdClass();
        $mdlbadge->name = $validateddata->name;
        $mdlbadge->description = $validateddata->description;
        $mdlbadge->courseid = $courseid;
        $mdlbadge->usercreated = $USER->id;
        $mdlbadge->usermodified = $USER->id;
        $mdlbadge->version = '';
        $mdlbadge->type = 2;
        $mdlbadge->imageauthorname = '';
        $mdlbadge->imageauthoremail = '';
        $mdlbadge->imageauthorurl = '';
        $mdlbadge->imagecaption = '';
        $mdlbadge->timecreated = $now;
        $mdlbadge->timemodified = $now;
        $mdlbadge->issuerurl = $CFG->wwwroot;
        $mdlbadge->issuername = $SITE->fullname;
        $mdlbadge->issuercontact = $CFG->badges_defaultissuercontact;

        $mdlbadge->messagesubject = get_string('messagesubject', 'badges');
        $mdlbadge->message = get_string('messagebody', 'badges',
            \html_writer::link($CFG->wwwroot . '/badges/mybadges.php', get_string('managebadges', 'badges')));
        $mdlbadge->attachment = 1;
        $mdlbadge->notification = BADGE_MESSAGE_NEVER;
        $mdlbadge->status = BADGE_STATUS_ACTIVE;

        $mdlbadgeid = $DB->insert_record('badge', $mdlbadge);

        // Add badges criterias.
        $badgecriteria = new \stdClass();

        $badgecriteria->badgeid = $mdlbadgeid;
        $badgecriteria->criteriatype = 0;
        $badgecriteria->method = 1;
        $badgecriteria->description = '';
        $badgecriteria->descriptionformat = 1;

        $DB->insert_record('badge_criteria', $badgecriteria);

        $badgecriteria->criteriatype = 2;
        $badgecriteria->method = 2;

        $DB->insert_record('badge_criteria', $badgecriteria);

        $eventparams = array('objectid' => $mdlbadgeid, 'context' => $PAGE->context);
        $event = \core\event\badge_created::create($eventparams);
        $event->trigger();

        $newbadge = new \core_badges\badge($mdlbadgeid);

        badges_process_badge_image($newbadge, $mform->save_temp_file('image'));

        $localbadge = new \stdClass();
        $localbadge->courseid = $courseid;
        $localbadge->badgeid = $mdlbadgeid;
        $localbadge->name = $validateddata->name;
        $localbadge->timecreated = time();
        $localbadge->timemodified = time();

        $localbadgeid = $DB->insert_record('ultimate_badges', $localbadge);

        $localbadge->id = $localbadgeid;

        return [
            'status' => 'ok',
            'message' => get_string('createbadge_success', 'local_ultimate'),
            'data' => json_encode($localbadge)
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
     * Create badge parameters
     *
     * @return external_function_parameters
     */
    public static function edit_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the badge form, encoded as a json array')
        ]);
    }

    /**
     * Create badge method
     *
     * @param int $contextid
     * @param string $jsonformdata
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function edit($contextid, $jsonformdata) {
        global $DB, $USER;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::edit_parameters(),
            ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = [];
        parse_str($serialiseddata, $data);

        $mform = new badgeform($data, $data);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        $localbadge = $DB->get_record('ultimate_badges', ['id' => $validateddata->id], '*', MUST_EXIST);
        $mdlbadge = $DB->get_record('badge', ['id' => $localbadge->badgeid], '*', MUST_EXIST);

        $localbadge->name = $validateddata->name;
        $localbadge->timemodified = time();
        $DB->update_record('ultimate_badges', $localbadge);

        $mdlbadge->name = $validateddata->name;
        $mdlbadge->description = $validateddata->description;
        $mdlbadge->timemodified = time();
        $mdlbadge->usermodified = $USER->id;

        $DB->update_record('badge', $mdlbadge);

        $returndata = [
            'name' => $mdlbadge->name,
            'description' => $mdlbadge->description
        ];

        return [
            'status' => 'ok',
            'message' => get_string('editbadge_success', 'local_ultimate'),
            'data' => json_encode($returndata)
        ];
    }

    /**
     * Create badge return fields
     *
     * @return external_single_structure
     */
    public static function edit_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_RAW, 'Return message'),
                'data' => new external_value(PARAM_RAW, 'Return data')
            )
        );
    }

    /**
     * Delete badge parameters
     *
     * @return external_function_parameters
     */
    public static function delete_parameters() {
        return new external_function_parameters([
            'badge' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The badge id', VALUE_REQUIRED)
            ])
        ]);
    }

    /**
     * Delete badge method
     *
     * @param array $badge
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function delete($badge) {
        global $DB, $PAGE;

        self::validate_parameters(self::delete_parameters(), ['badge' => $badge]);

        $badge = (object)$badge;

        $badgedb = $DB->get_record('ultimate_badges', ['id' => $badge->id], '*', MUST_EXIST);

        $context = \context_course::instance($badgedb->courseid);
        $PAGE->set_context($context);

        $newbadge = new \core_badges\badge($badgedb->badgeid);
        $newbadge->delete(false);

        $DB->delete_records('ultimate_badges', ['id' => $badgedb->id]);

        return [
            'status' => 'ok',
            'message' => get_string('deletebadge_success', 'local_ultimate')
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

    /**
     * Deliver badge parameters
     *
     * @return external_function_parameters
     */
    public static function deliver_parameters() {
        return new external_function_parameters([
            'badge' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The badge id', VALUE_REQUIRED)
            ])
        ]);
    }

    /**
     * Deliver badge method
     *
     * @param array $badge
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function deliver($badge) {
        global $DB, $PAGE;

        self::validate_parameters(self::delete_parameters(), ['badge' => $badge]);

        $badge = (object)$badge;

        $localbadge = $DB->get_record('ultimate_badges', ['id' => $badge->id], '*', MUST_EXIST);

        $badgecriterias = $DB->get_records('ultimate_badges_criterias', ['badgeid' => $localbadge->id]);

        if (!$badgecriterias) {
            throw new \Exception(get_string('deliverbadge_badgenocriterias', 'local_ultimate'));
        }

        $context = \context_course::instance($localbadge->courseid);
        $PAGE->set_context($context);

        $sql = 'SELECT DISTINCT u.id';

        $capjoin = get_enrolled_with_capabilities_join($context, '', 'moodle/course:viewparticipants');

        $sql .= ' FROM {user} u ' . $capjoin->joins . ' WHERE ' . $capjoin->wheres;

        $params = $capjoin->params;

        $users = $DB->get_records_sql($sql, $params);

        $counter = 0;
        foreach ($users as $user) {
            if (badgeissuer::user_already_have_badge($user->id, $localbadge->badgeid)) {
                continue;
            }

            if (!badgeissuer::check_if_user_can_receive_badge($user->id, $badgecriterias)) {
                continue;
            }

            badgeissuer::deliver_badge($user->id, $localbadge);

            $counter++;
        }

        return [
            'status' => 'ok',
            'message' => get_string('deliverbadge_success', 'local_ultimate', $counter)
        ];
    }

    /**
     * Deliver badge return fields
     *
     * @return external_single_structure
     */
    public static function deliver_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_TEXT, 'Return message')
            )
        );
    }
}