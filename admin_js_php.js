// Admin token
let adminToken = null;

// --- Helper Functions ---

function initializeTheme() {
    const savedTheme = localStorage.getItem('batchbinder-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.body.classList.add('dark-mode');
    }
}

function showMessage(elementId, message, isError = false) {
    const messageEl = document.getElementById(elementId);
    messageEl.textContent = message;
    messageEl.className = isError ? 'message error-message' : 'message success-message';
    messageEl.style.display = 'block';
    if (!isError) {
        setTimeout(() => hideMessage(elementId), 5000);
    }
}

function hideMessage(elementId) {
    const messageEl = document.getElementById(elementId);
    if (messageEl) messageEl.style.display = 'none';
}

function checkAuth() {
    if (!adminToken) {
        alert('Authentication error. Please login again.');
        return false;
    }
    return true;
}

function goBack() {
    // Hide all forms
    document.getElementById('uploadForm').style.display = 'none';
    document.getElementById('editForm').style.display = 'none';
    document.getElementById('deleteForm').style.display = 'none';
    // Show main menu
    document.getElementById('operationMenu').style.display = 'block';
    // Clear any leftover messages
    hideMessage('uploadMessage');
    hideMessage('editMessage');
    hideMessage('deleteMessage');
    // Reset forms
    document.getElementById('notesUploadForm').reset();
    document.getElementById('editNotesForm').reset();
}

// --- Main Application Logic ---

async function validateAdmin() {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const loginButton = document.querySelector('#loginForm .btn-primary');

    if (!email || !password) {
        return showMessage('loginMessage', 'Please enter both email and password', true);
    }

    loginButton.textContent = 'Logging in...';
    loginButton.disabled = true;

    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await response.json();

        // This check works with the corrected login_api.php
        if (response.ok && data.success) {
            adminToken = data.token;
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('operationMenu').style.display = 'block';
            hideMessage('loginMessage');
        } else {
            showMessage('loginMessage', data.error || 'Invalid credentials', true);
        }
    } catch (error) {
        showMessage('loginMessage', 'Error connecting to server.', true);
    } finally {
        loginButton.textContent = 'Login';
        loginButton.disabled = false;
    }
}

function showForm(operation) {
    document.getElementById('operationMenu').style.display = 'none';
    hideMessage('uploadMessage');
    hideMessage('editMessage');
    hideMessage('deleteMessage');

    switch (operation) {
        case 'add':
            document.getElementById('uploadForm').style.display = 'block';
            break;
        case 'edit':
            document.getElementById('editForm').style.display = 'block';
            loadContentForSelect('edit');
            break;
        case 'delete':
            document.getElementById('deleteForm').style.display = 'block';
            loadContentForSelect('delete');
            break;
    }
}

// Universal function to load content for Edit/Delete dropdowns
async function loadContentForSelect(mode) {
    const contentType = document.getElementById(`${mode}ContentType`).value;
    const dept = document.getElementById(`${mode}Dept`).value;
    const sem = document.getElementById(`${mode}Sem`).value;
    const selectElement = document.getElementById(mode === 'edit' ? 'noteSelect' : 'deleteNoteSelect');

    selectElement.innerHTML = '<option value="">Loading...</option>';
    
    try {
        const params = new URLSearchParams({ contentType, department: dept, semester: sem });
        const response = await fetch(`api/content?${params.toString()}`);
        const result = await response.json();

        selectElement.innerHTML = ''; // Clear loading text
        
        if (response.ok && result.success && result.data.length > 0) {
            result.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item._id;
                option.textContent = item.contentType === 'exclusive' ? item.title : `${item.subject} - ${item.topic}`;
                selectElement.appendChild(option);
            });
            // If in edit mode, load details for the first item
            if (mode === 'edit') {
                loadNoteDetailsForEdit(result.data[0]._id);
            }
        } else {
            selectElement.innerHTML = '<option value="">No content found</option>';
        }
    } catch (error) {
        selectElement.innerHTML = '<option value="">Error loading content</option>';
    }
}

// Populate edit form with details of the selected note
async function loadNoteDetailsForEdit(id) {
    try {
        const response = await fetch(`api/content/${id}`);
        const result = await response.json();

        if (response.ok && result.success) {
            const item = result.data;
            if (item.contentType === 'exclusive') {
                document.getElementById('editExclusiveTitle').value = item.title || '';
                document.getElementById('editExclusiveDesc').value = item.description || '';
                document.getElementById('editExclusivePrice').value = (item.price || '0').replace('₹', '');
                document.getElementById('editExclusiveQuote').value = item.quote || '';
            } else {
                document.getElementById('editSubject').value = item.subject || '';
                document.getElementById('editTopic').value = item.topic || '';
                document.getElementById('editProfessor').value = item.professor || '';
            }
        }
    } catch (error) {
        console.error('Error loading note details:', error);
    }
}

