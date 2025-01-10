import { postAjaxRequest } from './ajax';

const resendEmailVerificationLinkBtn =  document.querySelector('#resendVerificationBtn')

resendEmailVerificationLinkBtn.addEventListener('click', function () {
    postAjaxRequest(`/resendEmailVerification`).then(response => {
        if (response.ok) {
            resendEmailVerificationLinkBtn.innerHTML = 'Activation Link already Sent';
            resendEmailVerificationLinkBtn.setAttribute('disabled', true);
        } else {
            alert('Activation Link not sent');
        }
    })
})