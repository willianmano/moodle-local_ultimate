<?php

namespace local_ultimate;

abstract class badgecriteria {
    protected $userid;
    protected $courseid;
    protected $badgecriteria;

    public function __construct(int $userid, int $courseid, \stdClass $badgecriteria) {
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->badgecriteria = $badgecriteria;
    }

    public abstract function user_achieved_criteria(): bool;
    public abstract function get_user_criteria_progress(): int;
    public abstract function get_user_criteria_progress_html(): string;
}
