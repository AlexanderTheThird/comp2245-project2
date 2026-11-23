// ~~~~~ M4: delete confirmation box on the ticket page ~~~~~
// Shows the "Are you sure?" box when Delete is clicked, hides it on Cancel.
document.addEventListener('DOMContentLoaded', () => {
    const openButton = document.querySelector('#delete-open');
    const box = document.querySelector('#delete-confirm');
    const cancelButton = document.querySelector('#delete-cancel');


    if (!openButton || !box || !cancelButton) {
        return;
    }

    openButton.addEventListener('click', () => {
        box.hidden = false;
        cancelButton.focus();
    });

    cancelButton.addEventListener('click', () => {
        box.hidden = true;
        openButton.focus();
    });
});
// ~~~~~ end of M4 section ~~~~~
