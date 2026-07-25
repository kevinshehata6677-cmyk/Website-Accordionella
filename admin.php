<?php
// admin.php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}
$adminUsername = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — Accordionella</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --blue: #306fa4;
        --blue-dark: #1e4a70;
        --blue-light: #4f95cf;
        --amber: #b9740a;
        --amber-bg: #fdf3e0;
        --green: #15803d;
        --green-bg: #e9f7ee;
        --red: #b91c1c;
        --red-bg: #fdecec;
        --text: #0f172a;
        --text-muted: #64748b;
        --border: #e2e8f0;
        --bg: #f8fafc;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

    .topbar {
        background: #090d16;
        color: #fff;
        padding: 16px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .topbar .brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .topbar .brand-icon {
        width: 38px; height: 38px;
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .topbar .brand-icon svg { width: 20px; height: 20px; stroke: #fff; fill: none; stroke-width: 2; }
    .topbar .brand-name {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
    }
    .topbar .brand-sub {
        font-size: 11px;
        color: rgba(255,255,255,0.5);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .topbar .right { display: flex; align-items: center; gap: 14px; font-size: 13px; }
    .topbar .admin-badge {
        display: flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        padding: 6px 14px;
        border-radius: 100px;
        font-weight: 600;
    }
    .topbar .admin-badge svg { width: 15px; height: 15px; stroke: var(--blue-light); fill: none; stroke-width: 2; }
    .topbar .btn-add-admin {
        background: var(--blue);
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 100px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        display: flex; align-items: center; gap: 6px;
        transition: all 0.2s ease;
    }
    .topbar .btn-add-admin:hover { background: var(--blue-dark); }
    .topbar .btn-add-admin svg { width: 15px; height: 15px; stroke: #fff; fill: none; stroke-width: 2.5; }
    .topbar .logout-link {
        color: #fff;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        padding: 8px 18px;
        border-radius: 100px;
        text-decoration: none;
        font-weight: 600;
        display: flex; align-items: center; gap: 6px;
        transition: all 0.25s ease;
    }
    .topbar .logout-link svg { width: 15px; height: 15px; stroke: #fff; fill: none; stroke-width: 2; }
    .topbar .logout-link:hover { background: rgba(255,255,255,0.2); }

    .container { max-width: 1200px; margin: 0 auto; padding: 32px 24px 60px; }

    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 28px; }
    .stat-card {
        background: #fff;
        border-radius: 20px;
        padding: 22px 24px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        display: flex; align-items: center; justify-content: space-between;
    }
    .stat-card .stat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 6px; }
    .stat-card .stat-value { font-family: 'Playfair Display', serif; font-size: 34px; font-weight: 700; color: var(--text); }
    .stat-icon {
        width: 48px; height: 48px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        background: var(--bg);
    }
    .stat-icon svg { width: 24px; height: 24px; stroke: var(--blue); fill: none; stroke-width: 2; }

    .panel {
        background: #fff;
        border-radius: 20px;
        border: 1px solid var(--border);
        box-shadow: 0 6px 20px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .panel-header {
        padding: 22px 26px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    .panel-header h2 { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; }
    .filter-tabs { display: flex; gap: 8px; }
    .filter-tab {
        padding: 8px 16px;
        border-radius: 100px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        color: var(--text-muted);
        background: transparent;
        border: 1px solid var(--border);
        transition: all 0.2s ease;
    }
    .filter-tab.active { background: var(--blue); color: #fff; border-color: var(--blue); }

    table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    thead th {
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-muted);
        font-weight: 700;
        padding: 14px 18px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    tbody td { padding: 16px 18px; border-bottom: 1px solid var(--border); vertical-align: top; }
    tbody tr:hover { background: #fafbfc; }
    tbody tr:last-child td { border-bottom: none; }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 100px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .badge svg { width: 12px; height: 12px; stroke-width: 2.5; fill: none; }
    .badge.Pending { background: var(--amber-bg); color: var(--amber); }
    .badge.Pending svg { stroke: var(--amber); }
    .badge.Confirmed { background: var(--green-bg); color: var(--green); }
    .badge.Confirmed svg { stroke: var(--green); }
    .badge.Cancelled { background: var(--red-bg); color: var(--red); }
    .badge.Cancelled svg { stroke: var(--red); }

    .sound-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--text-muted);
    }
    .sound-tag svg { width: 14px; height: 14px; stroke-width: 2; fill: none; }
    .sound-tag.yes { color: var(--blue-dark); font-weight: 600; }
    .sound-tag.yes svg { stroke: var(--blue); }

    .action-btn {
        font-size: 12px;
        font-weight: 700;
        padding: 8px 14px;
        border-radius: 100px;
        border: none;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all 0.2s ease;
    }
    .action-btn svg { width: 14px; height: 14px; fill: none; stroke-width: 2.5; }
    .action-btn.confirm { background: var(--blue); color: #fff; }
    .action-btn.confirm svg { stroke: #fff; }
    .action-btn.confirm:hover { background: var(--blue-dark); }
    .action-btn.cancel { background: transparent; color: var(--red); border: 1px solid rgba(185,28,28,0.3); margin-left: 4px; }
    .action-btn.cancel svg { stroke: var(--red); }
    .action-btn.cancel:hover { background: var(--red-bg); }
    .action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .empty-state { padding: 60px 24px; text-align: center; color: var(--text-muted); }
    .client-name { font-weight: 700; color: var(--text); }
    .client-sub { font-size: 12px; color: var(--text-muted); margin-top: 3px; display: flex; align-items: center; gap: 5px; }
    .client-sub svg { width: 13px; height: 13px; stroke: var(--text-muted); fill: none; stroke-width: 2; shrink: 0; }

    /* Modal Overlay */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(9,13,22,0.75); backdrop-filter: blur(10px);
        z-index: 200; display: flex; align-items: center; justify-content: center; padding: 20px;
        opacity: 0; pointer-events: none; transition: all 0.3s ease;
    }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-card {
        background: #fff; border-radius: 24px; width: 100%; max-width: 440px; padding: 32px;
        box-shadow: 0 25px 80px rgba(0,0,0,0.4); position: relative; transform: translateY(20px); transition: transform 0.3s ease;
    }
    .modal-overlay.active .modal-card { transform: translateY(0); }
    .modal-close {
        position: absolute; top: 20px; right: 20px; background: #f1f5f9; border: none;
        width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;
        color: var(--text-muted);
    }
    .modal-close svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2.2; }
    .field-group { margin-bottom: 18px; }
    .field-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    input[type="email"], input[type="password"] {
        width: 100%; padding: 14px 16px; border: 1.5px solid var(--border); border-radius: 14px; font-size: 14.5px; outline: none;
    }
    input:focus { border-color: var(--blue); }
    .btn-modal-submit {
        width: 100%; background: var(--blue); color: #fff; border: none; padding: 15px; border-radius: 14px; font-weight: 700; font-size: 14.5px; cursor: pointer; margin-top: 8px;
    }
    .btn-modal-submit:hover { background: var(--blue-dark); }
</style>
</head>
<body>

<div class="topbar">
    <div class="brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>
        </div>
        <div>
            <div class="brand-name">Accordionella</div>
            <div class="brand-sub">Admin Portal</div>
        </div>
    </div>
    <div class="right">
        <div class="admin-badge">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span><?php echo $adminUsername; ?></span>
        </div>
        <button class="btn-add-admin" onclick="openAddAdminModal()">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Register Admin</span>
        </button>
        <a href="admin_logout.php" class="logout-link">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Log Out</span>
        </a>
    </div>
</div>

<div class="container">

    <div class="stats-row">
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Requests</div>
                <div class="stat-value" id="stat-total">—</div>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Awaiting Confirmation</div>
                <div class="stat-value" id="stat-pending">—</div>
            </div>
            <div class="stat-icon" style="background: var(--amber-bg);">
                <svg viewBox="0 0 24 24" style="stroke: var(--amber);"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Confirmed Events</div>
                <div class="stat-value" id="stat-confirmed">—</div>
            </div>
            <div class="stat-icon" style="background: var(--green-bg);">
                <svg viewBox="0 0 24 24" style="stroke: var(--green);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Booking Reservations</h2>
            <div class="filter-tabs">
                <div class="filter-tab active" data-filter="all" onclick="setFilter('all')">All</div>
                <div class="filter-tab" data-filter="Pending" onclick="setFilter('Pending')">Pending</div>
                <div class="filter-tab" data-filter="Confirmed" onclick="setFilter('Confirmed')">Confirmed</div>
                <div class="filter-tab" data-filter="Cancelled" onclick="setFilter('Cancelled')">Cancelled</div>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table id="bookings-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Event</th>
                        <th>Date &amp; Time</th>
                        <th>Location</th>
                        <th>Sound System</th>
                        <th>Referral</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="bookings-body"></tbody>
            </table>
        </div>
        <div class="empty-state" id="empty-state" style="display:none;">No bookings match this filter yet.</div>
    </div>
</div>

<!-- Modal: Register New Admin -->
<div class="modal-overlay" id="add-admin-modal">
    <div class="modal-card">
        <button class="modal-close" onclick="closeAddAdminModal()">
            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <h3 style="font-family:'Playfair Display',serif; font-size:22px; margin-bottom:6px;">Register New Admin</h3>
        <p style="color:var(--text-muted); font-size:13px; margin-bottom:20px;">Grant administrative access to another team member.</p>

        <form onsubmit="submitNewAdmin(event)">
            <div class="field-group">
                <label>Admin Email Address</label>
                <input type="email" id="new-admin-email" required placeholder="admin.team@accordionella.com">
            </div>
            <div class="field-group">
                <label>Admin Password</label>
                <input type="password" id="new-admin-pass" required placeholder="Min 6 characters">
            </div>
            <button type="submit" class="btn-modal-submit">Create Admin Account</button>
        </form>
    </div>
</div>

<script>
    let allBookings = [];
    let currentFilter = 'all';

    function openAddAdminModal() {
        document.getElementById('add-admin-modal').classList.add('active');
    }
    function closeAddAdminModal() {
        document.getElementById('add-admin-modal').classList.remove('active');
    }

    function submitNewAdmin(e) {
        e.preventDefault();
        const email = document.getElementById('new-admin-email').value.trim();
        const password = document.getElementById('new-admin-pass').value;

        fetch('create_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: password })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    closeAddAdminModal();
                    document.getElementById('new-admin-email').value = '';
                    document.getElementById('new-admin-pass').value = '';
                } else {
                    alert(data.message || 'Failed to create admin.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Server request failed.');
            });
    }

    function setFilter(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.toggle('active', t.dataset.filter === filter));
        renderTable();
    }

    function loadBookings() {
        fetch('get_bookings.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    allBookings = data.bookings;
                    renderStats();
                    renderTable();
                } else {
                    alert(data.message || 'Failed to load bookings.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Failed to reach the server.');
            });
    }

    function renderStats() {
        document.getElementById('stat-total').innerText = allBookings.length;
        document.getElementById('stat-pending').innerText = allBookings.filter(b => b.status === 'Pending').length;
        document.getElementById('stat-confirmed').innerText = allBookings.filter(b => b.status === 'Confirmed').length;
    }

    function formatDate(dateStr, timeStr) {
        if (!dateStr) return 'To be confirmed';
        try {
            const d = new Date(dateStr + 'T' + (timeStr || '00:00'));
            return d.toLocaleDateString('en-GB', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) + (timeStr ? ' · ' + timeStr.substring(0,5) : '');
        } catch (e) {
            return dateStr + ' ' + (timeStr || '');
        }
    }

    function renderTable() {
        const tbody = document.getElementById('bookings-body');
        const emptyState = document.getElementById('empty-state');
        const filtered = currentFilter === 'all' ? allBookings : allBookings.filter(b => b.status === currentFilter);

        if (filtered.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }
        emptyState.style.display = 'none';

        tbody.innerHTML = filtered.map(b => `
            <tr>
                <td>
                    <div class="client-name">${escapeHtml(b.client_name)}</div>
                    <div class="client-sub">
                        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span>${escapeHtml(b.client_phone)}</span>
                    </div>
                    ${b.client_email ? `
                        <div class="client-sub">
                            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <span>${escapeHtml(b.client_email)}</span>
                        </div>
                    ` : ''}
                </td>
                <td><strong>${escapeHtml(b.event_type || '—')}</strong></td>
                <td>${formatDate(b.booking_date, b.booking_time)}</td>
                <td>${escapeHtml(b.event_location || '—')}</td>
                <td>
                    <span class="sound-tag ${b.sound_system === 'yes' ? 'yes' : ''}">
                        <svg viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                        <span>${b.sound_system === 'yes' ? 'Needed' : 'Not needed'}</span>
                    </span>
                </td>
                <td>${escapeHtml(b.referral_source || '—')}</td>
                <td>
                    <span class="badge ${b.status}">
                        ${b.status === 'Confirmed' ? '<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>' : ''}
                        ${b.status === 'Pending' ? '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line></svg>' : ''}
                        ${b.status === 'Cancelled' ? '<svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' : ''}
                        <span>${escapeHtml(b.status)}</span>
                    </span>
                </td>
                <td>
                    ${b.status === 'Pending' ? `
                        <button class="action-btn confirm" onclick="updateStatus(${b.id}, 'confirm', this)">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Approve</span>
                        </button>
                        <button class="action-btn cancel" onclick="updateStatus(${b.id}, 'cancel', this)">
                            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            <span>Reject</span>
                        </button>
                    ` : '<span style="color:#94a3b8;font-size:12px;">Completed</span>'}
                </td>
            </tr>
        `).join('');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.innerText = str ?? '';
        return div.innerHTML;
    }

    function updateStatus(bookingId, action, btnEl) {
        if (action === 'confirm' && !confirm('Approve and confirm this reservation? This will create the Google Calendar event and send the client a confirmation email.')) return;
        if (action === 'cancel' && !confirm('Reject and cancel this booking request?')) return;

        const row = btnEl.closest('tr');
        row.querySelectorAll('.action-btn').forEach(b => b.disabled = true);

        fetch('update_booking_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ booking_id: bookingId, action: action })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    loadBookings();
                } else {
                    alert(data.message || 'Action failed.');
                    row.querySelectorAll('.action-btn').forEach(b => b.disabled = false);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Failed to reach the server.');
                row.querySelectorAll('.action-btn').forEach(b => b.disabled = false);
            });
    }

    loadBookings();
</script>
</body>
</html>
