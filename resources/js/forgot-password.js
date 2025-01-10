import { postAjaxRequest } from './ajax';

document.querySelector('#forgot-password').addEventListener('click', function () {
        const form = this.closest('form');
        const formData = new FormData(form);
        const inputs = Object.fromEntries(formData.entries());
        
        postAjaxRequest('/forgot-password', inputs, form)
        .then(response => {
            if (response.ok) {
                showNotification('Sent!', 'success');
            } else {
                showNotification('Failed!', 'error');
            }
        });
});

function showNotification(message, type) {
    const notification = document.createElement('strong');
    notification.textContent = message;

    var classNames = '';

    if (type === 'success') {
        classNames = 'text-green-500 text-center text-sm';
    } else {
        classNames = 'text-red-500 text-center text-sm'
    }
    notification.classList.add(...classNames.split(' '))
    document.querySelector('#div-btn').appendChild(notification);

    // Automatically remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}