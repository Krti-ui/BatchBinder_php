document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadInitialContent();
    setupMobileMenu();
});

// Event Listeners Setup
function setupEventListeners() {
    // Department dropdown change
    document.getElementById('deptDropdown').addEventListener('change', function() {
        loadSubjects();
        loadContent();
    });
    
    // Semester pills navigation
    document.querySelectorAll('.semester-pill').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.semester-pill').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            loadSubjects();
            loadContent();
        });
    });
    
    // Resource type navigation
    document.querySelectorAll('.resource-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.resource-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            loadContent();
        });
    });
    
    // Mobile resource tabs
    document.querySelectorAll('.mobile-resource-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.mobile-resource-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            loadMobileContent(this.dataset.tab);
        });
    });
}

// Mobile Menu Setup
function setupMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const closeBtn = document.getElementById('closeMobileMenuBtn');
    const menu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    
    if (hamburger && closeBtn && menu && overlay) {
        hamburger.addEventListener('click', function() {
            this.classList.toggle('active');
            menu.classList.add('open');
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
        
        closeBtn.addEventListener('click', function() {
            hamburger.classList.remove('active');
            menu.classList.remove('open');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        });
        
        overlay.addEventListener('click', function() {
            hamburger.classList.remove('active');
            menu.classList.remove('open');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        });
    }
}

// Initial Content Loading
async function loadInitialContent() {
    await loadSubjects();
    await loadContent();
    loadMobileContent('notes');
}

// Load Subjects List
async function loadSubjects() {
    const dept = document.getElementById('deptDropdown').value;
    const sem = document.querySelector('.semester-pill.active').dataset.sem;
    
    try {
        // Updated API endpoint for PHP
        const response = await fetch(`api/content.php?department=${dept}&semester=${sem}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const content = await response.json();
        
        // Extract unique subjects
        const subjects = [...new Set(content.map(item => item.subject))].filter(Boolean);
        const subjectsList = document.getElementById('subjectsList');
        
        // Clear existing subjects
        subjectsList.innerHTML = '';
        
        // Get bookmarks from localStorage
        const bookmarks = JSON.parse(localStorage.getItem('bookmarkedSubjects')) || [];
        
        // Add subjects to desktop sidebar
        subjects.forEach(subject => {
            const li = document.createElement('li');
            li.innerHTML = `
                <a href="#" class="subject-link" data-subject="${subject}">${subject}</a>
                <button class="bookmark-btn ${bookmarks.includes(subject) ? 'active' : ''}" 
                      data-subject="${subject}">🔖</button>
            `;
            subjectsList.appendChild(li);
        });
        
        // Setup subject click events
        document.querySelectorAll('.subject-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.subject-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                loadContent();
            });
        });
        
        // Setup bookmark click events
        document.querySelectorAll('.bookmark-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleBookmark(this.dataset.subject);
                this.classList.toggle('active');
            });
        });
    } catch (error) {
        console.error('Error loading subjects:', error);
        showToast('Error loading subjects. Please try again.');
    }
}

// Toggle Bookmark
function toggleBookmark(subject) {
    let bookmarks = JSON.parse(localStorage.getItem('bookmarkedSubjects')) || [];
    
    if (bookmarks.includes(subject)) {
        bookmarks = bookmarks.filter(b => b !== subject);
        showToast(`${subject} removed from BookShelf`);
    } else {
        bookmarks.push(subject);
        showToast(`${subject} added to BookShelf`);
    }
    
    localStorage.setItem('bookmarkedSubjects', JSON.stringify(bookmarks));
}

// Show Toast Notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Load Content
async function loadContent() {
    const contentType = document.querySelector('.resource-link.active')?.dataset.type || 'notes';
    const dept = document.getElementById('deptDropdown').value;
    const sem = document.querySelector('.semester-pill.active').dataset.sem;
    const subject = document.querySelector('.subject-link.active')?.dataset.subject;
    
    const mainContent = document.getElementById('mainContent');
    mainContent.innerHTML = '<div class="loading">Loading content...</div>';
    
    try {
        // Build query parameters
        const queryParams = new URLSearchParams({
            contentType: contentType,
            department: dept,
            semester: sem
        });
        
        if (subject) {
            queryParams.append('subject', subject);
        }
        
        // Updated API endpoint for PHP
        const response = await fetch(`api/content.php?${queryParams.toString()}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const filteredContent = await response.json();
        
        if (filteredContent.length === 0) {
            mainContent.innerHTML = '<div class="empty-state">No content found for the selected filters</div>';
            return;
        }
        
        if (contentType === 'exclusive') {
            renderExclusiveContent(filteredContent);
        } else {
            renderNotesContent(filteredContent);
        }
    } catch (error) {
        mainContent.innerHTML = '<div class="error-state">Error loading content. Please try again.</div>';
        console.error('Error loading content:', error);
    }
}

// Render Notes Content
function renderNotesContent(notes) {
    const mainContent = document.getElementById('mainContent');
    mainContent.innerHTML = '';
    
    notes.forEach(note => {
        const card = document.createElement('div');
        card.className = 'resource-card';
        card.innerHTML = `
            <h3>${note.subject}</h3>
            <div class="resource-meta">
                <span>${note.topic}</span>
                <span>Professor ${note.professor}</span>
            </div>
            <p class="resource-desc">${note.description || ''}</p>
            <div class="download-info">
                <span>${note.downloads || 0} downloads</span>
                <a href="#" class="download-btn" data-id="${note._id}">Download</a>
            </div>
        `;
        mainContent.appendChild(card);
    });
    
    // Setup download buttons
    document.querySelectorAll('.download-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            
            try {
                // Updated download endpoint for PHP
                const response = await fetch(`api/download.php?id=${id}`);
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    
                    // Get filename from Content-Disposition header or use default
                    const contentDisposition = response.headers.get('Content-Disposition');
                    let filename = `content-${id}`;
                    
                    if (contentDisposition && contentDisposition.includes('filename=')) {
                        const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }
                    
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                    
                    // Update the download count in UI
                    const downloadCount = this.previousElementSibling;
                    const currentCount = parseInt(downloadCount.textContent) || 0;
                    downloadCount.textContent = `${currentCount + 1} downloads`;
                    
                    showToast('Download started successfully!');
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    showToast(errorData.error || 'Download failed');
                }
            } catch (error) {
                console.error('Download error:', error);
                showToast('Error downloading file');
            }
        });
    });
}

