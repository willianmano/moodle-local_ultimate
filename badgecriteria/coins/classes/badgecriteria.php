<?php

/**
 * This file contains the ultimate element coins core interaction API.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace ultimatebadgecriteria_coins;

use local_ultimate\util\coin;

defined('MOODLE_INTERNAL') || die();

/**
 * The ultimate element coins core interaction API.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriteria extends \local_ultimate\badgecriteria {
    public function user_achieved_criteria(): bool {
        $coinutil = new coin();
        $coins = $coinutil->get_user_coins();

        $requiredcoins = (int) $this->badgecriteria->value;
        if ($coins >= $requiredcoins) {
            return true;
        }

        return false;
    }

    public function get_user_criteria_progress(): int {
        $coinutil = new coin();
        $coins = $coinutil->get_user_coins();

        if ($coins == 0) {
            return 0;
        }

        $requiredcoins = (int) $this->badgecriteria->value;
        if ($coins >= $requiredcoins) {
            return 100;
        }

        return (int)($coins * 100 / $this->badgecriteria->value);
    }

    public function get_user_criteria_progress_html(): string {
        $pluginname = get_string('pluginname', 'ultimatebadgecriteria_coins');

        $progress = $this->get_user_criteria_progress();

        $criteriaprogresdesc = get_string('criteriaprogresdesc', 'ultimatebadgecriteria_coins', $this->badgecriteria->value);

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
