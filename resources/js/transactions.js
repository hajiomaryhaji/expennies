import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import { getAjaxRequest, postAjaxRequest, patchAjaxRequest, deleteAjaxRequest } from './ajax';
import { openModal, closeModal } from './helpers';

// Ensure the DOM is fully loaded
window.addEventListener('DOMContentLoaded', function () {
    const createTransactionModalElement = document.getElementById('createTransactionModal');
    const importTransactionsModalElement = document.getElementById('importTransactionsModal');
    const editTransactionModalElement = document.getElementById('editTransactionModal');
    const uploadTransactionReceiptModalElement = this.document.getElementById('uploadTransactionReceiptModal');
    
    const transactionsTable = new DataTable('#transactionsTable', {
        responsive: true,
        serverSide: true,
        processing: true,
        ajax: '/transactions/load',
        orderMulti: false,
        rowCallback: (row, data) => {
            if (! data.wasReviewed) {
                row.classList.add('font-bold')
            }

            row.classList.add(...'mb-2 lg:mb-0'.split(' '))

            return row
        },
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
        columns: [
            { data: "description" },
            {
                data: 'amount',
                render:data => {
                        const amount = new Intl.NumberFormat(
                        'en-TZ',
                        {
                            style: 'currency',
                            currency: 'TZS',
                            currencySign: 'accounting'
                        }
                        ).format(data)

                    return `<span class="${ data > 0 ? 'text-green-500' : '' }">${ amount }</span>`
                }
            },
            {data: row => `<span class="text-xs sm:text-sm">${ row.category }</span>`},
            {
                sortable: false,
                data: row => {
                    let iconsDiv = document.createElement('div');
                    iconsDiv.classList.add(...'mt-2 md:mt-0 inline-flex justify-start gap-2'.split(' '));

                    for(let i = 0; i < row.receipts.length; i++) {
                        const receipt = row.receipts[i]

                        const div = document.createElement('div')

                        const span = document.createElement('span')

                        const anchor = document.createElement('a')

                        const viewReceiptIconMarkup = `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M14 3v4a1 1 0 0 0 1 1h4" />  <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />  <line x1="9" y1="9" x2="10" y2="9" />  <line x1="9" y1="13" x2="15" y2="13" />  <line x1="9" y1="17" x2="15" y2="17" /></svg>`;
                        
                        const deleteReceiptIconMarkup = `<svg xmlns="http://www.w3.org/2000/svg" data-id="${receipt.id}" data-transactionid="${row.id}" class="delete-receipt-btn h-4 w-4 text-red-500 absolute top-0 right-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
                        
                        span.classList.add(...'relative inline-block w-10'.split(' '))

                        anchor.innerHTML = viewReceiptIconMarkup;

                        anchor.href = `/transactions/${ row.id }/receipts/${ receipt.id }`
                        anchor.target = '_blank'
                        anchor.title = receipt.name

                        span.append(anchor)

                        const parser = new DOMParser();
                        const svgNode = parser.parseFromString(deleteReceiptIconMarkup, 'image/svg+xml').documentElement;

                        // Ensure proper namespace and append deleteIconNode to span
                        if (svgNode instanceof SVGElement) {
                            span.appendChild(svgNode);
                        } else {
                            console.error('Failed to parse the deleteReceiptIconMarkup as SVG.');
                        }
                        
                        div.classList.add(...'mt-2 md:mt-0 flex gap-4'.split(' '))

                        iconsDiv.append(span)
                    }

                    return iconsDiv.outerHTML;
                }
            },
            { data: "date" },   
            {
                sortable: false,
                data: row => `              
                        <div class="flex justify-start items-center gap-x-5 mt-2 lg:mt-0">
                            <div>
                                <button class="toggle-review-btn md:h-8 md:w-8 inline-flex items-center" data-id="${ row.id }">
                                    <svg class="md:w-full md:h-full ${ row.wasReviewed ? 'text-green-500' : 'text-black' }"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <circle cx="12" cy="12" r="9" />  <path d="M9 12l2 2l4 -4" /></svg>
                                    <span class="md:hidden">Review</span>
                                </button>
                            </div>
                            <div class="relative">
                                <button class="dropdown-toggle md:w-8 md:h-8 inline-flex items-center" type="button">
                                    <svg class="md:w-full md:h-full text-black"  width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">  <path stroke="none" d="M0 0h24v24H0z"/>  <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />  <circle cx="12" cy="12" r="3" /></svg>
                                    <span class="md:hidden">Routines</span>
                                </button>
                                <div class="dropdown-menu hidden absolute right-0 mt-2 bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:divide-gray-600 z-50">
                                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                                        <li>
                                            <button data-id="${row.id}" class="open-upload-receipt-modal-btn  flex items-center gap-x-2 px-4 py-2"><svg class="h-5 w-5 text-green-500"  fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg><span class="text-green-600">Upload Receipt</span></button>
                                        </li>
                                        <li>
                                            <button data-id="${row.id}" class="edit-transaction-btn flex items-center gap-x-2 px-4 py-2"><svg class="h-5 w-5 text-blue-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg><span class="text-blue-600">Edit</span></button>
                                        </li>
                                        <li>
                                            <button data-id="${row.id}" class="delete-transaction-btn  flex items-center gap-x-2 px-4 py-2"><svg class="h-5 w-5 text-red-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg><span class="text-red-600">Delete</span></button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                `
            }
        ],
    });

    const select = document.querySelector('.dt-length label select.dt-input');

    if (select) {
        select.classList.add(...'w-16 pr-1'.split(' '))
    }

    document.addEventListener('click', (event) => {
        const target = event.target;
        const dropdownButton = target.closest('.dropdown-toggle');
        
        if (dropdownButton) {
            const dropdownMenu = dropdownButton.nextElementSibling;
            dropdownMenu.classList.toggle('hidden');
        } else {
            // Close any open dropdowns when clicking outside
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });    
    

    const openCreateTransactionModalButton = document.querySelector('.openCreateTransactionModalButton'); // Adjust the selector based on your button
    const closeCreateTransactionModalButton = createTransactionModalElement.querySelector('.closeCreateTransactionModalButton'); // Update selector if needed
    const openImportTransactionsModalButton = document.querySelector('.openImportTransactionsModalButton');
    const closeImportTransactionsModalButton = importTransactionsModalElement.querySelector('.closeImportTransactionsModalButton');
    
    
    // Open modal when clicking the "Add Transaction" button
    if (openCreateTransactionModalButton) {
        openCreateTransactionModalButton.addEventListener('click', function () {
            openModal(createTransactionModalElement);
        });
    }

    // Open modal when clicking the "Import Transactions" button
    if (openImportTransactionsModalButton) {
        openImportTransactionsModalButton.addEventListener('click', function () {
            openModal(importTransactionsModalElement);
        });
    }

    // Close modal button functionality
    if (closeCreateTransactionModalButton) {
        closeCreateTransactionModalButton.addEventListener('click', function () {
            closeModal(createTransactionModalElement);
        });
    }

    // Close modal button functionality
    if (closeImportTransactionsModalButton) {
        closeImportTransactionsModalButton.addEventListener('click', function () {
            closeModal(importTransactionsModalElement);
        });
    }

    // Handle the form submission for the "Add Transaction" modal
    createTransactionModalElement.querySelector('form').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission
        const formData = new FormData(this);
        
        postAjaxRequest('/transactions', Object.fromEntries(formData.entries()), createTransactionModalElement)
            .then(response => {
                if (response.ok) {
                    transactionsTable.draw(); // Refresh the DataTable
                    closeModal(createTransactionModalElement); // Close the modal
                } else {
                    console.error('Failed to create transaction.');
                }
            })
            .catch(error => console.error(error));
    });

    // Handle a CSV file importing for the "Import Transactions" modal
    importTransactionsModalElement.querySelector('.import-transactions-btn').addEventListener('click', function (event) {
        const formData = new FormData();
        const button = event.currentTarget;
        const files = importTransactionsModalElement.querySelector('input[type="file"]').files;

            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    formData.append('importedCSVFile', files[i]);
                }
            } else {
                console.error('No files selected.');
            }

            button.setAttribute('disabled', true);

            button.innerHTML = `
                <div role="status">
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                    <span class="sr-only">Loading...</span>
                </div>
            `;
            
            postAjaxRequest('/transactions/import', formData, importTransactionsModalElement)
                .then(response => {
                    if (response.ok) {
                        transactionsTable.draw(); // Refresh the DataTable
                        closeModal(importTransactionsModalElement); // Close the modal
                        button.removeAttribute('disabled');
                        button.innerHTML = `
                            <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
									</svg>
									Import
                        `;

                    } else {
                        button.removeAttribute('disabled');
                        button.innerHTML = `
                            <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
									</svg>
									Import
                        `;
                    }
                })
                .catch(error => console.error(error));
    });


    // Open modal when clicking the edit button
    document.querySelector('#transactionsTable').addEventListener('click', function (event) {
        const editBtn = event.target.closest('.edit-transaction-btn');
        const deleteBtn = event.target.closest('.delete-transaction-btn');
        const uploadBtn = event.target.closest('.open-upload-receipt-modal-btn');
        const deleteReceiptBtn = event.target.closest('.delete-receipt-btn');
        const reviewBtn = event.target.closest('.toggle-review-btn');

        if (editBtn) {
            const transactionId = editBtn.getAttribute('data-id');

            getAjaxRequest(`/transactions/${transactionId}`)
            .then(response =>response.json())
            .then(response => openEditTransactionModal(editTransactionModalElement, response))
        } else if (deleteBtn) {
            const transactionId = deleteBtn.getAttribute('data-id');

            if (confirm('Are you sure you want to delete this transaction?')) {
                deleteAjaxRequest(`/transactions/${transactionId}`).then(() => {
                    transactionsTable.draw()
                });
            }
        } else if (uploadBtn) {
            const transactionId = uploadBtn.getAttribute('data-id');

            uploadTransactionReceiptModalElement.querySelector('.upload-receipt-btn').setAttribute('data-id', transactionId);

            openModal(uploadTransactionReceiptModalElement);
        } else if (deleteReceiptBtn) {
            const receiptId = deleteReceiptBtn.getAttribute('data-id');
            const transactionId = deleteReceiptBtn.getAttribute('data-transactionid');

            if (confirm('Are you sure you want to delete this transaction receipt?')) {
                deleteAjaxRequest(`/transactions/${transactionId}/receipts/${receiptId}`).then(() => {
                    transactionsTable.draw()
                });
            }
        } else if(reviewBtn) {
            const transactionId = reviewBtn.getAttribute('data-id');
            console.log(transactionId)

            postAjaxRequest(`/transactions/${ transactionId }/review`).then(response => {
                if (response.ok) {
                    transactionsTable.draw()
                }
            })
        }
    });

    // Handle the "Save" button click
    document.querySelector('.save-transaction-btn').addEventListener('click', function () {
        const transactionId = this.getAttribute('data-id');
        
        patchAjaxRequest(`/transactions/${transactionId}`, {
            description: editTransactionModalElement.querySelector('[name="description"]').value,
            amount: editTransactionModalElement.querySelector('[name="amount"]').value,
            date: editTransactionModalElement.querySelector('[name="date"]').value,
            category: editTransactionModalElement.querySelector('[name="category"]').value
        }, editTransactionModalElement).then(response => {
            if (response.ok) {
                transactionsTable.draw();
                closeModal(editTransactionModalElement); // Close the modal after save
            }
        });
    });

    // Handle the "Upload" button click
    document.querySelector('.upload-receipt-btn').addEventListener('click', function () {
        const transactionId = this.getAttribute('data-id');
        const formData = new FormData();
        const files = uploadTransactionReceiptModalElement.querySelector('input[type="file"]').files;

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                formData.append('receipt', files[i]);
            }
        } else {
            console.error('No files selected.');
        }
        
        postAjaxRequest(`/transactions/${transactionId}/receipt`, formData, uploadTransactionReceiptModalElement).then(response => {
            if (response.ok) {
                transactionsTable.draw();
                closeModal(uploadTransactionReceiptModalElement); // Close the modal after upload
            }
        });
    });

    // Close modal button functionality
    const closeEditTransactionModalButton = editTransactionModalElement.querySelector('.closeEditTransactionModal');
    if (closeEditTransactionModalButton) {
        closeEditTransactionModalButton.addEventListener('click', function () {
            closeModal(editTransactionModalElement);
        });
    }
    const closeUploadTransactionReceiptModalButton = uploadTransactionReceiptModalElement.querySelector('.closeUploadTransactionReceiptModal');
    if (closeUploadTransactionReceiptModalButton) {
        closeUploadTransactionReceiptModalButton.addEventListener('click', function () {
            closeModal(uploadTransactionReceiptModalElement);
        });
    }
});