// Get file extension from MIME type
function getFileExtension(mimeType) {
    const extensions = {
        'application/pdf': '.pdf',
        'application/msword': '.doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '.docx',
        'application/vnd.ms-powerpoint': '.ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation': '.pptx',
        'application/vnd.ms-excel': '.xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': '.xlsx',
        'text/plain': '.txt'
    };
    return extensions[mimeType] || '';
}

// Render Exclusive Content
function renderExclusiveContent(exclusives) {
    const mainContent = document.getElementById('mainContent');
    mainContent.innerHTML = '';
    
    exclusives.forEach(item => {
        const card = document.createElement('div');
        card.className = 'resource-card exclusive-card';
        card.innerHTML = `
            <div class="exclusive-card-content">
                <img src="${item.imageUrl || 'images/default-exclusive.jpg'}" alt="${item.title}" class="exclusive-image">
                <h3 class="exclusive-title">${item.title}</h3>
                <p class="exclusive-desc">${item.description}</p>
                <p class="exclusive-price">Price: ${item.price}</p>
                <p class="exclusive-quote">"${item.quote}"</p>
                <button class="download-btn" data-id="${item._id}">Buy Now</button>
            </div>
        `;
        mainContent.appendChild(card);
    });
    
    // Setup purchase buttons
    document.querySelectorAll('.exclusive-card .download-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            alert(`You selected item ${this.dataset.id}. Payment gateway would open here.`);
        });
    });
}

