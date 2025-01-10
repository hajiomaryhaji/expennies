import { postAjaxRequest } from './ajax';
import { openModal } from './helpers';

const twoFactorAuthenticationModalElement = document.getElementById('twoFactorAuthenticationModal');

document.querySelector('#login').addEventListener('click', function () {
        const form = this.closest('form');
        const formData = new FormData(form);
        const inputs = Object.fromEntries(formData.entries());
        
        postAjaxRequest('/authenticate', inputs, form).then(response => response.json())
            .then(response => {
                if (response.two_factor_authentication) {
                    openModal(twoFactorAuthenticationModalElement); 
                } else {
                    window.location = '/'
                }
        })
        .catch(error => console.error(error));
});

document.querySelector('#login-2fa').addEventListener('click', function () {
    const code = twoFactorAuthenticationModalElement.querySelector('input[name="code"]').value;
    const email = document.querySelector('#loginForm input[name="email"]').value;

    postAjaxRequest('authenticate/2fa', { code, email }, twoFactorAuthenticationModalElement)
        .then(response => {
            if (response.ok) {
                window.location = '/'
            }
        })

});


