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
<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
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
        --bg: #f5f7fa;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

    .topbar {
        background: #090d16;
        color: #fff;
        padding: 18px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .topbar .brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .topbar .brand-name {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
    }
    .topbar .brand-sub {
        font-size: 11px;
        color: rgba(255,255,255,0.6);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .topbar .right { display: flex; align-items: center; gap: 16px; font-size: 13px; }
    .topbar .logout-link {
        color: #fff;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        padding: 8px 16px;
        border-radius: 100px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.25s ease;
    }
    .topbar .logout-link:hover { background: rgba(255,255,255,0.2); }

    .container { max-width: 1180px; margin: 0 auto; padding: 32px 24px 60px; }

    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px; }
    .stat-card {
        background: #fff;
        border-radius: 18px;
        padding: 20px 22px;
        border: 1px solid var(--border);
    }
    .stat-card .stat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 8px; }
    .stat-card .stat-value { font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: var(--blue-dark); }

    .panel {
        background: #fff;
        border-radius: 18px;
        border: 1px solid var(--border);
        overflow: hidden;
    }
    .panel-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .panel-header h2 { font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 700; }
    .filter-tabs { display: flex; gap: 6px; }
    .filter-tab {
        padding: 7px 14px;
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
        padding: 12px 16px;
        background: #fafbfc;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    tbody td { padding: 14px 16px; border-bottom: 1px solid var(--border); vertical-align: top; }
    tbody tr:hover { background: #fafbfc; }
    tbody tr:last-child td { border-bottom: none; }

    .badge {
        display: inline-block;
        padding: 4px 11px;
        border-radius: 100px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .badge.Pending { background: var(--amber-bg); color: var(--amber); }
    .badge.Confirmed { background: var(--green-bg); color: var(--green); }
    .badge.Cancelled { background: var(--red-bg); color: var(--red); }

    .sound-tag { font-size: 12px; color: var(--text-muted); }
    .sound-tag.yes { color: var(--blue-dark); font-weight: 600; }

    .action-btn {
        font-size: 12px;
        font-weight: 700;
        padding: 7px 13px;
        border-radius: 100px;
        border: none;
        cursor: pointer;
        margin-right: 6px;
        transition: all 0.2s ease;
    }
    .action-btn.confirm { background: var(--blue); color: #fff; }
    .action-btn.confirm:hover { background: var(--blue-dark); }
    .action-btn.cancel { background: transparent; color: var(--red); border: 1px solid rgba(185,28,28,0.3); }
    .action-btn.cancel:hover { background: var(--red-bg); }
    .action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .empty-state { padding: 60px 24px; text-align: center; color: var(--text-muted); }
    .client-name { font-weight: 600; color: var(--text); }
    .client-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
</style>
</head>
<body>

<div class="topbar">
    <div class="brand">
        <span class="brand-name">Accordionella</span>
        <span class="brand-sub">Admin Dashboard</span>
    </div>
    <div class="right">
        <span>Hello, <?php echo $adminUsername; ?></span>
        <a href="admin_logout.php" class="logout-link">Log Out</a>
    </div>
</div>

<div class="container">

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Requests</div>
            <div class="stat-value" id="stat-total">—</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Awaiting Confirmation</div>
            <div class="stat-value" id="stat-pending">—</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Confirmed Events</div>
            <div class="stat-value" id="stat-confirmed">—</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Bookings</h2>
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

<script>
    let allBookings = [];
    let currentFilter = 'all';

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
        try {
            const d = new Date(dateStr + 'T' + (timeStr || '00:00'));
            return d.toLocaleDateString('en-GB', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) + ' · ' + (timeStr || '').substring(0,5);
        } catch (e) {
            return dateStr + ' ' + timeStr;
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
                    <div class="client-sub">${escapeHtml(b.client_phone)}</div>
                    <div class="client-sub">${escapeHtml(b.client_email || '')}</div>
                </td>
                <td>${escapeHtml(b.event_type || '—')}</td>
                <td>${formatDate(b.booking_date, b.booking_time)}</td>
                <td>${escapeHtml(b.event_location || '—')}</td>
                <td><span class="sound-tag ${b.sound_system === 'yes' ? 'yes' : ''}">${b.sound_system === 'yes' ? '🔊 Needed' : 'Not needed'}</span></td>
                <td>${escapeHtml(b.referral_source || '—')}</td>
                <td><span class="badge ${b.status}">${escapeHtml(b.status)}</span></td>
                <td>
                    ${b.status === 'Pending' ? `
                        <button class="action-btn confirm" onclick="updateStatus(${b.id}, 'confirm', this)">Confirm</button>
                        <button class="action-btn cancel" onclick="updateStatus(${b.id}, 'cancel', this)">Cancel</button>
                    ` : '<span style="color:#94a3b8;font-size:12px;">No action</span>'}
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
        if (action === 'confirm' && !confirm('Confirm this booking? This will create the Google Calendar event (starting 2 hours early) and send the client a confirmation email.')) return;
        if (action === 'cancel' && !confirm('Cancel this booking request?')) return;

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
