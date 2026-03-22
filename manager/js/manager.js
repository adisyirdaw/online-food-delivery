console.log('NEW manager.js loaded');   // remove after test

/* ---------- 1.  LOGIN  ---------- */
if (document.getElementById('loginForm')) {
  document.getElementById('loginForm').addEventListener('submit', async e => {
    e.preventDefault();
    const res = await fetch('login.php', {
      method : 'POST',
      body   : new FormData(e.target)
    });
    const data = await res.json();
    if (data.success) location.href = data.redirect;
    else alert(data.message || 'Login failed');
  });
}

/* ---------- 2.  SIDEBAR ACTIVE HIGHLIGHT  ---------- */
const current = location.pathname.split('/').pop();
document.querySelectorAll('.sidebar-menu a').forEach(a => {
  if (a.getAttribute('href') === current) a.parentElement.classList.add('active');
  else a.parentElement.classList.remove('active');
});

/* ---------- 3.  MENU TOGGLE  ---------- */
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('collapsed');
});
fetch('manager-dashboard.php?action=stats')
fetch('manager-dashboard.php?action=recent_orders')
if (current === 'manager-dashboard.php') {
  Promise.all([
    fetch('manager-dashboard.php?action=stats').then(r => r.json()),
    fetch('manager-dashboard.php?action=recent_orders').then(r => r.json())
  ]).then(([stats, orders]) => {
    document.getElementById('totalOrders').textContent        = stats.total_orders;
    document.getElementById('revenue').textContent            = '$' + Number(stats.revenue).toFixed(2);
    document.getElementById('customers').textContent          = stats.customers;
    document.getElementById('pendingComplaints').textContent  = stats.pending_complaints;

    const tbody = document.querySelector('#recentOrdersTable tbody');
    tbody.innerHTML = orders.map(o =>
      `<tr>
        <td>${o.order_id}</td>
        <td>${o.customer}</td>
        <td>${o.amount}</td>
        <td><span class="badge ${o.status_class}">${o.status}</span></td>
      </tr>`).join('');
  }).catch(() => console.error('Dashboard load failed'));
}

/* ---------- 5.  COMPLAINTS LOAD  ---------- */
if (current === 'manager-complaints.php') {
  fetch('manager-complaints.php?action=list')
    .then(r => r.json())
    .then(data => {
      const box = document.querySelector('.complaints-list');
      box.innerHTML = data.map(c =>
        `<div class="complaint-card">
          <h4>${c.title} <small>${c.order_id}</small></h4>
          <p>${c.description}</p>
          <div class="meta">${c.created_at} – <strong>${c.status}</strong>
            ${c.status === 'open' ? `<button class="resolve-btn" data-id="${c.id}">Resolve</button>` : ''}
          </div>
        </div>`).join('');

      box.addEventListener('click', e => {
        if (!e.target.classList.contains('resolve-btn')) return;
        const id = e.target.dataset.id;
        fetch('manager-complaints.php?action=resolve', {
          method : 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body   : 'complaint_id=' + id
        })
          .then(r => r.json())
          .then(res => {
            if (res.success) location.reload();
            else alert(res.message);
          });
      });
    });
}

/* ---------- 6.  SALES LOAD  ---------- */
if (current === 'manager-sales.php') {
  Promise.all([
    fetch('manager-sales.php?action=summary').then(r => r.json()),   // ← fixed path
    fetch('manager-sales.php?action=categories').then(r => r.json()) // ← fixed path
  ]).then(([sum, cat]) => {
    document.querySelector('.summary-card:nth-child(1) .amount').textContent = '$' + Number(sum.today).toFixed(2);
    document.querySelector('.summary-card:nth-child(2) .amount').textContent = '$' + Number(sum.week).toFixed(2);
    document.querySelector('.summary-card:nth-child(3) .amount').textContent = '$' + Number(sum.month).toFixed(2);

    const tbody = document.querySelector('.data-table tbody');
    tbody.innerHTML = cat.map(c =>
      `<tr>
        <td>${c.category}</td>
        <td>${c.orders}</td>
        <td>$${Number(c.revenue).toFixed(2)}</td>
        <td>${c.percentage}%</td>
      </tr>`).join('');
  });
}

/* ---------- 7.  PROFILE SAVE + EYE-TOGGLE  ---------- */
if (current === 'manager-profile.php') {
  document.querySelectorAll('.toggle-pw').forEach(icon => {
    icon.addEventListener('click', function () {
      const tgt = document.getElementById(this.dataset.target);
      tgt.type = tgt.type === 'password' ? 'text' : 'password';
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });
  });

  document.getElementById('profileForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const res = await fetch('manager-profile.php', {
      method : 'POST',
      body   : new FormData(e.target)
    });
    const data = await res.json();
    alert(data.success ? 'Profile updated' : (data.message || 'Update failed'));
  });
}
