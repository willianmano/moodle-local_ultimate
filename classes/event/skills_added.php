<?php

namespace local_ultimate\event;

/**
 * The skills_added event class.
 *
 * @package     local_ultimate
 * @category    event
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class skills_added extends \core\event\base {
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id {$this->relateduserid} received skill points in th course with id {$this->courseid}.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_skills_added', 'local_ultimate');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
            return new \moodle_url('/local/ultimate/profile.php', ['id' => $this->courseid]);
    }

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'ultimate_skills_users';
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->courseid)) {
            throw new \coding_exception('The \'courseid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'ultimate_skills_users', 'restore' => 'ultimate_skills_user');
    }
}
