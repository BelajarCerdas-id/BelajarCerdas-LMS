/**
 * Belajar Cerdas - Site Administrator Analytics Dashboard Controller
 * Handles Chart.js charts, real-time KPI metrics, school leaderboard, and live activity audit logs
 */

document.addEventListener('DOMContentLoaded', function () {
    let currentPeriod = 'today';
    let currentSchool = 'all';
    let currentRole = 'all';
    let currentModule = 'all';
    let currentSearch = '';
    let currentPage = 1;

    // Chart instances
    let chartTrend = null;
    let chartRoles = null;
    let chartModules = null;
    let chartSubmodules = null;

    // Role Color Palette
    const roleColors = {
        'Siswa': '#10B981',               // Emerald
        'Guru': '#6366F1',                // Indigo
        'Kepala Sekolah': '#8B5CF6',       // Purple
        'Wakil Kepala Sekolah': '#A855F7',// Purple-light
        'Wakil Kesiswaan': '#EC4899',     // Pink
        'Admin Sekolah': '#0EA5E9',       // Sky
        'Orang Tua': '#F59E0B',           // Amber
        'Administrator': '#EF4444',       // Red
        'Finance': '#14B8A6',             // Teal
        'Yayasan': '#3B82F6',             // Blue
        'Default': '#64748B'              // Slate
    };

    // Helper untuk badge role
    function getRoleBadge(role) {
        const bgMap = {
            'Siswa': 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Guru': 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'Kepala Sekolah': 'bg-purple-50 text-purple-700 border-purple-200',
            'Wakil Kepala Sekolah': 'bg-purple-50 text-purple-700 border-purple-200',
            'Wakil Kesiswaan': 'bg-pink-50 text-pink-700 border-pink-200',
            'Admin Sekolah': 'bg-sky-50 text-sky-700 border-sky-200',
            'Orang Tua': 'bg-amber-50 text-amber-700 border-amber-200',
            'Administrator': 'bg-rose-50 text-rose-700 border-rose-200',
            'Finance': 'bg-teal-50 text-teal-700 border-teal-200',
            'Yayasan': 'bg-blue-50 text-blue-700 border-blue-200',
        };

        const classes = bgMap[role] || 'bg-slate-50 text-slate-700 border-slate-200';
        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border ${classes}">${role}</span>`;
    }

    // Helper untuk modul badge
    function getModuleBadge(module, subModule) {
        let subText = subModule && subModule !== '-' ? ` <i class="fa-solid fa-angle-right text-[10px] text-slate-400 mx-1"></i> <span class="font-medium text-slate-700">${subModule}</span>` : '';
        return `<div class="inline-flex items-center text-xs font-semibold text-[#0071BC] bg-sky-50 px-2.5 py-1 rounded-xl border border-sky-200/80">
            <i class="fa-solid fa-cube text-[11px] mr-1.5 text-sky-600"></i>
            <span>${module}</span>
            ${subText}
        </div>`;
    }

    // ==========================================
    // 1. LOAD KPI METRICS
    // ==========================================
    function loadKpis() {
        fetch('/administrator/analytics/kpi')
            .then(res => res.json())
            .then(json => {
                if (json.status === 'success') {
                    const d = json.data;

                    document.getElementById('kpi-online-users').textContent = d.online_users_now.toLocaleString('id-ID');
                    document.getElementById('kpi-activities-today').textContent = d.activities_today.toLocaleString('id-ID');
                    document.getElementById('kpi-active-users-today').textContent = d.active_users_today.toLocaleString('id-ID');
                    document.getElementById('kpi-total-registered').textContent = d.total_registered_users.toLocaleString('id-ID');
                    document.getElementById('kpi-top-module').textContent = d.top_module;
                    document.getElementById('kpi-active-schools').textContent = d.active_schools_today.toLocaleString('id-ID');
                }
            })
            .catch(err => console.error('Error fetching KPI metrics:', err));
    }

    // ==========================================
    // 2. LOAD CHARTS
    // ==========================================
    function loadCharts() {
        const params = new URLSearchParams({
            period: currentPeriod,
            school_id: currentSchool,
            role: currentRole,
            module: currentModule
        });

        // Show loaders
        toggleChartLoader('chart-trend-loader', true);
        toggleChartLoader('chart-roles-loader', true);
        toggleChartLoader('chart-modules-loader', true);
        toggleChartLoader('chart-submodules-loader', true);

        fetch(`/administrator/analytics/charts?${params.toString()}`)
            .then(res => res.json())
            .then(json => {
                if (json.status === 'success') {
                    renderTrendChart(json.timeline);
                    renderRoleChart(json.roles);
                    renderModuleChart(json.modules);
                    renderSubmoduleChart(json.sub_modules);
                }
            })
            .catch(err => console.error('Error fetching chart data:', err))
            .finally(() => {
                toggleChartLoader('chart-trend-loader', false);
                toggleChartLoader('chart-roles-loader', false);
                toggleChartLoader('chart-modules-loader', false);
                toggleChartLoader('chart-submodules-loader', false);
            });
    }

    function toggleChartLoader(id, show) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = show ? 'flex' : 'none';
        }
    }

    // 2.1 Trend Line Chart
    function renderTrendChart(timeline) {
        const ctx = document.getElementById('canvas-activity-trend');
        if (!ctx) return;

        if (chartTrend) {
            chartTrend.destroy();
        }

        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(0, 113, 188, 0.35)');
        gradient.addColorStop(1, 'rgba(0, 113, 188, 0.00)');

        chartTrend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timeline.labels,
                datasets: [{
                    label: 'Jumlah Aktivitas Akses',
                    data: timeline.data,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: '#0071BC',
                    borderWidth: 2.5,
                    tension: 0.35,
                    pointBackgroundColor: '#0071BC',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 12,
                        cornerRadius: 12,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function (context) {
                                return ` ${context.parsed.y} aktivitas`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: {
                            color: '#64748B',
                            font: { size: 11 },
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // 2.2 Role Breakdown Donut Chart
    function renderRoleChart(rolesData) {
        const ctx = document.getElementById('canvas-role-breakdown');
        if (!ctx) return;

        if (chartRoles) {
            chartRoles.destroy();
        }

        const labels = rolesData.labels.length ? rolesData.labels : ['Belum Ada Data'];
        const values = rolesData.data.length ? rolesData.data : [1];
        const colors = labels.map(r => roleColors[r] || roleColors['Default']);

        chartRoles = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function (context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const val = context.parsed;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ` ${val} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Update Legend Badges
        const legendContainer = document.getElementById('roles-legend-container');
        if (legendContainer) {
            legendContainer.innerHTML = '';
            labels.forEach((label, i) => {
                if (label !== 'Belum Ada Data') {
                    const color = colors[i];
                    legendContainer.innerHTML += `
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: ${color}"></span>
                            <span class="text-slate-700 font-medium">${label}: <strong>${values[i]}</strong></span>
                        </div>
                    `;
                }
            });
        }
    }

    // 2.3 Module Usage Horizontal Bar Chart
    function renderModuleChart(modulesData) {
        const ctx = document.getElementById('canvas-module-usage');
        if (!ctx) return;

        if (chartModules) {
            chartModules.destroy();
        }

        const labels = modulesData.labels.length ? modulesData.labels : ['Belum Ada Modul'];
        const values = modulesData.data.length ? modulesData.data : [0];

        chartModules = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Akses',
                    data: values,
                    backgroundColor: '#10B981',
                    borderRadius: 8,
                    barThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 10,
                        cornerRadius: 10
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#64748B', precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#334155', font: { size: 11, weight: '500' } }
                    }
                }
            }
        });
    }

    // 2.4 Sub-Module Popularity
    function renderSubmoduleChart(submodulesData) {
        const ctx = document.getElementById('canvas-submodule-usage');
        if (!ctx) return;

        if (chartSubmodules) {
            chartSubmodules.destroy();
        }

        const desc = document.getElementById('submodule-parent-desc');
        if (desc && submodulesData.parent_module) {
            desc.textContent = `Rincian sub-modul untuk modul teratas: ${submodulesData.parent_module}`;
        }

        const labels = submodulesData.labels && submodulesData.labels.length ? submodulesData.labels : ['Belum Ada Sub-modul'];
        const values = submodulesData.data && submodulesData.data.length ? submodulesData.data : [0];

        chartSubmodules = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Akses Sub-Modul',
                    data: values,
                    backgroundColor: '#0284C7',
                    borderRadius: 8,
                    barThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 10,
                        cornerRadius: 10
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9' },
                        ticks: { color: '#64748B', precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#334155', font: { size: 11, weight: '500' } }
                    }
                }
            }
        });
    }

    // ==========================================
    // 3. LOAD SCHOOL LEADERBOARD
    // ==========================================
    function loadSchoolLeaderboard() {
        const container = document.getElementById('school-leaderboard-list');
        if (!container) return;

        fetch(`/administrator/analytics/schools?period=${currentPeriod}`)
            .then(res => res.json())
            .then(json => {
                if (json.status === 'success') {
                    const schools = json.data;

                    if (!schools.length) {
                        container.innerHTML = `
                            <div class="text-center py-10 text-slate-400">
                                <i class="fa-solid fa-school text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs">Belum ada aktivitas sekolah pada periode ini.</p>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    schools.forEach((item, index) => {
                        const rank = index + 1;
                        let rankBadge = `<span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center">${rank}</span>`;
                        if (rank === 1) rankBadge = `<span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 font-bold text-xs flex items-center justify-center shadow-sm"><i class="fa-solid fa-trophy"></i></span>`;
                        else if (rank === 2) rankBadge = `<span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center"><i class="fa-solid fa-medal"></i></span>`;
                        else if (rank === 3) rankBadge = `<span class="w-6 h-6 rounded-full bg-amber-50 text-amber-700 font-bold text-xs flex items-center justify-center"><i class="fa-solid fa-award"></i></span>`;

                        const logoHtml = item.logo
                            ? `<img src="${item.logo}" alt="Logo" class="w-8 h-8 rounded-xl object-cover border border-slate-200">`
                            : `<div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs"><i class="fa-solid fa-school"></i></div>`;

                        html += `
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 hover:bg-sky-50/50 border border-slate-200/70 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    ${rankBadge}
                                    ${logoHtml}
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 truncate">${item.school_name}</h4>
                                        <p class="text-[11px] text-slate-500">NPSN: ${item.npsn} &bull; ${item.jenjang}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-xs sm:text-sm font-extrabold text-[#0071BC]">${item.total_activities.toLocaleString('id-ID')}</span>
                                    <p class="text-[10px] text-slate-400">${item.active_users_count} pengguna</p>
                                </div>
                            </div>
                        `;
                    });

                    container.innerHTML = html;
                }
            })
            .catch(err => console.error('Error fetching school leaderboard:', err));
    }

    // ==========================================
    // 4. LOAD LIVE ONLINE USERS
    // ==========================================
    function loadLiveOnlineUsers() {
        const container = document.getElementById('live-online-users-list');
        if (!container) return;

        fetch('/administrator/analytics/live-users')
            .then(res => res.json())
            .then(json => {
                if (json.status === 'success') {
                    const users = json.data;

                    if (!users.length) {
                        container.innerHTML = `
                            <div class="col-span-full text-center py-10 text-slate-400">
                                <i class="fa-solid fa-user-clock text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs">Tidak ada aktivitas pengguna dalam 15 menit terakhir.</p>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    users.forEach(u => {
                        html += `
                            <div class="p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100/80 border border-slate-200/80 transition flex flex-col justify-between gap-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-400 to-[#0071BC] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                            ${u.name.charAt(0).toUpperCase()}
                                        </div>
                                        <div class="min-w-0">
                                            <h5 class="text-xs sm:text-sm font-bold text-slate-800 truncate">${u.name}</h5>
                                            <p class="text-[11px] text-slate-400 truncate">${u.email}</p>
                                        </div>
                                    </div>
                                    ${getRoleBadge(u.role)}
                                </div>
                                <div class="flex items-center justify-between text-[11px] pt-2 border-t border-slate-200/60 text-slate-500">
                                    <span class="truncate max-w-[140px]" title="${u.school}"><i class="fa-solid fa-school text-[10px] mr-1 text-slate-400"></i>${u.school}</span>
                                    <span class="text-emerald-600 font-semibold shrink-0"><i class="fa-solid fa-eye text-[10px] mr-1"></i>${u.module} &bull; ${u.last_seen}</span>
                                </div>
                            </div>
                        `;
                    });

                    container.innerHTML = html;
                }
            })
            .catch(err => console.error('Error fetching live online users:', err));
    }

    // ==========================================
    // 5. LOAD ACTIVITY AUDIT LOG TABLE
    // ==========================================
    function loadActivityLogs(page = 1) {
        currentPage = page;
        const tbody = document.getElementById('table-activity-body');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-12 text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-[#0071BC] mb-2"></i>
                    <p class="text-xs">Memuat data aktivitas...</p>
                </td>
            </tr>
        `;

        const params = new URLSearchParams({
            page: page,
            search: currentSearch,
            school_id: currentSchool,
            role: currentRole,
            module: currentModule,
        });

        fetch(`/administrator/analytics/logs?${params.toString()}`)
            .then(res => res.json())
            .then(json => {
                if (json.status === 'success') {
                    const logs = json.data;

                    if (!logs.length) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center py-14 text-slate-400">
                                    <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300"></i>
                                    <p class="text-sm font-semibold text-slate-600">Tidak ada riwayat aktivitas ditemukan</p>
                                    <p class="text-xs mt-1 text-slate-400">Coba ubah kata kunci pencarian atau sesuaikan filter di atas.</p>
                                </td>
                            </tr>
                        `;
                        updatePaginationControls(0, 0, 0, 0);
                        return;
                    }

                    let html = '';
                    logs.forEach(row => {
                        html += `
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                            ${row.user_name.charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900">${row.user_name}</div>
                                            <div class="text-[11px] text-slate-400 font-mono">${row.user_email}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    ${getRoleBadge(row.role)}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800">${row.school_name}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    ${getModuleBadge(row.module, row.sub_module)}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-xs text-slate-600 max-w-[200px] truncate" title="${row.url}">
                                        <span class="font-semibold text-slate-700">${row.action}</span>
                                        <span class="text-slate-400 block truncate font-mono text-[11px]">${row.url}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                                    <span class="font-medium text-slate-700">${row.created_at_human}</span>
                                    <span class="text-[11px] text-slate-400 block">${row.created_at_full}</span>
                                </td>
                            </tr>
                        `;
                    });

                    tbody.innerHTML = html;
                    updatePaginationControls(json.current_page, json.last_page, json.total, json.per_page);
                }
            })
            .catch(err => {
                console.error('Error fetching activity logs:', err);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-10 text-red-500 text-xs">
                            Gagal memuat catatan aktivitas. Silakan coba segarkan kembali.
                        </td>
                    </tr>
                `;
            });
    }

    // 5.1 Pagination Controls
    function updatePaginationControls(currentPage, lastPage, total, perPage) {
        const info = document.getElementById('table-pagination-info');
        const controls = document.getElementById('table-pagination-controls');

        if (!info || !controls) return;

        if (total === 0) {
            info.textContent = 'Tidak ada data aktivitas';
            controls.innerHTML = '';
            return;
        }

        const start = ((currentPage - 1) * perPage) + 1;
        const end = Math.min(currentPage * perPage, total);
        info.textContent = `Menampilkan ${start} - ${end} dari total ${total.toLocaleString('id-ID')} aktivitas`;

        let btnHtml = '';

        // Previous button
        btnHtml += `
            <button class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium cursor-pointer transition ${currentPage <= 1 ? 'opacity-40 cursor-not-allowed bg-slate-50 text-slate-400' : 'bg-white hover:bg-slate-50 text-slate-700'}"
                ${currentPage <= 1 ? 'disabled' : `onclick="changePage(${currentPage - 1})"`}>
                <i class="fa-solid fa-chevron-left text-[10px] mr-1"></i> Sebelumnya
            </button>
        `;

        // Page numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, currentPage + 2);

        for (let p = startPage; p <= endPage; p++) {
            const isActive = p === currentPage;
            btnHtml += `
                <button class="w-8 h-8 rounded-xl text-xs font-bold cursor-pointer transition ${isActive ? 'bg-[#0071BC] text-white shadow-sm' : 'border border-slate-200 bg-white hover:bg-slate-50 text-slate-700'}"
                    onclick="changePage(${p})">
                    ${p}
                </button>
            `;
        }

        // Next button
        btnHtml += `
            <button class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium cursor-pointer transition ${currentPage >= lastPage ? 'opacity-40 cursor-not-allowed bg-slate-50 text-slate-400' : 'bg-white hover:bg-slate-50 text-slate-700'}"
                ${currentPage >= lastPage ? 'disabled' : `onclick="changePage(${currentPage + 1})"`}>
                Berikutnya <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i>
            </button>
        `;

        controls.innerHTML = btnHtml;
    }

    // Expose changePage to window for pagination buttons
    window.changePage = function (p) {
        loadActivityLogs(p);
    };

    // ==========================================
    // 6. EVENT LISTENERS
    // ==========================================

    // Period buttons
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.period-btn').forEach(b => {
                b.classList.remove('bg-white', 'text-[#0071BC]', 'shadow-sm');
                b.classList.add('text-slate-600');
            });
            this.classList.add('bg-white', 'text-[#0071BC]', 'shadow-sm');
            this.classList.remove('text-slate-600');

            currentPeriod = this.dataset.period;

            // Update period label
            const labelMap = { 'today': 'Hari Ini', '7days': '7 Hari Terakhir', '30days': '30 Hari Terakhir' };
            const labelEl = document.getElementById('trend-period-label');
            if (labelEl) labelEl.textContent = labelMap[currentPeriod] || 'Hari Ini';

            loadCharts();
            loadSchoolLeaderboard();
        });
    });

    // Dropdown filters
    const selectSchool = document.getElementById('filter-school');
    if (selectSchool) {
        selectSchool.addEventListener('change', function () {
            currentSchool = this.value;
            loadCharts();
            loadActivityLogs(1);
        });
    }

    const selectRole = document.getElementById('filter-role');
    if (selectRole) {
        selectRole.addEventListener('change', function () {
            currentRole = this.value;
            loadCharts();
            loadActivityLogs(1);
        });
    }

    const selectModule = document.getElementById('filter-module');
    if (selectModule) {
        selectModule.addEventListener('change', function () {
            currentModule = this.value;
            loadCharts();
            loadActivityLogs(1);
        });
    }

    // Reset filters
    const btnReset = document.getElementById('btn-reset-filters');
    if (btnReset) {
        btnReset.addEventListener('click', function () {
            currentSchool = 'all';
            currentRole = 'all';
            currentModule = 'all';
            currentSearch = '';

            if (selectSchool) selectSchool.value = 'all';
            if (selectRole) selectRole.value = 'all';
            if (selectModule) selectModule.value = 'all';
            const inputSearch = document.getElementById('table-search');
            if (inputSearch) inputSearch.value = '';

            loadCharts();
            loadActivityLogs(1);
        });
    }

    // Search input with debounce
    let searchTimeout = null;
    const inputSearch = document.getElementById('table-search');
    if (inputSearch) {
        inputSearch.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = this.value.trim();
                loadActivityLogs(1);
            }, 350);
        });
    }

    // Refresh button
    const btnRefresh = document.getElementById('btn-refresh-analytics');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            const icon = document.getElementById('icon-refresh');
            if (icon) icon.classList.add('fa-spin');

            loadKpis();
            loadCharts();
            loadSchoolLeaderboard();
            loadLiveOnlineUsers();
            loadActivityLogs(currentPage);

            setTimeout(() => {
                if (icon) icon.classList.remove('fa-spin');
            }, 800);
        });
    }

    // ==========================================
    // 7. INITIALIZE DASHBOARD
    // ==========================================
    loadKpis();
    loadCharts();
    loadSchoolLeaderboard();
    loadLiveOnlineUsers();
    loadActivityLogs(1);

    // Auto-refresh real-time sections every 60 seconds
    setInterval(() => {
        loadKpis();
        loadLiveOnlineUsers();
    }, 60000);
});