// --- DOM Event Listeners ---

document.addEventListener('DOMContentLoaded', function() {
    
    // Login form submission on Enter key
    document.getElementById('password').addEventListener('keypress', e => e.key === 'Enter' && validateAdmin());
    document.getElementById('email').addEventListener('keypress', e => e.key === 'Enter' && validateAdmin());

    // Upload form submission
    document.getElementById('notesUploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!checkAuth()) return;
        
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.textContent = 'Uploading...';
        submitButton.disabled = true;

        try {
            const formData = new FormData(this); // More efficient way to get form data
            formData.append('contentType', document.getElementById('contentType').value);

            const response = await fetch('api/content.php', {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${adminToken}` },
                body: formData
            });

            const result = await response.json();
            if (response.ok && result.success) {
                showMessage('uploadMessage', 'Content uploaded successfully!', false);
                setTimeout(goBack, 2000);
            } else {
                showMessage('uploadMessage', result.error || 'Upload failed.', true);
            }
        } catch (error) {
            showMessage('uploadMessage', 'Error connecting to server.', true);
        } finally {
            submitButton.textContent = 'Upload Content';
            submitButton.disabled = false;
        }
    });

    // Edit form submission
    document.getElementById('editNotesForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!checkAuth()) return;
        
        const id = document.getElementById('noteSelect').value;
        if (!id) return showMessage('editMessage', 'Please select content to edit', true);
        
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.textContent = 'Updating...';
        submitButton.disabled = true;

        const contentType = document.getElementById('editContentType').value;
        const updatedData = { contentType };
        if (contentType === 'exclusive') {
            updatedData.title = document.getElementById('editExclusiveTitle').value;
            updatedData.description = document.getElementById('editExclusiveDesc').value;
            updatedData.price = document.getElementById('editExclusivePrice').value;
            updatedData.quote = document.getElementById('editExclusiveQuote').value;
        } else {
            updatedData.subject = document.getElementById('editSubject').value;
            updatedData.topic = document.getElementById('editTopic').value;
            updatedData.professor = document.getElementById('editProfessor').value;
        }
        
        try {
            // CORRECTED: Using 'PUT' method and the correct RESTful API endpoint.
            const response = await fetch(`api/content/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${adminToken}`
                },
                body: JSON.stringify(updatedData)
            });

            const result = await response.json();
            if (response.ok && result.success) {
                showMessage('editMessage', 'Content updated successfully!', false);
                setTimeout(goBack, 2000);
            } else {
                showMessage('editMessage', result.error || 'Update failed.', true);
            }
        } catch (error) {
            showMessage('editMessage', 'Error connecting to server.', true);
        } finally {
            submitButton.textContent = 'Update Content';
            submitButton.disabled = false;
        }
    });

    // Delete button click
    document.querySelector('#deleteForm .btn-primary').addEventListener('click', async function() {
        if (!checkAuth()) return;
        
        const id = document.getElementById('deleteNoteSelect').value;
        if (!id) return showMessage('deleteMessage', 'Please select content to delete', true);
        if (!confirm('Are you sure you want to delete this content?')) return;
        
        this.textContent = 'Deleting...';
        this.disabled = true;

        try {
            // CORRECTED: Using 'DELETE' method and the correct RESTful API endpoint.
            const response = await fetch(`api/content/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${adminToken}` }
            });
            const result = await response.json();

            if (response.ok && result.success) {
                showMessage('deleteMessage', 'Content deleted successfully.', false);
                loadContentForSelect('delete'); // Refresh the list
            } else {
                showMessage('deleteMessage', result.error || 'Delete failed.', true);
            }
        } catch (error) {
            showMessage('deleteMessage', 'Error connecting to server.', true);
        } finally {
            this.textContent = 'Delete Content';
            this.disabled = false;
        }
    });

    // Event listeners for dropdowns in Edit/Delete forms
    ['edit', 'delete'].forEach(mode => {
        document.getElementById(`${mode}ContentType`).addEventListener('change', () => loadContentForSelect(mode));
        document.getElementById(`${mode}Dept`).addEventListener('change', () => loadContentForSelect(mode));
        document.getElementById(`${mode}Sem`).addEventListener('change', () => loadContentForSelect(mode));
    });

    // Populate edit form when a new note is selected
    document.getElementById('noteSelect').addEventListener('change', e => loadNoteDetailsForEdit(e.target.value));

    initializeTheme();
});