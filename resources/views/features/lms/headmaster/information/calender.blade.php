@include('components/sidebar-beranda', [
    'headerSideNav' => 'Kalender Akademik'
])

@if (in_array(Auth::user()->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah']))
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-slate-100 min-h-screen pb-12">
        <div class="pt-6 sm:pt-8 mx-4 sm:mx-6 lg:mx-10">
            
            {{-- HEADER SECTION --}}
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 sm:mb-8 gap-5 bg-white p-4 sm:p-5 lg:p-6 rounded-2xl shadow-sm border-t-4 border-t-[#0071BC] transition-all">
                <div class="w-full">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-[#005B94] tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 shrink-0 rounded-lg bg-blue-100 flex items-center justify-center text-[#0071BC]">
                            <i class="fas fa-calendar-check text-lg"></i>
                        </div>
                        Kalender Akademik
                    </h1>
                    <p class="text-slate-500 mt-3 sm:mt-1 text-xs sm:text-sm font-medium sm:ml-[52px]">Kelola agenda dan sinkronisasi libur nasional secara otomatis.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                    <button id="btn-draft" onclick="saveCalendarData('draft')" class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border-2 border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-[#0071BC] transition-all text-sm font-bold shadow-sm cursor-pointer">
                        <i class="fas fa-file-lines"></i> Draft
                    </button>
                    <button id="btn-upload" onclick="saveCalendarData('published')" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#0071BC] to-[#005B94] text-white hover:shadow-lg hover:shadow-blue-500/30 transition-all text-sm font-bold cursor-pointer transform hover:-translate-y-0.5 border border-transparent">
                        <i class="fas fa-cloud-arrow-up"></i> Publish
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">
                
                {{-- CALENDAR GRID --}}
                <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-200 p-4 sm:p-6 lg:p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-full -z-0 opacity-60 pointer-events-none"></div>
                    
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 sm:mb-8 gap-4 relative z-10">
                        <h2 id="calendar-month-year" class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
                            <span id="loading-api" class="hidden"><i class="fas fa-circle-notch fa-spin text-lg text-blue-500"></i></span>
                        </h2>
                        <div class="flex gap-1.5 bg-slate-50 p-1.5 rounded-xl border border-slate-200 self-end sm:self-auto">
                            <button id="prev-month" class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg hover:bg-white hover:shadow-sm transition-all cursor-pointer text-slate-500 hover:text-[#0071BC] focus:outline-none">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button id="next-month" class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg hover:bg-white hover:shadow-sm transition-all cursor-pointer text-slate-500 hover:text-[#0071BC] focus:outline-none">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-7 gap-1 sm:gap-2 text-center text-[10px] sm:text-xs lg:text-sm font-extrabold text-slate-500 mb-4 sm:mb-6 uppercase tracking-wider bg-slate-50 py-2 sm:py-3 rounded-xl relative z-10 border border-slate-100">
                        <div class="text-red-500">Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div class="text-[#0071BC]">Sab</div>
                    </div>
                    
                    <div id="calendar-days" class="grid grid-cols-7 gap-y-2 sm:gap-y-4 lg:gap-y-6 gap-x-1 sm:gap-x-2 text-center relative z-10"></div>
                </div>

                {{-- SIDEBAR KANAN (Legend & List) --}}
                <div class="flex flex-col gap-6">
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-5 sm:p-6 lg:p-7">
                        <h3 class="font-extrabold text-slate-800 mb-4 sm:mb-5 text-[16px] sm:text-[17px] flex items-center gap-2 pb-4 border-b border-slate-100">
                            <i class="fas fa-palette text-[#0071BC]"></i> Panduan Warna
                        </h3>
                        <div class="flex flex-col gap-3.5 text-[12px] sm:text-[13px] font-bold text-slate-600">
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md bg-[#B91C1C] shadow-sm shrink-0"></div> <span>Libur Nasional</span>
                            </div>
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md border-2 border-[#B91C1C] bg-red-50 shrink-0"></div> <span>Cuti / Libur Sekolah</span>
                            </div>
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md border-2 border-[#10B981] bg-emerald-50 shrink-0"></div> <span>Hari Ujian</span>
                            </div>
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md border-2 border-[#F59E0B] bg-amber-50 shrink-0"></div> <span>Kegiatan Sekolah</span>
                            </div>
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md border-2 border-[#1E3A8A] bg-blue-50 shrink-0"></div> <span>Kegiatan WFA</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-5 sm:p-6 lg:p-7 flex flex-col max-h-[400px] sm:max-h-[480px]">
                        <h3 class="font-extrabold text-slate-800 mb-4 sm:mb-5 text-[16px] sm:text-[17px] flex items-center gap-2 pb-4 border-b border-slate-100 shrink-0">
                            <i class="fas fa-list-check text-[#0071BC]"></i> Agenda Bulan Ini
                        </h3>
                        <div id="event-list" class="flex flex-col gap-4 text-sm overflow-y-auto pr-2 sm:pr-3 custom-scrollbar flex-1 pb-2">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- MODAL TAMBAH AGENDA --}}
<dialog id="modal_tambah_kegiatan" class="modal backdrop:bg-slate-900/40 backdrop:backdrop-blur-sm transition-all p-4">
    <div class="modal-box bg-white w-full max-w-md rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 mx-auto">
        <h3 class="font-extrabold text-lg sm:text-xl text-slate-800 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-50 text-[#0071BC] rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-plus text-lg sm:text-xl"></i>
            </div>
            Tambah Agenda
        </h3>
        <form id="form-tambah-kegiatan" class="space-y-5">
            <div>
                <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-2">Judul Kegiatan</label>
                <input type="text" id="event-title" required placeholder="Contoh: Ujian Tengah Semester" class="w-full border-2 border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-[#0071BC] outline-none transition-all placeholder:font-medium placeholder:text-slate-400">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                    <input type="date" id="event-start-date" required class="w-full border-2 border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-[#0071BC] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-2">Selesai (Opsional)</label>
                    <input type="date" id="event-end-date" class="w-full border-2 border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-[#0071BC] outline-none transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-2">Kategori Label</label>
                <select id="event-type" required class="w-full border-2 border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-[#0071BC] outline-none transition-all appearance-none cursor-pointer">
                    <option value="exam">Hari Ujian (Hijau)</option>
                    <option value="school_event" selected>Kegiatan Sekolah (Kuning)</option>
                    <option value="wfa">Kegiatan WFA (Biru)</option>
                </select>
            </div>
            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                <button type="button" class="w-full sm:w-auto px-5 py-2.5 text-slate-500 font-bold bg-slate-100 hover:bg-slate-200 rounded-xl cursor-pointer transition-colors" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-[#0071BC] hover:bg-blue-700 font-bold text-white rounded-xl cursor-pointer transition-colors shadow-md shadow-[#0071BC]/20">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let userEvents = @json($savedEvents ?? []);
    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    let currentDate = new Date(); 

    // Variabel Penampung Data API
    let nationalHolidays = {};
    let cutiBersama = {};
    let loadedYears = [];

    // FUNGSI MENGAMBIL API LIBUR NASIONAL
    async function loadHolidaysAPI(year) {
        if (loadedYears.includes(year)) return;
        
        document.getElementById('loading-api').classList.remove('hidden');
        try {
            // Menggunakan API DayOff (Gratis & Stabil untuk Indonesia)
            const response = await fetch(`https://dayoffapi.vercel.app/api?year=${year}`);
            if (!response.ok) throw new Error('API Timeout');
            
            const data = await response.json();
            data.forEach(holiday => {
                if (holiday.is_cuti) {
                    cutiBersama[holiday.tanggal] = holiday.keterangan;
                } else {
                    nationalHolidays[holiday.tanggal] = holiday.keterangan;
                }
            });
            loadedYears.push(year);
        } catch (error) {
            console.warn("Gagal terhubung ke API Libur Nasional. Menggunakan data cadangan lokal (Fallback 2026).");
            
            // Fallback Cadangan jika API down
            if (year === 2026 && !loadedYears.includes(2026)) {
                nationalHolidays = { ...nationalHolidays, "2026-01-01": "Tahun Baru 2026", "2026-01-16": "Isra Mikraj", "2026-02-17": "Tahun Baru Imlek", "2026-03-19": "Hari Raya Nyepi", "2026-03-21": "Idul Fitri", "2026-03-22": "Idul Fitri", "2026-04-03": "Wafat Yesus Kristus", "2026-04-05": "Hari Paskah", "2026-05-01": "Hari Buruh", "2026-05-14": "Kenaikan Yesus Kristus", "2026-05-27": "Idul Adha", "2026-05-31": "Hari Raya Waisak", "2026-06-01": "Hari Lahir Pancasila", "2026-06-16": "Tahun Baru Islam", "2026-08-17": "Kemerdekaan RI", "2026-08-25": "Maulid Nabi", "2026-12-25": "Hari Raya Natal" };
                cutiBersama = { ...cutiBersama, "2026-02-16": "Cuti Bersama Imlek", "2026-03-18": "Cuti Bersama Nyepi", "2026-03-20": "Cuti Bersama Idul Fitri", "2026-03-23": "Cuti Bersama Idul Fitri", "2026-03-24": "Cuti Bersama Idul Fitri", "2026-05-15": "Cuti Bersama Kenaikan YK", "2026-05-28": "Cuti Bersama Idul Adha", "2026-12-24": "Cuti Bersama Natal" };
                loadedYears.push(2026);
            }
        } finally {
    document.getElementById('loading-api').classList.add('hidden');
}
    }

    // Inisialisasi awal (Load data lalu Render)
async function initCalendar() {
    renderCalendarUI(currentDate);

    loadHolidaysAPI(currentDate.getFullYear());
}

    function renderCalendarUI(date) {
        const daysEl = document.getElementById('calendar-days');
        const year = date.getFullYear();
        const month = date.getMonth();
        document.getElementById('calendar-month-year').innerText = `${monthNames[month]} ${year} `;
        daysEl.innerHTML = "";

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Hitung event di setiap tanggal
        let eventCounts = {};
        userEvents.forEach(e => { eventCounts[e.date] = (eventCounts[e.date] || 0) + 1; });

        for (let i = 0; i < firstDay; i++) daysEl.innerHTML += `<div></div>`;

        for (let i = 1; i <= daysInMonth; i++) {
            let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            let isSunday = new Date(year, month, i).getDay() === 0;
            let css = "";
            let htmlInner = i;
            
            let baseClasses = "w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 mx-auto flex flex-col items-center justify-center rounded-lg sm:rounded-xl cursor-pointer transition-all duration-200 font-extrabold text-[12px] sm:text-[14px] lg:text-[15px] border-2 border-transparent hover:-translate-y-1 hover:shadow-md relative overflow-hidden";

            if (nationalHolidays[dateStr]) {
                css = "background-color: #B91C1C; color: white;"; 
                baseClasses += " hover:bg-red-800 hover:shadow-red-500/30";
            } else if (cutiBersama[dateStr] || isSunday) {
                css = "border-color: #B91C1C; color: #B91C1C; background-color: #FEF2F2;"; 
                baseClasses += " hover:bg-red-100";
            } else {
                let evCount = eventCounts[dateStr] || 0;
                if(evCount > 0) {
                    let firstEv = userEvents.find(e => e.date === dateStr);
                    css = `border-color: ${firstEv.color}; color: ${firstEv.color}; background-color: ${firstEv.color}15;`; 
                    baseClasses += " hover:brightness-95";
                    
                    // Indikator 2 Agenda atau lebih di tanggal yang sama (Dot bawah)
                    if(evCount > 1) {
                        htmlInner += `<span class="absolute bottom-1 flex gap-0.5 items-center justify-center">`;
                        for(let j=0; j<Math.min(evCount, 3); j++) {
                            htmlInner += `<span class="w-1.5 h-1.5 rounded-full" style="background-color: ${firstEv.color}"></span>`;
                        }
                        if(evCount > 3) htmlInner += `<span class="text-[8px] leading-none ml-0.5 font-bold" style="color: ${firstEv.color}">+</span>`;
                        htmlInner += `</span>`;
                    }
                } else {
                    css = "background-color: #F8FAFC; color: #475569;"; 
                    baseClasses += " hover:bg-blue-50 hover:border-blue-200 hover:text-[#0071BC]";
                }
            }

            daysEl.innerHTML += `<div class="py-0.5 sm:py-1"><div class="${baseClasses}" style="${css}" onclick="selectDate('${dateStr}')">${htmlInner}</div></div>`;
        }
        renderSideList(year, month);
    }

    function renderSideList(year, month) {
        const listEl = document.getElementById('event-list');
        listEl.innerHTML = "";
        let prefix = `${year}-${String(month + 1).padStart(2, '0')}`;

        // 1. Kumpulkan Event User untuk mencari Bulk (Rentang Tanggal)
        let bulkEvents = [];
        let dailyEvents = {}; // Menyimpan event harian per tanggal
        
        // Group by title dulu untuk mencari tanggal berturut-turut
        let userEventsByTitle = {};
        userEvents.forEach(e => {
            if(e.date.startsWith(prefix)) {
                if(!userEventsByTitle[e.title]) userEventsByTitle[e.title] = [];
                userEventsByTitle[e.title].push(e);
            }
        });

        for(let title in userEventsByTitle) {
            let evs = userEventsByTitle[title].sort((a,b) => a.date.localeCompare(b.date));
            let currentBulk = { start: evs[0].date, end: evs[0].date, c: evs[0].color, t: title };
            
            for(let i=1; i<evs.length; i++) {
                let prevD = new Date(currentBulk.end);
                let currD = new Date(evs[i].date);
                let diffDays = Math.round((currD - prevD)/(1000*60*60*24));
                
                if(diffDays === 1) {
                    currentBulk.end = evs[i].date; // Perpanjang tanggal end
                } else {
                    if(currentBulk.start !== currentBulk.end) {
                        bulkEvents.push(currentBulk);
                    } else {
                        if(!dailyEvents[currentBulk.start]) dailyEvents[currentBulk.start] = [];
                        dailyEvents[currentBulk.start].push({t: currentBulk.t, c: currentBulk.c});
                    }
                    currentBulk = { start: evs[i].date, end: evs[i].date, c: evs[i].color, t: title };
                }
            }
            if(currentBulk.start !== currentBulk.end) {
                bulkEvents.push(currentBulk);
            } else {
                if(!dailyEvents[currentBulk.start]) dailyEvents[currentBulk.start] = [];
                dailyEvents[currentBulk.start].push({t: currentBulk.t, c: currentBulk.c});
            }
        }

        // 2. Tambahkan Libur Nasional ke dailyEvents
        Object.entries(nationalHolidays).forEach(([d, t]) => { 
            if(d.startsWith(prefix)) {
                if(!dailyEvents[d]) dailyEvents[d] = [];
                dailyEvents[d].push({t, c: '#B91C1C', isHol: true});
            }
        });
        Object.entries(cutiBersama).forEach(([d, t]) => { 
            if(d.startsWith(prefix)) {
                if(!dailyEvents[d]) dailyEvents[d] = [];
                dailyEvents[d].push({t, c: '#B91C1C', isHol: true});
            }
        });

        // 3. Gabungkan Bulk dan Daily menjadi satu array tampilan
        let finalDisplay = [];
        
        bulkEvents.forEach(b => {
            finalDisplay.push({ type: 'bulk', sortDate: b.end, start: b.start, end: b.end, t: b.t, c: b.c });
        });

        for(let d in dailyEvents) {
            finalDisplay.push({ type: 'daily', sortDate: d, date: d, items: dailyEvents[d] });
        }

        // Urutkan berdasarkan tanggal terbaru di atas
        finalDisplay.sort((a,b) => b.sortDate.localeCompare(a.sortDate));

        if (finalDisplay.length === 0) {
            listEl.innerHTML = `
                <div class="text-center py-8 sm:py-10 flex flex-col items-center justify-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 mx-1">
                    <i class="fas fa-mug-hot text-2xl sm:text-3xl mb-3 text-slate-300"></i>
                    <span class="text-xs sm:text-sm font-bold text-slate-500">Tidak ada agenda.</span>
                </div>`;
            return;
        }

        // 4. Render HTML
        finalDisplay.forEach(block => {
            if(block.type === 'bulk') {
                let startD = parseInt(block.start.split('-')[2]);
                let endD = parseInt(block.end.split('-')[2]);
                let dateText = `${startD} - ${endD} ${monthNames[month]} ${year}`;
                
                listEl.innerHTML += `
                    <div class="border bg-slate-50 border-slate-200 hover:bg-white hover:border-blue-200 hover:shadow-md transition-all rounded-xl p-3.5 sm:p-4 flex flex-col gap-2 cursor-default">
                        <span class="font-extrabold text-slate-800 text-[12px] sm:text-[13px] border-b border-slate-200 pb-1.5">${dateText}</span>
                        <div class="flex items-start gap-2.5">
                            <div class="w-2.5 h-2.5 rounded-full mt-1 shrink-0" style="background-color: ${block.c}; box-shadow: 0 0 0 3px ${block.c}20;"></div>
                            <span class="text-slate-600 font-bold text-[11px] sm:text-[12px] leading-snug">${block.t}</span>
                        </div>
                    </div>`;
            } else {
                let dNum = parseInt(block.date.split('-')[2]);
                let dateText = `${dNum} ${monthNames[month]} ${year}`;
                let isHolGroup = block.items.some(x => x.isHol);
                let bgClass = isHolGroup ? "bg-red-50/50 border-red-100" : "bg-slate-50 border-slate-200";

                let itemsHtml = "";
                block.items.forEach(it => {
                    itemsHtml += `
                        <div class="flex items-start gap-2.5 mb-1.5 last:mb-0">
                            <div class="w-2.5 h-2.5 rounded-full mt-1 shrink-0" style="background-color: ${it.c}; box-shadow: 0 0 0 3px ${it.c}20;"></div>
                            <span class="text-slate-600 font-bold text-[11px] sm:text-[12px] leading-snug">${it.t}</span>
                        </div>`;
                });

                listEl.innerHTML += `
                    <div class="border ${bgClass} hover:bg-white hover:shadow-md transition-all rounded-xl p-3.5 sm:p-4 flex flex-col gap-2.5 cursor-default">
                        <span class="font-extrabold text-slate-800 text-[12px] sm:text-[13px] border-b border-slate-200/70 pb-1.5">${dateText}</span>
                        <div class="flex flex-col">${itemsHtml}</div>
                    </div>`;
            }
        });
    }

    window.selectDate = function(dateStr) {
        if (nationalHolidays[dateStr] || cutiBersama[dateStr] || new Date(dateStr).getDay() === 0) {
            Swal.fire({
                icon: 'info', title: 'Hari Libur', text: `Tanggal ${dateStr} adalah hari libur.`,
                confirmButtonColor: '#0071BC', customClass: { popup: 'rounded-3xl' }
            });
            return;
        }
        document.getElementById('event-start-date').value = dateStr;
        document.getElementById('event-end-date').value = ""; 
        document.getElementById('event-title').value = ""; 
        document.getElementById('modal_tambah_kegiatan').showModal();
    };

    document.getElementById('form-tambah-kegiatan').onsubmit = function(e) {
        e.preventDefault();
        const start = new Date(document.getElementById('event-start-date').value);
        const end = document.getElementById('event-end-date').value ? new Date(document.getElementById('event-end-date').value) : start;
        const type = document.getElementById('event-type').value;
        const colors = { exam: '#10B981', school_event: '#F59E0B', wfa: '#1E3A8A' };
        const title = document.getElementById('event-title').value;

        for(let d = new Date(start); d <= end; d.setDate(d.getDate()+1)) {
            let s = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            if (!nationalHolidays[s] && !cutiBersama[s] && d.getDay() !== 0) {
                userEvents.push({ date: s, title: title, type: type, color: colors[type] });
            }
        }
        
        document.getElementById('modal_tambah_kegiatan').close();
        renderCalendarUI(currentDate);
        saveCalendarData('published', true);
    };

document.getElementById('prev-month').onclick = async () => { 
    currentDate.setMonth(currentDate.getMonth() - 1);

    // render langsung
    renderCalendarUI(currentDate);

    // fetch holiday di background
    loadHolidaysAPI(currentDate.getFullYear());
};

document.getElementById('next-month').onclick = async () => { 
    currentDate.setMonth(currentDate.getMonth() + 1);

    // render langsung
    renderCalendarUI(currentDate);

    // fetch holiday di background
    loadHolidaysAPI(currentDate.getFullYear());
};

    async function saveCalendarData(statusType, isAutoSave = false) {
        if (!isAutoSave) {
            const confirmResult = await Swal.fire({
                title: statusType === 'draft' ? "Simpan Draft?" : "Publish Agenda?",
                text: statusType === 'draft' ? "Data akan disimpan secara internal." : "Agenda akan di-publish ke seluruh pengguna.",
                icon: "question", showCancelButton: true, confirmButtonColor: "#0071BC", cancelButtonColor: "#cbd5e1", confirmButtonText: "Ya, Lanjutkan", cancelButtonText: "Batal", customClass: { popup: 'rounded-3xl' }
            });
            if (!confirmResult.isConfirmed) return;
        }
        
        const btnId = statusType === 'draft' ? 'btn-draft' : 'btn-upload';
        const btn = document.getElementById(btnId);
        let originalText = "Simpan";
        
        if (btn) {
            originalText = btn.innerHTML;
            btn.innerHTML = `<i class="fas fa-circle-notch fa-spin"></i>`;
            btn.disabled = true;
        }

        try {
            const url = `{{ route('lms.headmaster.calendar.save', ['role' => $role ?? Auth::user()->role, 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? 0]) }}`;            
            const response = await fetch(url, {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ status: statusType, events: userEvents })
            });

            if (!response.ok) throw new Error();
            const result = await response.json();
            
            if (!isAutoSave) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, timer: 1500, showConfirmButton: false, customClass: { popup: 'rounded-3xl' } });
            } else {
                Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: 'Tersimpan otomatis', showConfirmButton: false, timer: 2000 });
            }
        } catch (error) {
            if (!isAutoSave) Swal.fire({ icon: 'error', title: 'Gagal', text: 'Koneksi bermasalah.', customClass: { popup: 'rounded-3xl' } });
        } finally {
            if (btn) { btn.innerHTML = originalText; btn.disabled = false; }
        }
    }

    // Eksekusi Inisialisasi
    document.addEventListener("DOMContentLoaded", initCalendar);
</script>