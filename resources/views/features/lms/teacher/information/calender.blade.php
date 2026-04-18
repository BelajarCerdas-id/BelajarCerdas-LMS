@include('components/sidebar-beranda', [
    'headerSideNav' => 'Kalender Akademik'
])

@if (Auth::user()->role === 'Guru')
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-slate-100 min-h-screen pb-12">
        <div class="pt-8 mx-6 lg:mx-10">
            
            <div class="sticky top-[85px] lg:top-[100px] z-40 flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-5 bg-white/95 backdrop-blur-xl p-5 lg:p-6 rounded-2xl shadow-sm border-t-4 border-t-[#0071BC] transition-all">
                <div>
                    <h1 class="text-2xl font-extrabold text-[#005B94] tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-[#0071BC]">
                            <i class="fas fa-calendar-check text-lg"></i>
                        </div>
                        Kalender Akademik 2026
                    </h1>
                    <p class="text-slate-500 mt-2 text-sm font-medium ml-[52px]">Kelola agenda dan sinkronisasi libur nasional secara otomatis.</p>
                </div>
                <div class="flex gap-3 w-full md:w-auto">
                    <button id="btn-draft" onclick="saveCalendarData('draft')" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border-2 border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-[#0071BC] transition-all text-sm font-bold shadow-sm cursor-pointer">
                        <i class="fas fa-file-lines"></i> Simpan Draft
                    </button>
                    <button id="btn-upload" onclick="saveCalendarData('published')" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#0071BC] to-[#005B94] text-white hover:shadow-lg hover:shadow-blue-500/30 transition-all text-sm font-bold cursor-pointer transform hover:-translate-y-0.5 border border-transparent">
                        <i class="fas fa-cloud-arrow-up"></i> Upload & Publish
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">
                
                <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-200 p-6 lg:p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-full -z-0 opacity-60 pointer-events-none"></div>
                    
                    <div class="flex justify-between items-center mb-8 relative z-10">
                        <h2 id="calendar-month-year" class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight"></h2>
                        <div class="flex gap-1.5 bg-slate-50 p-1.5 rounded-xl border border-slate-200">
                            <button id="prev-month" class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-white hover:shadow-sm transition-all cursor-pointer text-slate-500 hover:text-[#0071BC] focus:outline-none">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button id="next-month" class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-white hover:shadow-sm transition-all cursor-pointer text-slate-500 hover:text-[#0071BC] focus:outline-none">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-7 gap-2 text-center text-xs lg:text-sm font-extrabold text-slate-500 mb-6 uppercase tracking-wider bg-slate-50 py-3 rounded-xl relative z-10 border border-slate-100">
                        <div class="text-red-500">Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div class="text-[#0071BC]">Sab</div>
                    </div>
                    
                    <div id="calendar-days" class="grid grid-cols-7 gap-y-4 lg:gap-y-6 gap-x-2 text-center text-md font-bold relative z-10"></div>
                </div>

                <div class="flex flex-col gap-6">
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 lg:p-7">
                        <h3 class="font-extrabold text-slate-800 mb-5 text-[17px] flex items-center gap-2 pb-4 border-b border-slate-100">
                            <i class="fas fa-palette text-[#0071BC]"></i> Panduan Warna
                        </h3>
                        <div class="flex flex-col gap-3.5 text-[13px] font-bold text-slate-600">
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md bg-[#B91C1C] shadow-sm"></div> <span>Libur Nasional</span>
                            </div>
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md border-2 border-[#B91C1C] bg-red-50"></div> <span>Cuti / Libur Sekolah</span>
                            </div>
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md border-2 border-[#10B981] bg-emerald-50"></div> <span>Hari Ujian</span>
                            </div>
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md border-2 border-[#F59E0B] bg-amber-50"></div> <span>Kegiatan Sekolah</span>
                            </div>
                            <div class="flex items-center gap-3 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-5 h-5 rounded-md border-2 border-[#1E3A8A] bg-blue-50"></div> <span>Kegiatan WFA</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 lg:p-7 flex flex-col max-h-[480px]">
                        <h3 class="font-extrabold text-slate-800 mb-5 text-[17px] flex items-center gap-2 pb-4 border-b border-slate-100 shrink-0">
                            <i class="fas fa-list-check text-[#0071BC]"></i> Agenda Bulan Ini
                        </h3>
                        <div id="event-list" class="flex flex-col gap-3 text-sm overflow-y-auto pr-3 custom-scrollbar flex-1 pb-2">
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<dialog id="modal_tambah_kegiatan" class="modal backdrop:bg-slate-900/40 backdrop:backdrop-blur-sm transition-all">
    <div class="modal-box bg-white w-11/12 max-w-md rounded-3xl p-8 shadow-2xl border border-slate-100">
        <h3 class="font-extrabold text-xl text-slate-800 mb-6 flex items-center gap-3">
            <div class="w-12 h-12 bg-blue-50 text-[#0071BC] rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-plus text-xl"></i>
            </div>
            Tambah Agenda Baru
        </h3>
        <form id="form-tambah-kegiatan" class="space-y-5">
            <div>
                <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-2">Judul Kegiatan</label>
                <input type="text" id="event-title" required placeholder="Contoh: Ujian Tengah Semester" class="w-full border-2 border-slate-200 bg-slate-50 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-[#0071BC] outline-none transition-all placeholder:font-medium placeholder:text-slate-400">
            </div>
            <div class="grid grid-cols-2 gap-4">
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
            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                <button type="button" class="px-5 py-2.5 text-slate-500 font-bold bg-slate-100 hover:bg-slate-200 rounded-xl cursor-pointer transition-colors" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="px-6 py-2.5 bg-[#0071BC] hover:bg-blue-700 font-bold text-white rounded-xl cursor-pointer transition-colors shadow-md shadow-[#0071BC]/20">Simpan Agenda</button>
            </div>
        </form>
    </div>
</dialog>

<style>
    /* Styling Scrollbar Estetik */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let userEvents = @json($savedEvents ?? []);
    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    let currentDate = new Date(); 

    const nationalHolidays = {
        "2026-01-01": "Tahun Baru 2026", "2026-01-16": "Isra Mikraj",
        "2026-02-17": "Tahun Baru Imlek", "2026-03-19": "Hari Raya Nyepi",
        "2026-03-21": "Idul Fitri", "2026-03-22": "Idul Fitri",
        "2026-04-03": "Wafat Yesus Kristus", "2026-04-05": "Hari Paskah",
        "2026-05-01": "Hari Buruh", "2026-05-14": "Kenaikan Yesus Kristus",
        "2026-05-27": "Idul Adha", "2026-05-31": "Hari Raya Waisak",
        "2026-06-01": "Hari Lahir Pancasila", "2026-06-16": "Tahun Baru Islam",
        "2026-08-17": "Kemerdekaan RI", "2026-08-25": "Maulid Nabi",
        "2026-12-25": "Hari Raya Natal"
    };

    const cutiBersama = {
        "2026-02-16": "Cuti Bersama Imlek", "2026-03-18": "Cuti Bersama Nyepi",
        "2026-03-20": "Cuti Bersama Idul Fitri", "2026-03-23": "Cuti Bersama Idul Fitri",
        "2026-03-24": "Cuti Bersama Idul Fitri", "2026-05-15": "Cuti Bersama Kenaikan YK",
        "2026-05-28": "Cuti Bersama Idul Adha", "2026-12-24": "Cuti Bersama Natal"
    };

    function renderCalendar(date) {
        const daysEl = document.getElementById('calendar-days');
        const year = date.getFullYear();
        const month = date.getMonth();
        document.getElementById('calendar-month-year').innerText = `${monthNames[month]} ${year}`;
        daysEl.innerHTML = "";

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        for (let i = 0; i < firstDay; i++) daysEl.innerHTML += `<div></div>`;

        for (let i = 1; i <= daysInMonth; i++) {
            let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            let isSunday = new Date(year, month, i).getDay() === 0;
            let css = "";
            
            // Base Class diubah agar tidak terkesan dominan putih
            let baseClasses = "w-11 h-11 lg:w-12 lg:h-12 mx-auto flex items-center justify-center rounded-xl cursor-pointer transition-all duration-200 font-extrabold text-[15px] border-2 border-transparent hover:-translate-y-1 hover:shadow-md ";

            if (nationalHolidays[dateStr]) {
                css = "background-color: #B91C1C; color: white;"; 
                baseClasses += " hover:bg-red-800 hover:shadow-red-500/30";
            } else if (cutiBersama[dateStr] || isSunday) {
                css = "border-color: #B91C1C; color: #B91C1C; background-color: #FEF2F2;"; // red-50
                baseClasses += " hover:bg-red-100";
            } else {
                let ev = userEvents.find(e => e.date === dateStr);
                if(ev) {
                    css = `border-color: ${ev.color}; color: ${ev.color}; background-color: ${ev.color}10;`; // Warna 10% opacity
                    baseClasses += " hover:brightness-95";
                } else {
                    // Tanggal biasa
                    css = "background-color: #F8FAFC; color: #475569;"; // slate-50 text-slate-600
                    baseClasses += " hover:bg-blue-50 hover:border-blue-200 hover:text-[#0071BC]";
                }
            }

            daysEl.innerHTML += `<div class="py-1"><div class="${baseClasses}" style="${css}" onclick="selectDate('${dateStr}')">${i}</div></div>`;
        }
        renderSideList(year, month);
    }

    function renderSideList(year, month) {
        const listEl = document.getElementById('event-list');
        listEl.innerHTML = "";
        let prefix = `${year}-${String(month + 1).padStart(2, '0')}`;
        let all = [];

        Object.entries(nationalHolidays).forEach(([d, t]) => { if(d.startsWith(prefix)) all.push({d, t, c: '#B91C1C', isHol: true}); });
        Object.entries(cutiBersama).forEach(([d, t]) => { if(d.startsWith(prefix)) all.push({d, t, c: '#B91C1C', isHol: true}); });
        userEvents.forEach(e => { if(e.date.startsWith(prefix)) all.push({d: e.date, t: e.title, c: e.color, isHol: false}); });

        all.sort((a,b) => a.d.localeCompare(b.d));

        if (all.length === 0) {
            listEl.innerHTML = `
                <div class="text-center py-10 flex flex-col items-center justify-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                    <i class="fas fa-mug-hot text-3xl mb-3 text-slate-300"></i>
                    <span class="text-sm font-bold text-slate-500">Tidak ada agenda.</span>
                </div>`;
            return;
        }

        all.forEach(item => {
            let bgClass = item.isHol ? "bg-red-50 border-red-100" : "bg-slate-50 border-slate-200";
            listEl.innerHTML += `
                <div class="group border ${bgClass} hover:bg-white hover:border-blue-200 hover:shadow-md transition-all rounded-xl p-3.5 flex items-start gap-3 cursor-default">
                    <div class="w-3 h-3 rounded-full mt-1.5 shrink-0" style="background-color: ${item.c}; box-shadow: 0 0 0 3px ${item.c}20;"></div>
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-800 text-[13px] group-hover:text-[#0071BC] transition-colors">${item.d.split('-')[2]} ${monthNames[month]} ${year}</span>
                        <span class="text-slate-500 font-medium text-[12px] mt-0.5 leading-snug">${item.t}</span>
                    </div>
                </div>`;
        });
    }

    window.selectDate = function(dateStr) {
        if (nationalHolidays[dateStr] || cutiBersama[dateStr] || new Date(dateStr).getDay() === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Hari Libur',
                text: `Tanggal ${dateStr} adalah hari libur, tidak dapat ditambahkan agenda.`,
                confirmButtonColor: '#0071BC'
            });
            return;
        }
        document.getElementById('event-start-date').value = dateStr;
        document.getElementById('modal_tambah_kegiatan').showModal();
    };

    document.getElementById('form-tambah-kegiatan').onsubmit = function(e) {
        e.preventDefault();
        const start = new Date(document.getElementById('event-start-date').value);
        const end = document.getElementById('event-end-date').value ? new Date(document.getElementById('event-end-date').value) : start;
        const type = document.getElementById('event-type').value;
        const colors = { exam: '#10B981', school_event: '#F59E0B', wfa: '#1E3A8A' };

        for(let d = new Date(start); d <= end; d.setDate(d.getDate()+1)) {
            let localYear = d.getFullYear();
            let localMonth = String(d.getMonth() + 1).padStart(2, '0');
            let localDay = String(d.getDate()).padStart(2, '0');
            let s = `${localYear}-${localMonth}-${localDay}`;
            
            if (!nationalHolidays[s] && !cutiBersama[s] && d.getDay() !== 0) {
                userEvents = userEvents.filter(x => x.date !== s);
                userEvents.push({ date: s, title: document.getElementById('event-title').value, type: type, color: colors[type] });
            }
        }
        
        document.getElementById('modal_tambah_kegiatan').close();
        renderCalendar(currentDate);
        
        // Auto Save ke database diam-diam
        saveCalendarData('published', true);
    };

    document.getElementById('prev-month').onclick = () => { currentDate.setMonth(currentDate.getMonth()-1); renderCalendar(currentDate); };
    document.getElementById('next-month').onclick = () => { currentDate.setMonth(currentDate.getMonth()+1); renderCalendar(currentDate); };

    async function saveCalendarData(statusType, isAutoSave = false) {
        if (!isAutoSave) {
            const confirmResult = await Swal.fire({
                title: statusType === 'draft' ? "Simpan Draft?" : "Publish Agenda?",
                text: statusType === 'draft' ? "Data akan disimpan secara internal." : "Agenda akan di-publish ke seluruh pengguna yang relevan.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#0071BC",
                cancelButtonColor: "#cbd5e1",
                confirmButtonText: "Ya, Lanjutkan",
                cancelButtonText: "Batal"
            });
            if (!confirmResult.isConfirmed) return;
        }
        
        const btnId = statusType === 'draft' ? 'btn-draft' : 'btn-upload';
        const btn = document.getElementById(btnId);
        let originalText = "Simpan";
        
        if (btn) {
            originalText = btn.innerHTML;
            btn.innerHTML = `<i class="fas fa-circle-notch fa-spin"></i> Menyimpan...`;
            btn.disabled = true;
        }

        try {
            const url = `{{ route('lms.teacherCalendar.save', ['role' => $role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}`;
            
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ status: statusType, events: userEvents })
            });

            if (!response.ok) throw new Error(`HTTP Error Status: ${response.status}`);

            const result = await response.json();
            
            if (!isAutoSave) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message || "Data berhasil disimpan.", timer: 1500, showConfirmButton: false });
            } else {
                // Notifikasi kecil di pojok untuk autosave
                Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: 'Tersimpan otomatis', showConfirmButton: false, timer: 2000 });
            }
            
        } catch (error) {
            console.error("Fetch Error Details:", error);
            if (!isAutoSave) {
                Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: 'Terjadi kesalahan sistem, pastikan koneksi internet stabil.', confirmButtonColor: '#0071BC' });
            }
        } finally {
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    }

    document.addEventListener("DOMContentLoaded", () => renderCalendar(currentDate));
</script>