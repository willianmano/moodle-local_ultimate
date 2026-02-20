/**
 * Delete badge criteria js logic.
 *
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
define(['jquery', 'core/ajax', 'core/str', 'local_ultimate/sweetalert', 'core/notification'],
    function($, Ajax, Str, Swal, Notification) {
    var STRINGS = {
        CONFIRM_TITLE: 'Are you sure?',
        CONFIRM_MSG: 'Once deleted, the item cannot be recovered!',
        CONFIRM_YES: 'Yes, delete it!',
        CONFIRM_NO: 'Cancel',
        SUCCESS: 'Chapter successfully deleted.'
    };

    var componentStrings = [
        {
            key: 'deleteitem_confirm_title',
            component: 'local_ultimate'
        },
        {
            key: 'deleteitem_confirm_msg',
            component: 'local_ultimate'
        },
        {
            key: 'deleteitem_confirm_yes',
            component: 'local_ultimate'
        },
        {
            key: 'deleteitem_confirm_no',
            component: 'local_ultimate'
        },
        {
            key: 'deletebadgecriteria_success',
            component: 'local_ultimate'
        },
    ];

    var DeleteBadgeCriteria = function() {
        this.getStrings();

        this.registerEventListeners();
    };

    DeleteBadgeCriteria.prototype.getStrings = function() {
        var stringsPromise = Str.get_strings(componentStrings);

        $.when(stringsPromise).done(function(strings) {
            STRINGS.CONFIRM_TITLE = strings[0];
            STRINGS.CONFIRM_MSG = strings[1];
            STRINGS.CONFIRM_YES = strings[2];
            STRINGS.CONFIRM_NO = strings[3];
            STRINGS.SUCCESS = strings[4];
        });
    };

    DeleteBadgeCriteria.prototype.registerEventListeners = function() {
        $("body").on("click", ".delete-ultimate-badgecriteria", function(event) {
            event.preventDefault();

            var eventTarget = $(event.currentTarget);

            Swal.fire({
                title: STRINGS.CONFIRM_TITLE,
                text: STRINGS.CONFIRM_MSG,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: STRINGS.CONFIRM_YES,
                cancelButtonText: STRINGS.CONFIRM_NO
            }).then(function(result) {
                if (result.value) {
                    this.deleteItemRow(eventTarget);
                }
            }.bind(this));
        }.bind(this));
    };

    DeleteBadgeCriteria.prototype.deleteItemRow = function(eventTarget) {
        var request = Ajax.call([{
            methodname: 'local_ultimate_deletebadgecriteria',
            args: {
                badgecriteria: {
                    id: eventTarget.data('id')
                }
            }
        }]);

        request[0].done(function() {
            this.removeLine(eventTarget);
        }.bind(this)).fail(function(error) {
            var message = error.message;

            if (!message) {
                message = error.error;
            }
            this.showToast('error', message);
        }.bind(this));
    };

    DeleteBadgeCriteria.prototype.removeLine = function(eventTarget) {
        var tableLine = eventTarget.closest('tr');

        tableLine.fadeOut("normal", function() {
            $(this).remove();
        });

        Notification.addNotification({
            message: STRINGS.SUCCESS,
            type: 'success'
        });
    };

    return {
        'init': function() {
            return new DeleteBadgeCriteria();
        }
    };
});