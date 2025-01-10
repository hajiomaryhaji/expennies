const ajax = (url, method = 'GET', data = {}, domElement = null) => {
    method = method.toLowerCase()

    let options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }

    const csrfMethods = new Set(['post', 'put', 'delete', 'patch'])

    if (csrfMethods.has(method)) {
        var additionalFields = { ...getCsrfFields() };
    
        if (method !== 'post') {
            options.method = 'post';

            additionalFields._METHOD = method.toUpperCase();
        }
    
        if (data instanceof FormData) {
            for (let additionalField in additionalFields) {
                data.append(additionalField, additionalFields[additionalField]);
            }
    
            // Remove the Content-Type header to allow FormData to set it automatically
            delete options.headers['Content-Type'];

            options.body = data;

        } else {
            options.body = JSON.stringify({ ...data, ...additionalFields });
        }
    } else if (method === 'get') {
        url += '?' + new URLSearchParams(data).toString();
    }

    return fetch(url, options).then(response => {
        if (domElement) {
            clearFormValidationErrors(domElement)
        }

        if(! response.ok) {
            if (response.status === 422) {
                response.json().then(errors => {
                    handleFormValidationErrors(errors, domElement)
                })
            } else if(response.status === 404) {
                alert(response.statusText)
            }
        }

        return response
    })
}

const getAjaxRequest = (url, data) => ajax(url, 'get', data)
const postAjaxRequest = (url, data, domElement) => ajax(url, 'post', data, domElement)
const patchAjaxRequest = (url, data, domElement) => ajax(url, 'patch', data, domElement)
const deleteAjaxRequest = (url, data) => ajax(url, 'delete', data)

function handleFormValidationErrors(errors, domElement)
{
    for (const name in errors) {
        const labelElement = domElement.querySelector(`label[for="${name}"]`)
        const inputElement = domElement.querySelector(`[name="${name}"]`)

        
        labelElement?.classList.add('is-invalid-label')
        inputElement.classList.add('is-invalid-input')
    
       
        const errorStrongElement = document.createElement('strong')
        errorStrongElement.classList.add(...'text-red-500 text-xs mt-4'.split(' '))
        errorStrongElement.classList.add('invalid-feedback')
        errorStrongElement.textContent = errors[name][0]

        inputElement?.insertAdjacentElement('afterend', errorStrongElement);
        
    }
}

function clearFormValidationErrors(domElement)
{
    domElement.querySelectorAll('.is-invalid-input').forEach(function (element) {
        if (element.classList.contains('is-invalid-input')) {
            element.classList.remove('is-invalid-input');
        } else {
            console.warn('Element does not have is-invalid-input class:', element);
        }
    
        // Find invalid feedback elements
        const feedbackElements = element.parentNode.querySelectorAll('.invalid-feedback');
        
        // Remove each invalid feedback element
        feedbackElements.forEach(function (feedbackElement) {
            feedbackElement.remove();
        });
    });
        
    domElement.querySelectorAll('.is-invalid-label').forEach(function (element) {
        if (element.classList.contains('is-invalid-label')) {
            element.classList.remove('is-invalid-label');
        } else {
            console.warn('Element does not have is-invalid-label class:', element);
        }
    })
}

function getCsrfFields()
{
    const csrfNameField = document.querySelector('#csrfName');
    const csrfValueField = document.querySelector('#csrfValue');

    const csrfNameKey = csrfNameField.getAttribute('name');
    const csrfNameContent = csrfNameField.content;

    const csrfValueKey = csrfValueField.getAttribute('name');
    const csrfValueContent = csrfValueField.content;

    return {
        [csrfNameKey]: csrfNameContent,
        [csrfValueKey]: csrfValueContent
    };
}

export {
    ajax,
    getAjaxRequest,
    postAjaxRequest,
    patchAjaxRequest,
    deleteAjaxRequest,
    clearFormValidationErrors
}