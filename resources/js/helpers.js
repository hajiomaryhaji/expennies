function openModal(modalElement) {
    modalElement.classList.add('flex');
    modalElement.classList.remove('hidden'); // Show modal by removing 'hidden' class
    modalElement.setAttribute('aria-hidden', 'false'); // Set aria-hidden to 'false'
    document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
}

function closeModal(modalElement) {
    modalElement.classList.add('hidden'); // Hide modal by adding 'hidden' class
    modalElement.setAttribute('aria-hidden', 'true'); // Set aria-hidden to 'true'
    document.body.style.overflow = 'auto'; // Allow scrolling when modal is closed

    clearFormValidationErrors(modalElement)

    // Reset all input fields within the modal
    const inputs = modalElement.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        if (input.type === 'checkbox' || input.type === 'radio') {
            input.checked = false; // Reset checkboxes and radios
        } else if (input.type === 'file') {
            input.value = ''; // Clear file inputs
        } else {
            input.value = ''; // Reset text, number, date, etc.
        }
    });
}

export {
    openModal,
    closeModal
}