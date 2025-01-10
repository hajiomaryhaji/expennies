import { getAjaxRequest, postAjaxRequest, patchAjaxRequest } from './ajax';


document.querySelector('#update-profile-btn').addEventListener('click', function () {
    const form = this.closest('form');
    const formData = new FormData(form);
    const inputs = Object.fromEntries(formData.entries());
    const userId = form.getAttribute('data-id');

    patchAjaxRequest(`/profile/${userId}/update`, inputs, form)
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Failed to save changes');
            }
        })
        .then(data => {
            // Show success message
            showNotification('#div-profile-btn', 'Saved!', 'success');
            fetchProfileInfo(); // Refresh profile info
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('#div-profile-btn', 'Failed!', 'error');
        });
});

function fetchProfileInfo() {
    const profileForm = document.querySelector('#profile-update-form');
    const userId = profileForm.getAttribute('data-id');

    getAjaxRequest(`/profile/${userId}/show`)
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Failed to fetch profile information');
            }
        })
        .then(data => {
            if (data) {
                const name = data.name;
                const email = data.email;

                
                profileForm.querySelector('input[name="name"]').value = name;
                profileForm.querySelector('input[name="email"]').value = email;

                document.querySelector('#profile-name').innerHTML = name;
                document.querySelector('#profile-email').innerHTML = email;
            }
        })
        .catch(error => {
            console.error('Error during GET:', error);
        });
}

fetchProfileInfo();

document.querySelector('#update-password-btn').addEventListener('click', function () {
    const form = this.closest('form');
    const formData = new FormData(form);
    const inputs = Object.fromEntries(formData.entries());
    const userId = form.getAttribute('data-id');

    patchAjaxRequest(`/profile/${userId}/update-password`, inputs, form)
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Failed to save changes');
            }
        })
        .then(data => {
            showNotification('#div-password-btn', 'Saved!', 'success');
        })
        .catch(error => {
            showNotification('#div-password-btn', 'Failed!', 'error');
        });
});

// Function to show a notification
function showNotification(notify, message, type) {
    const notification = document.createElement('strong');
    notification.textContent = message;

    var classNames = '';

    if (type === 'success') {
        classNames = 'text-green-500 text-sm';
    } else {
        classNames = 'text-red-500 text-sm'
    }
    notification.classList.add(...classNames.split(' '))
    document.querySelector(notify).appendChild(notification);

    // Automatically remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}




// START: Two Factor Authenication
document.querySelector('#two-factor-btn').addEventListener('click', function (event) {
    const twoFactorBtn = event.target.closest('#two-factor-btn');
    const userId = twoFactorBtn.getAttribute('data-id');

    postAjaxRequest(`/profile/${ userId }/2fa`, {check: event.target.checked}).then(response => {
        if (response.ok) {
            console.log(response.statusText)
        }
    })
});

// Fetch the initial state on page load
function fetchTwoFactorStatus() {
    const twoFactorBtn = document.querySelector('#two-factor-btn');
    const userId = twoFactorBtn.getAttribute('data-id');

    getAjaxRequest(`/profile/${userId}/2fa`).then(response => {
        if (response.ok) {
            response.json().then(data => {
                if (data.enabled) {
                    twoFactorBtn.checked = true;
                } else {
                    twoFactorBtn.checked = false;
                }
            });
        } else {
            console.error('Failed to fetch 2FA status');
        }
    }).catch(error => {
        console.error('Error during GET:', error);
    });
}

// Call this function on page load
fetchTwoFactorStatus();

// END: Two Factor Authentication