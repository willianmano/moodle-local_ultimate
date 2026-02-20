<?php

/**
 * This file contains the ultimate element coins core interaction API.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace ultimatebadgecriteria_skillpointsaggregation;

use local_ultimate\util\coin;
use local_ultimate\util\skill;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The ultimate element coins core interaction API.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class criteriaform extends \moodleform {

    /**
     * Class constructor.
     *
     * @param array $formdata
     * @param array $customodata
     */
    public function __construct($formdata, $customodata = null) {
        parent::__construct(null, $customodata, 'post',  '', ['class' => 'ultimate-badgecriterias-form'], true, $formdata);

        $this->set_display_vertical();
    }

    /**
     * The form definition.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function definition() {
        $mform = $this->_form;

        $courseid = !(empty($this->_customdata['courseid'])) ? $this->_customdata['courseid'] : null;
        $badgeid = !(empty($this->_customdata['badgeid'])) ? $this->_customdata['badgeid'] : null;
        $method = !(empty($this->_customdata['method'])) ? $this->_customdata['method'] : null;

        $mform->addElement('hidden', 'method', $method);
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->addElement('hidden', 'badgeid', $badgeid);

        $skillutil = new skill();
        $options = $skillutil->get_course_skills_select($courseid);
        $mform->addElement('select', 'target', get_string('target', 'ultimatebadgecriteria_skillpointsaggregation'), $options);
        $mform->setType('target', PARAM_INT);
        $mform->getElement('target')->setMultiple(true);
        $mform->addRule('target', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'value', get_string('value', 'ultimatebadgecriteria_skillpointsaggregation'));
        $mform->addHelpButton('value', 'value', 'ultimatebadgecriteria_skillpointsaggregation');
        $mform->addRule('value', get_string('required'), 'required', null, 'client');
        $mform->setType('value', PARAM_INT);
    }

    /**
     * A bit of custom validation for this form
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $target = $data['target'] ?? null;
        $value = $data['value'] ?? null;

        if ($this->is_submitted() && (empty($target))) {
            $errors['target'] = get_string('required');
        }

        if ($this->is_submitted() && (empty($value))) {
            $errors['value'] = get_string('required');
        }

        return $errors;
    }
}
