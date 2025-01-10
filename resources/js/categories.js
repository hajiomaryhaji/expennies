import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import { getAjaxRequest, postAjaxRequest, patchAjaxRequest, deleteAjaxRequest, clearFormValidationErrors} from './ajax';

// Ensure the DOM is fully loaded
window.addEventListener('DOMContentLoaded', function () {
    const createCategoryModalElement = document.getElementById('createCategoryModal');
    const editCategoryModalElement = document.getElementById('editCategoryModal');
    
    const categoriesTable = new DataTable('#categoriesTable', {
        responsive: true,
        serverSide: true,
        ajax: '/categories/load',
        orderMulti: false,
        lengthMenu: [5, 10, 25, 50, 100],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        order:{ 
            name: "updatedAt",
            dir: "desc"
        },
        columns: [
            { data: "name" },
            {data: row => `<span class="text-xs sm:text-sm">${ row.createdAt }</span>`},
            {data: row => `<span class="text-xs sm:text-sm">${ row.updatedAt }</span>`},
            {
                sortable: false,
                data: row => `
                    <div class="flex gap-4 items-center">
                        <button data-id="${row.id}" class="edit-category-btn"><svg class="h-5 w-5 text-blue-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                        <button data-id="${row.id}" class="delete-category-btn"><svg class="h-5 w-5 text-red-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </div>
                `
            }
        ]
    });

    const select = document.querySelector('.dt-length label select.dt-input');

    if (select) {
        select.classList.add(...'w-14 pr-3'.split(' '))
    }

    const openCreateCategoryModalButton = document.querySelector('.openCreateCategoryModalButton'); // Adjust the selector based on your button
    const closeCreateCategoryModalButton = createCategoryModalElement.querySelector('.closeCreateCategoryModalButton'); // Update selector if needed

    // Open modal when clicking the "Add Category" button
    if (openCreateCategoryModalButton) {
        openCreateCategoryModalButton.addEventListener('click', function () {
            openModal(createCategoryModalElement);
        });
    }

    // Close modal button functionality
    if (closeCreateCategoryModalButton) {
        closeCreateCategoryModalButton.addEventListener('click', function () {
            closeModal(createCategoryModalElement);
        });
    }

    // Handle the form submission for the "Add Category" modal
    createCategoryModalElement.querySelector('form').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission
        const formData = new FormData(this);
        
        postAjaxRequest('/categories', Object.fromEntries(formData.entries()), createCategoryModalElement)
            .then(response => {
                if (response.ok) {
                    categoriesTable.draw(); // Refresh the DataTable
                    closeModal(createCategoryModalElement); // Close the modal
                } else {
                    console.error('Failed to create category.');
                }
            })
            .catch(error => console.error(error));
    });


    // Open modal when clicking the edit button
    document.querySelector('#categoriesTable').addEventListener('click', function (event) {
        const editBtn = event.target.closest('.edit-category-btn');
        const deleteBtn = event.target.closest('.delete-category-btn');

        if (editBtn) {
            const categoryId = editBtn.getAttribute('data-id');

            getAjaxRequest(`/categories/${categoryId}`)
            .then(response =>response.json())
            .then(response => openEditCategoryModal(editCategoryModalElement, response))
        } else if (deleteBtn) {
            const categoryId = deleteBtn.getAttribute('data-id');

            if (confirm('Are you sure you want to delete this category?')) {
                deleteAjaxRequest(`/categories/${categoryId}`).then(() => {
                    categoriesTable.draw()
                });
            }
        }
    });

    // Handle the "Save" button click
    document.querySelector('.save-category-btn').addEventListener('click', function () {
        const categoryId = this.getAttribute('data-id');

        patchAjaxRequest(`/categories/${categoryId}`, {
            name: editCategoryModalElement.querySelector('input[name="name"]').value
        }, editCategoryModalElement).then(response => {
            if (response.ok) {
                categoriesTable.draw();
                closeModal(editCategoryModalElement); // Close the modal after save
            }
        });
    });

    // Close modal button functionality
    const closeButton = editCategoryModalElement.querySelector('.closeEditCategoryModal');
    if (closeButton) {
        closeButton.addEventListener('click', function () {
            closeModal(editCategoryModalElement);
        });
    }
});

// Function to populate and show the modal
function openEditCategoryModal(modalElement, { id, name }) {
    const nameInput = modalElement.querySelector('input[name="name"]');
    if (nameInput) {
        nameInput.value = name;
    } else {
        console.error('Name input field not found in modal.');
    }

    const saveButton = modalElement.querySelector('.save-category-btn');
    if (saveButton) {
        saveButton.setAttribute('data-id', id);
    } else {
        console.error('Save button not found in modal.');
    }

    openModal(modalElement);
}

function openModal(modalElement) {
    modalElement.classList.add('flex');
    modalElement.classList.remove('hidden'); // Show modal by removing 'hidden' class
    modalElement.setAttribute('aria-hidden', 'false'); // Set aria-hidden to 'false'
    document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
}

function closeModal(modalElement) {
    const nameInput = modalElement.querySelector('input[name="name"]');

    nameInput.value = '';

    modalElement.classList.add('hidden'); // Hide modal by adding 'hidden' class
    modalElement.setAttribute('aria-hidden', 'true'); // Set aria-hidden to 'true'
    document.body.style.overflow = 'auto'; // Allow scrolling when modal is closed


    clearFormValidationErrors(modalElement)
}