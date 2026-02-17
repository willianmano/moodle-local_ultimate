<?php

/**
 * Game changer badge notification.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace local_ultimate\notification;

defined('MOODLE_INTERNAL') || die();

/**
 * Game changer badge notification service.
 *
 * @package     local_ultimate
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class badge {

    /** User preference prefix. */
    const USERPREF_NOTIFY = 'local_ultimate_notify_new_badge';

    protected $userid;

    /**
     * Constructor.
     *
     * @param int $courseid The course ID.
     */
    public function __construct($userid) {
        $this->userid = $userid;
    }

    /**
     * Flag the user as having been notified.
     *
     * @param int $userid The user ID.
     */
    public function mark_as_notified() {
        unset_user_preference(self::USERPREF_NOTIFY, $this->userid);
    }

    /**
     * Notify a user.
     *
     * @param int $userid The user ID.
     * @return void
     */
    public function notify($gamebadgeid) {
        set_user_preference(self::USERPREF_NOTIFY, $gamebadgeid, $this->userid);
    }

    /**
     * Whether the user should be notified.
     *
     * @param int $userid The user ID.
     * @return bool
     */
    public function should_be_notified() {
        return (bool) get_user_preferences(self::USERPREF_NOTIFY, false, $this->userid);
    }

    /**
     * Returns notification badge data.
     *
     * @return bool
     */
    public function get_notification_data() {
        return get_user_preferences(self::USERPREF_NOTIFY, false, $this->userid);
    }
}
