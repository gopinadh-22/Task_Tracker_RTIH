// Trim the title and block a spaces-only title before the form is sent.
// The server checks this again, so this is only for a faster message.
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('task-form');
    if (!form) { return; }

    var title = form.elements['title'];

    form.addEventListener('submit', function (event) {
        title.value = title.value.trim();
        if (title.value === '') {
            title.setCustomValidity('Title is required.');
            title.reportValidity();
            event.preventDefault();
        }
    });

    title.addEventListener('input', function () {
        title.setCustomValidity('');
    });
});