// Function to populate and show the modal
function openEditTransactionModal(modalElement, { id, description, amount, category, date }) {
    const descriptionInput = modalElement.querySelector('[name="description"]');
    const amountInput = modalElement.querySelector('[name="amount"]');
    const categorySelect = modalElement.querySelector('select[name="category"]');
    const dateInput = modalElement.querySelector('[name="date"]');

    if (descriptionInput && amountInput && categorySelect && dateInput) {
        descriptionInput.value = description;
        amountInput.value = amount;
        if (categorySelect) {
            const categoryOption = categorySelect.querySelector(`option[value="${category}"]`);
            if (categoryOption) {
                categorySelect.value = category; // Set the correct value
            } else {
                console.warn(`No matching option found for category value: ${category}`);
            }
        } else {
            console.error('Category select field not found in modal.');
        }
        if (dateInput) {
            const formattedDate = new Date(date).toISOString().slice(0, 16); // Convert date to yyyy-MM-ddTHH:mm
            dateInput.value = formattedDate;
        } else {
            console.error('Date input field not found in modal.');
        }


    } else {
        console.error('Some input fields not found in modal.');
    }

    const saveButton = modalElement.querySelector('.save-transaction-btn');
    if (saveButton) {
        saveButton.setAttribute('data-id', id);
    } else {
        console.error('Save button not found in modal.');
    }

    openModal(modalElement);
}

