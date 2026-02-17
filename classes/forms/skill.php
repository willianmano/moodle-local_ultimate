<?php

namespace local_ultimate\forms;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir. '/formslib.php');

class skill extends \moodleform {
    /**
     * Class constructor.
     *
     * @param array $formdata
     * @param array $customodata
     */
    public function __construct($formdata, $customodata = null) {
        parent::__construct(null, $customodata, 'post',  '', ['class' => 'ultimate-skills-form'], true, $formdata);

        $this->set_display_vertical();
    }

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'local_ultimate'), ['style' => 'width: 100%;']);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('textarea', 'points', get_string('points', 'local_ultimate'), 'wrap="virtual" rows="5" style="width: 100%;"');
        $mform->addRule('points', null, 'required', null, 'client');
        $mform->setType('points', PARAM_TEXT);
        $mform->addHelpButton('points', 'points', 'local_ultimate');

        $this->add_action_buttons(true);
    }
}
