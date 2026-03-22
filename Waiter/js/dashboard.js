/* ---------- SIDEBAR TOGGLE ---------- */
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.admin-sidebar');
toggle.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
    document.querySelector('.admin-main').classList.toggle('collapsed');
});

/* ---------- CONFIRM / ASSIGN DELIVERY ---------- */
document.querySelectorAll('.confirm-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const orderId = this.dataset.order;
        const input   = document.querySelector(`input[name="delivery-${orderId}"]`);
        const val     = input ? input.value.trim() : '';
        if (!val) { alert('Please enter delivery person id or name'); return; }

        const isNum = /^\d+$/.test(val);
        const body  = isNum
            ? `order_id=${orderId}&delivery_id=${encodeURIComponent(val)}`
            : `order_id=${orderId}&delivery_name=${encodeURIComponent(val)}`;

        fetch('assign_delivery.php', {
            method : 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) { alert(res.message); return; }

            /* update delivery table UI */
            document.querySelectorAll('#delivery-info-table tbody tr').forEach(row => {
                if (isNum) {
                    if (row.cells[0].textContent.trim() === val) {
                        const b = row.querySelector('.status-btn');
                        b.classList.remove('btn-active'); b.classList.add('btn-inactive');
                        b.textContent = 'On Delivery';   b.dataset.status = 'busy';
                    }
                } else {
                    if (row.cells[1].textContent.trim().toLowerCase() === val.toLowerCase()) {
                        const b = row.querySelector('.status-btn');
                        b.classList.remove('btn-active'); b.classList.add('btn-inactive');
                        b.textContent = 'On Delivery';   b.dataset.status = 'busy';
                    }
                }
            });

            /* switch button state */
            this.textContent = 'Confirmed';
            this.classList.remove('unconfirmed');
            this.classList.add('confirmed');
        })
        .catch(() => alert('Server error'));
    });
});

