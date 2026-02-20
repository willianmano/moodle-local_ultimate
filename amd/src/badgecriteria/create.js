import ModalEvents from 'core/modal_events';
import ModalSaveCancel from 'core/modal_save_cancel';
import Fragment from 'core/fragment';
import {getString} from 'core/str';
import Ajax from 'core/ajax';
import $ from 'jquery';
import * as Notification from 'core/notification';

const SELECTORS = {
    TRIGGER: '[data-action="open-criteria-modal"]',
};

let courseid = 0;
let badgeid = 0;
let contextid = 0;
let modal = null;

export const init = (course, badge, context) => {
    courseid = course;
    badgeid = badge;
    contextid = context;

    registerListenerEvents();
};

const registerListenerEvents = () => {
    document.addEventListener('click', (e) => {
        const preview = e.target.closest(SELECTORS.TRIGGER);
        if (preview) {
            buildModal(preview).catch(Notification.exception);
        }
    });
};

const buildModal = async(element) => {
    // This string can be long. We will fetch it with JS as opposed to passing it as an attribute.
    let title = await getString('createbadgecriteria', 'local_ultimate');

    // Prepare data for modal.
    const data = {
        criteria: element.getAttribute('data-criteria'),
        courseid: courseid,
        badgeid: badgeid,
    };

    // Determine which modal template we should use.
    let modalTemplate = ModalSaveCancel;

    modal = await modalTemplate.create({
        title: title,
        body: getBody(data),
        show: true,
    });

    // We catch the modal save event, and use it to submit the form inside the modal.
    // Triggering a form submission will give JS validation scripts a chance to check for errors.
    modal.getRoot().on(ModalEvents.save, (e) => submitFormAjax(e));
    // We also catch the form submit event and use it to submit the form with ajax.
    modal.getRoot().on('submit', 'form', (e) => submitFormAjax(e));
};

const submitFormAjax = async(e) => {
    e.preventDefault();

    var formData = modal.getRoot().find('form').serialize();

    Ajax.call([{
        methodname: 'local_ultimate_createbadgecriteria',
        args: {contextid: contextid, courseid: courseid, jsonformdata: JSON.stringify(formData)},
        done: handleFormSubmissionResponse.bind(this),
        fail: handleFormSubmissionFailure.bind(this, formData)
    }]);
};

const handleFormSubmissionFailure = data => {
    // Oh noes! Epic fail :(
    // Ah wait - this is normal. We need to re-display the form with errors!
    modal.setBody(getBody(data));
};

const handleFormSubmissionResponse = (data) => {
    modal.destroy();

    var item = JSON.parse(data.data);

    var target = item.target;
    if (typeof item.target === 'undefined') {
        target = '';
    }

    var tableLine = $('<tr>' +
        '<th scope="row">' + item.id + '</th>' +
        '<td>' + item.method + '</td>' +
        '<td>' + target + '</td>' +
        '<td>' + item.value + '</td>' +
        '<td style="width: 120px; text-align: center;">' +
        '<a href="#" data-id="' + item.id + '" class="btn btn-danger btn-sm delete-ultimate-badgecriteria">' +
        '<i class="fa fa-trash-o"></i>' +
        '</a> ' +
        '</td>' +
        '</tr>');

    tableLine
        .appendTo('.table-ultimate-badgecriterias tbody')
        .hide().fadeIn('normal');

    Notification.addNotification({
        message: data.message,
        type: "success"
    });
};

const getBody = async(formdata) => {
    if (typeof formdata === "undefined") {
        formdata = {};
    }

    // Get the content of the modal.
    const params = {jsonformdata: JSON.stringify(formdata)};

    return Fragment.loadFragment('local_ultimate', 'badgecriteria_form', contextid, params);
};