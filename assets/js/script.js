
function toggleDarkMode() {
    document.documentElement.classList.toggle('light-mode');
    const isLight = document.documentElement.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
}

function toggleNotifications(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) dropdown.classList.toggle('show');
}

document.addEventListener('click', function (e) {
    const wrapper = document.querySelector('.notification-wrapper');
    const dropdown = document.getElementById('notificationDropdown');
    if (wrapper && dropdown && !wrapper.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});

function showLogoutModal(e) {
    e.preventDefault();
    document.getElementById('logoutModal').classList.add('show');
}

function hideLogoutModal() {
    document.getElementById('logoutModal').classList.remove('show');
}

function showDeleteModal(e, el) {
    e.preventDefault();
    const title = el.dataset.title;
    const url = el.getAttribute('href');
    document.getElementById('deleteModalText').textContent = 'Are you sure you want to delete "' + title + '"?';
    document.getElementById('deleteModalConfirmBtn').setAttribute('href', url);
    document.getElementById('deleteModal').classList.add('show');
    return false;
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

function showToast(message) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg) {
        showToast(msg);
        urlParams.delete('msg');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
});

