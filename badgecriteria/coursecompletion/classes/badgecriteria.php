<?php

/**
 * This file contains the ultimate element coursecompletion's core interaction API.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace ultimatebadgecriteria_coursecompletion;

defined('MOODLE_INTERNAL') || die();

/**
 * The ultimate element coursecompletion's core interaction API.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends \local_ultimate\badgecriteria {
    public function user_achieved_criteria(): bool {
        global $CFG;

        require_once $CFG->dirroot.'/completion/completion_completion.php';

        $params = [
            'userid' => $this->userid,
            'course' => $this->badgecriteria->value,
        ];

        $ccompletion = new \completion_completion($params);

        return $ccompletion->is_complete();
    }

    public function get_user_criteria_progress(): int {
        global $DB;

        $course = $DB->get_record('course', ['id' => $this->badgecriteria->value]);

        $percentage = \core_completion\progress::get_course_progress_percentage($course, $this->userid);

        if (!is_null($percentage)) {
            $percentage = floor($percentage);
        }

        if (is_null($percentage)) {
            $percentage = 0;
        }

        return $percentage;
    }

    public function get_user_criteria_progress_html(): string {
        $pluginname = get_string('pluginname', 'ultimatebadgecriteria_coursecompletion');

        $progress = $this->get_user_criteria_progress();

        $criteriaprogresdesc = get_string('criteriaprogresdesc', 'ultimatebadgecriteria_coursecompletion');

        return '<p class="mb-0">'.$pluginname.'
                        <a class="btn btn-link p-0"
                           role="button"
                           data-bs-container="body"
                           data-bs-toggle="popover"
                           data-bs-placement="right"
                           data-bs-html="true"
                           tabindex="0"
                           data-bs-trigger="focus"
                           data-bs-content="<div class=\'no-overflow\'><p>'.$criteriaprogresdesc.'</p></div>">
                            <i class="icon fa fa-info-circle text-info fa-fw " title="'.$pluginname.'" role="img" aria-label="'.$pluginname.'"></i>
                        </a>
                    </p>
                    <div class="progress ml-0">
                        <div class="progress-bar" role="progressbar" style="width: '.$progress.'%" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100">'.$progress.'%</div>
                    </div>';
    }
}
