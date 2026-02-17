<?php

/**
 * Block util class
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\util;

class block {
    public function context_has_block($contextid) {
        global $DB;

        return $DB->record_exists('block_instances', ['blockname' => 'ultimate', 'parentcontextid' => $contextid]);
    }
}
