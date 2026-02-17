<?php

/**
 * Coin util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

class level {
    /**
     *
     * @param \stdClass $points ultimate_points table instance
     *
     * @return void
     */
    public function calculate_level(\stdClass $points) {
        $config = get_config('local_ultimate');

        $levels = $this->levels($config);

        return $this->get_updated_level($levels, $points->level, $points->points);
    }

    public function get_next_level_points($currentlevel)
    {
        $config = get_config('local_ultimate');

        $levels = $this->levels($config);

        $maxlevel = (int) $config->level_number;

        $nextlevel = $currentlevel == $maxlevel ? $currentlevel : $currentlevel + 1;

        return $levels[$nextlevel];
    }

    private function levels($config) {
        $maxlevel = (int) $config->level_number;
        $levels = [];

        for ($i = 2; $i <= $maxlevel; $i++) {
            $xlevel = 'level_up' . $i;

            $levels[$i] = (int) $config->$xlevel;
        }

        return $levels;
    }

    private function get_updated_level($levels, $currentlevel, $points) {
        $newlevel = $currentlevel;

        foreach ($levels as $level => $levelpoints) {
            if ($level < $newlevel) {
                continue;
            }

            if ($points < $levelpoints && $newlevel == 1) {
                return 1;
            }

            if ($points >= $levelpoints) {
                $newlevel++;

                continue;
            }

            return $newlevel;
        }

        return $currentlevel;
    }
}