// Load Mobile Content
async function loadMobileContent(tab) {
    const mobileContent = document.getElementById('mobileMainContent');
    const dept = document.getElementById('mobileDeptDropdown').value;
    const sem = document.querySelector('.mobile-semester-pill.active').dataset.sem;
    
    try {
        // Updated API endpoint for PHP
        const response = await fetch(`api/content.php?contentType=${tab}&department=${dept}&semester=${sem}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const filteredContent = await response.json();
        
        if (filteredContent.length === 0) {
            mobileContent.innerHTML = `<div class="mobile-empty-state">No ${tab} content found</div>`;
            return;
        }
        
        if (tab === 'exclusive') {
            let html = '<div class="mobile-exclusive-list">';
            filteredContent.forEach(item => {
                html += `
                    <div class="mobile-exclusive-card">
                        <h3>${item.title}</h3>
                        <p>${item.description}</p>
                        <p class="mobile-price">${item.price}</p>
                        <button class="mobile-download-btn" data-id="${item._id}">Buy Now</button>
                    </div>
                `;
            });
            html += '</div>';
            mobileContent.innerHTML = html;
            
            // Setup purchase buttons
            document.querySelectorAll('.mobile-download-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    alert(`You selected item ${this.dataset.id}. Payment gateway would open here.`);
                });
            });
        } else {
            let html = '<div class="mobile-notes-list">';
            filteredContent.forEach(item => {
                html += `
                    <div class="mobile-note-card">
                        <h4>${item.subject}</h4>
                        <p>${item.topic}</p>
                        <div class="mobile-download-info">
                            <span>${item.downloads || 0} downloads</span>
                            <button class="mobile-download-btn" data-id="${item._id}">Download</button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            mobileContent.innerHTML = html;
            
            // Setup download buttons
            document.querySelectorAll('.mobile-download-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const id = this.dataset.id;
                    try {
                        // Updated download endpoint for PHP
                        const response = await fetch(`api/download.php?id=${id}`);
                        
                        if (response.ok) {
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            
                            // Get filename from Content-Disposition header or use default
                            const contentDisposition = response.headers.get('Content-Disposition');
                            let filename = `content-${id}`;
                            
                            if (contentDisposition && contentDisposition.includes('filename=')) {
                                const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/);
                                if (filenameMatch) {
                                    filename = filenameMatch[1];
                                }
                            }
                            
                            a.download = filename;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                            
                            // Update the download count in UI
                            const downloadCount = this.previousElementSibling;
                            const currentCount = parseInt(downloadCount.textContent) || 0;
                            downloadCount.textContent = `${currentCount + 1} downloads`;
                            
                            showToast('Download started successfully!');
                        } else {
                            const errorData = await response.json().catch(() => ({}));
                            showToast(errorData.error || 'Download failed');
                        }
                    } catch (error) {
                        console.error('Mobile download error:', error);
                        showToast('Error downloading file');
                    }
                });
            });
        }
    } catch (error) {
        mobileContent.innerHTML = '<div class="mobile-empty-state">Error loading content</div>';
        console.error('Error loading mobile content:', error);
    }
}

// Coming Soon Message
function notesShowComingSoon(feature) {
    const messages = [
        `🚧 Oops! ${feature} is still cooking in our development kitchen! 👨‍🍳`,
        `✨ Hold tight! ${feature} is getting dressed up for its big debut! ✨`,
        `🎯 Almost there! ${feature} is doing final rehearsals! 🎬`,
        `🔮 ${feature} is learning some cool tricks before meeting you! 🎩`,
        `🚀 ${feature} is in its final countdown to launch! 🌟`
    ];
    alert(messages[Math.floor(Math.random() * messages.length)]);
}

// Expose for mobile menu
window.notesShowComingSoon = notesShowComingSoon;