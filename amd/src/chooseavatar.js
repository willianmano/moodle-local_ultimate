/**
 * Choose avatar js logic.
 *
 * @copyright   2022 Willian Mano {@link https://conecti.me}
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

define([
        'jquery',
        'core/config',
        'core/str',
        'core/modal',
        'core/modal_events',
        'core/fragment',
        'core/ajax',
        'local_ultimate/sweetalert'],
    function($, Config, Str, Modal, ModalEvents, Fragment, Ajax, Swal) {

        var ChooseAvatar = function(selector, contextid) {
            this.contextid = contextid;

            this.init(selector);
        };

        ChooseAvatar.prototype.modal = null;

        ChooseAvatar.prototype.contextid = -1;

        ChooseAvatar.prototype.init = function(selector) {
            var triggers = $(selector);

            return Str.get_string('chooseavatar', 'local_ultimate').then(function(title) {
                // Create the modal using the current default modal type.
                return Modal.create({
                    title: title,
                    body: this.getBody(),
                    large: true
                });
            }.bind(this)).then(function(modal) {
                // Keep a reference to the modal.
                this.modal = modal;

                // We want to reset the form every time it is opened.
                this.modal.getRoot().on(ModalEvents.hidden, function() {
                    this.modal.setBody(this.getBody());
                }.bind(this));

                // We want to hide the submit buttons every time it is opened.
                this.modal.getRoot().on(ModalEvents.shown, function() {
                    this.modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
                }.bind(this));

                // We also catch the form submit event and use it to submit the form with ajax.
                this.modal.getRoot().on('click', '.chooseavatar-form img.avatar', this.avatarSelected.bind(this));

                // ModalFactory used to show the modal automatically when a trigger was clicked,
                // core/modal requires us to wire that up ourselves.
                triggers.on('click', function(e) {
                    e.preventDefault();

                    this.modal.show();
                }.bind(this));

                return this.modal;
            }.bind(this));
        };

        ChooseAvatar.prototype.getBody = function(formdata) {
            if (typeof formdata === "undefined") {
                formdata = {};
            }

            // Get the content of the modal.
            var params = {jsonformdata: JSON.stringify(formdata)};

            return Fragment.loadFragment('local_ultimate', 'chooseavatar_form', this.contextid, params);
        };

        ChooseAvatar.prototype.handleFormSubmissionResponse = function(data) {
            this.modal.hide();

            var item = JSON.parse(data.data);

            var useravatar = $('#ultimate-user-avatar');
            useravatar.attr('src', item);

            var navuserimg = $('.usermenu .userpicture');
            navuserimg.attr('src', item);

            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 8000,
                timerProgressBar: true,
                onOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            Toast.fire({
                icon: 'success',
                title: data.message
            });
        };

        ChooseAvatar.prototype.handleFormSubmissionFailure = function() {
            // Oh noes! Epic fail :(
            // Ah wait - this is normal. We need to re-display the form with errors!
            this.modal.setBody(this.getBody());
        };

        ChooseAvatar.prototype.avatarSelected = function(e) {
            // We don't want to do a real form submission.
            e.preventDefault();

            var avatarid = $(e.currentTarget).data('id');

            Ajax.call([{
                methodname: 'local_ultimate_chooseavatar',
                args: {contextid: this.contextid, avatarid: avatarid},
                done: this.handleFormSubmissionResponse.bind(this),
                fail: this.handleFormSubmissionFailure.bind(this)
            }]);
        };

        return {
            init: function(selector, contextid) {
                return new ChooseAvatar(selector, contextid);
            }
        };
    }
);
