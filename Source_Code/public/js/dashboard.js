const profileButton = document.getElementById('open-profile-panel');
const closeProfileButton = document.getElementById('close-profile-panel');
const profileOverlay = document.getElementById('profile-panel-overlay');
const profilePanel = document.getElementById('profile-panel');
const notificationButton = document.getElementById('notification-button');
const notificationPanel = document.getElementById('notification-panel');
const closeNotificationButton = document.getElementById('close-notification-panel');
const notificationWrapper = document.querySelector('.notification-wrapper');

function openProfilePanel() {
    profileOverlay.classList.add('active');
    profilePanel.classList.add('active');
    closeNotificationPanel();
}

function closeProfilePanel() {
    profileOverlay.classList.remove('active');
    profilePanel.classList.remove('active');
}

function openNotificationPanel() {
    if (notificationPanel) {
        notificationPanel.classList.toggle('active');
    }
    closeProfilePanel();
}

function closeNotificationPanel() {
    if (notificationPanel) {
        notificationPanel.classList.remove('active');
    }
}

if (profileButton) {
    profileButton.addEventListener('click', openProfilePanel);
}

if (closeProfileButton) {
    closeProfileButton.addEventListener('click', closeProfilePanel);
}

if (profileOverlay) {
    profileOverlay.addEventListener('click', closeProfilePanel);
}

if (notificationButton) {
    notificationButton.addEventListener('click', openNotificationPanel);
}

if (closeNotificationButton) {
    closeNotificationButton.addEventListener('click', closeNotificationPanel);
}

document.addEventListener('click', function (event) {
    if (notificationPanel && notificationPanel.classList.contains('active')) {
        const target = event.target;
        if (notificationWrapper && !notificationWrapper.contains(target)) {
            closeNotificationPanel();
        }
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeProfilePanel();
        closeNotificationPanel();
    }
});

// Dropdown menu toggle for sidebar
// NOTE: Nurse sidebar dropdowns are handled by nurse-specific markup/JS.
// Keeping this generic handler can cause double toggles.
// Disable on nurse routes.
if (!document.body.classList.contains('is-nurse-page') && !document.body.classList.contains('is-nursing-staff-page')) {
    const dropdownMenus = document.querySelectorAll('.menu-dropdown > a');

    dropdownMenus.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const parentLi = link.closest('.menu-dropdown');
            parentLi.classList.toggle('open');
        });
    });
}

