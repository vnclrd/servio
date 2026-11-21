const API_BASE = '/api/books';

const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const clearSearchBtn = document.getElementById('clearSearchBtn');
const booksTableBody = document.getElementById('booksTableBody');
const feedbackBanner = document.getElementById('feedbackBanner');

let debounceTimer = null;

function showFeedback(message, type = 'success') {
    feedbackBanner.textContent = message;
    feedbackBanner.className = `feedback ${type}`;
}

function hideFeedback() {
    feedbackBanner.textContent = '';
    feedbackBanner.className = 'feedback hidden';
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = String(value ?? '');
    return div.innerHTML;
}

async function fetchBooks(searchTerm = '') {
    try {
        const params = new URLSearchParams();
        const trimmed = searchTerm.trim();
        if (trimmed !== '') {
            params.set('search', trimmed);
        }

        const url = params.toString() ? `${API_BASE}?${params.toString()}` : API_BASE;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
            },
        });

        const payload = await response.json();

        if (!response.ok || !payload.success) {
            throw new Error(payload.message || 'Failed to load books.');
        }

        renderBooks(payload.data || []);
    } catch (error) {
        booksTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="empty-state error-text">${escapeHtml(error.message)}</td>
            </tr>
        `;
        showFeedback(error.message, 'error');
    }
}

function renderBooks(books) {
    if (!books.length) {
        booksTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="empty-state">No matching books found.</td>
            </tr>
        `;
        return;
    }

    booksTableBody.innerHTML = books.map((book) => {
        const isAvailable = book.status === 'Available';
        const nextStatus = isAvailable ? 'Borrowed' : 'Available';
        const actionLabel = isAvailable ? 'Borrow' : 'Return';
        const badgeClass = isAvailable ? 'badge-available' : 'badge-borrowed';

        return `
            <tr data-book-id="${escapeHtml(book.book_id)}">
                <td>${escapeHtml(book.book_id)}</td>
                <td>${escapeHtml(book.title)}</td>
                <td>${escapeHtml(book.author)}</td>
                <td><span class="badge ${badgeClass}">${escapeHtml(book.status)}</span></td>
                <td>
                    <button
                        type="button"
                        class="btn-toggle"
                        data-id="${escapeHtml(book.book_id)}"
                        data-next-status="${nextStatus}"
                    >
                        ${actionLabel}
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

async function toggleBookStatus(bookId, nextStatus, buttonEl) {
    const originalText = buttonEl.textContent;
    const method = nextStatus === 'Borrowed' ? 'POST' : 'PUT';

    buttonEl.disabled = true;
    buttonEl.textContent = 'Updating...';

    try {
        const response = await fetch(`${API_BASE}/${encodeURIComponent(bookId)}/status`, {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: nextStatus }),
        });

        const payload = await response.json();

        if (!response.ok || !payload.success) {
            throw new Error(payload.message || 'Could not update book status.');
        }

        showFeedback(
            `"${payload.data.title}" is now marked as ${payload.data.status}.`,
            'success'
        );
        await fetchBooks(searchInput.value);
    } catch (error) {
        showFeedback(error.message, 'error');
        buttonEl.disabled = false;
        buttonEl.textContent = originalText;
    }
}

searchInput.addEventListener('input', (event) => {
    clearTimeout(debounceTimer);
    const value = event.target.value;
    debounceTimer = setTimeout(() => {
        fetchBooks(value);
    }, 200);
});

// Search button: runs the search immediately (skips the typing delay)
searchBtn.addEventListener('click', () => {
    clearTimeout(debounceTimer);
    fetchBooks(searchInput.value);
});

clearSearchBtn.addEventListener('click', () => {
    searchInput.value = '';
    hideFeedback();
    fetchBooks('');
    searchInput.focus();
});

booksTableBody.addEventListener('click', (event) => {
    const button = event.target.closest('.btn-toggle');
    if (!button) return;

    const bookId = button.dataset.id;
    const nextStatus = button.dataset.nextStatus;
    toggleBookStatus(bookId, nextStatus, button);
});

document.addEventListener('DOMContentLoaded', () => {
    fetchBooks('');
});