<?php

namespace local_ultimate\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Badge criterias renderable class.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badgecriterias implements renderable, templatable {
    protected $course;
    protected $context;
    protected $gamebadge;

    public function __construct($course, $context, $gamebadge) {
        $this->course = $course;
        $this->context = $context;
        $this->gamebadge = $gamebadge;
    }

    public function export_for_template(renderer_base $output) {
        $badgecriteriautil = new \local_ultimate\util\badgecriteria();

        $criteriamethods = [];
        $installedcriterias = \core_plugin_manager::instance()->get_plugins_of_type('ultimatebadgecriteria');
        if ($installedcriterias) {
            foreach ($installedcriterias as $criteria) {
                $criteriamethods[] = [
                    'name' => $criteria->name,
                    'displayname' => $criteria->displayname,
                ];
            }
        }

        return [
            'contextid' => $this->context->id,
            'courseid' => $this->course->id,
            'badgeid' => $this->gamebadge->id,
            'criterias' => $badgecriteriautil->get_game_badge_criterias_with_skill_name($this->gamebadge),
            'criteriamethods' => $criteriamethods,
        ];
    }
}
