@include('components/sidebar-beranda', [
    'headerSideNav' => 'Jadwal Pelajaran'
])

@if (in_array(Auth::user()->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah']))
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-slate-100 min-h-screen pb-12">
        <div class="pt-6 sm:pt-8 mx-4 sm:mx-6 lg:mx-10">
            
            {{-- HEADER SECTION --}}
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 lg:mb-8 gap-5 bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border-t-4 border-t-[#0071BC] transition-all">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-[#005B94] tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-[#0071BC] shrink-0">
                            <i class="fas fa-calendar-alt text-lg"></i>
                        </div>
                        Penyusunan Jadwal
                    </h1>
                    <p class="text-slate-500 mt-2 text-xs sm:text-sm font-medium ml-[52px]">Gunakan metode Drag & Drop untuk menentukan jam mengajar guru.</p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
                    <div class="w-full sm:w-64 relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-chalkboard-teacher text-slate-400 group-hover:text-[#0071BC] transition-colors"></i>
                        </div>
                        <select id="class-selector" class="w-full border-2 border-slate-200 rounded-xl pl-11 pr-10 py-3 text-sm font-bold text-slate-700 bg-white hover:border-[#0071BC]/40 focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all cursor-pointer appearance-none">
                            <option value="" disabled selected>-- Pilih Kelas Dahulu --</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls->id }}" data-name="{{ $cls->class_name }}">{{ $cls->class_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button onclick="saveScheduleData('draft')" class="flex-1 sm:flex-none px-5 py-3 rounded-xl border-2 border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-[#0071BC] transition-all text-sm font-bold shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Draft
                        </button>
                        <button onclick="saveScheduleData('published')" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#0071BC] to-[#005B94] text-white hover:shadow-lg hover:shadow-blue-500/30 transition-all text-sm font-bold transform hover:-translate-y-0.5 border border-transparent">
                            <i class="fas fa-paper-plane"></i> Publish
                        </button>
                    </div>
                </div>
            </div>

            {{-- WORKSPACE (Tersembunyi Sebelum Kelas Dipilih) --}}
            <div id="workspace-jadwal" class="hidden flex-col xl:flex-row gap-6 lg:gap-8 items-start">
                
                {{-- KIRI: PENGATURAN JAM & DAFTAR MAPEL --}}
                <div class="w-full xl:w-[320px] shrink-0 flex flex-col gap-6">
                    
                    {{-- PANEL GENERATOR WAKTU --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6 h-fit">
                        <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                            <i class="fas fa-clock text-orange-500"></i> Pengaturan Waktu
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Jam Masuk</label>
                                    <input type="time" id="set_jam_mulai" value="07:00" class="w-full px-3 py-2 text-sm font-bold border-2 border-slate-200 rounded-lg outline-none focus:border-orange-500 bg-slate-50 focus:bg-white transition-colors">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Durasi /Sesi (Menit)</label>
                                    <input type="number" id="set_durasi_sesi" value="45" min="30" max="90" class="w-full px-3 py-2 text-sm font-bold border-2 border-slate-200 rounded-lg outline-none focus:border-orange-500 bg-slate-50 focus:bg-white transition-colors">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Total Sesi Mapel Sehari</label>
                                <input type="number" id="set_total_sesi" value="10" min="5" max="15" class="w-full px-3 py-2 text-sm font-bold border-2 border-slate-200 rounded-lg outline-none focus:border-orange-500 bg-slate-50 focus:bg-white transition-colors">
                            </div>
                            
                            <div class="bg-orange-50/50 p-3 rounded-xl border border-orange-100">
                                <label class="block text-[10px] font-extrabold text-orange-800 uppercase tracking-wider border-b border-orange-200/60 mb-2 pb-1">Istirahat 1</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <span class="text-[10px] font-bold text-orange-600 block mb-1">Setelah Sesi Ke-</span>
                                        <input type="number" id="set_ist1_setelah" value="4" class="w-full px-2 py-1.5 text-sm font-bold border-2 border-orange-200 rounded-lg outline-none focus:border-orange-500">
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-orange-600 block mb-1">Durasi (Menit)</span>
                                        <input type="number" id="set_ist1_durasi" value="15" class="w-full px-2 py-1.5 text-sm font-bold border-2 border-orange-200 rounded-lg outline-none focus:border-orange-500">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-orange-50/50 p-3 rounded-xl border border-orange-100">
                                <label class="block text-[10px] font-extrabold text-orange-800 uppercase tracking-wider border-b border-orange-200/60 mb-2 pb-1">Istirahat 2 (Opsional)</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <span class="text-[10px] font-bold text-orange-600 block mb-1">Setelah Sesi Ke-</span>
                                        <input type="number" id="set_ist2_setelah" value="8" class="w-full px-2 py-1.5 text-sm font-bold border-2 border-orange-200 rounded-lg outline-none focus:border-orange-500">
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-orange-600 block mb-1">Durasi (Menit)</span>
                                        <input type="number" id="set_ist2_durasi" value="15" class="w-full px-2 py-1.5 text-sm font-bold border-2 border-orange-200 rounded-lg outline-none focus:border-orange-500">
                                    </div>
                                </div>
                            </div>
                            
                            <button onclick="generateGrid()" class="w-full py-3 bg-slate-800 text-white font-bold text-sm rounded-xl hover:bg-slate-900 transition-colors shadow-md mt-2 flex justify-center items-center gap-2">
                                <i class="fas fa-magic"></i> Terapkan Waktu & Buat Tabel
                            </button>
                        </div>
                    </div>

                    {{-- PANEL DAFTAR MAPEL --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6 h-fit flex-1 flex flex-col max-h-[500px]">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 shrink-0">
                            <h3 class="font-extrabold text-slate-800 text-[15px] flex items-center gap-2">
                                <i class="fas fa-layer-group text-[#0071BC]"></i> Tarik Mapel
                                <span id="mapel-count" class="bg-blue-100 text-[#0071BC] text-[10px] font-bold px-2 py-1 rounded-md hidden">0 Guru</span>
                            </h3>
                            <span class="bg-red-50 text-red-500 text-[9px] font-bold px-2 py-1 rounded-md border border-red-100 uppercase tracking-wider">Max 3 Sesi/Hari</span>
                        </div>
                        
                        <div id="loading-guru" class="text-center py-6 text-slate-400 hidden">
                            <i class="fas fa-circle-notch fa-spin text-2xl mb-2"></i><br><span class="text-xs font-bold">Memuat Guru...</span>
                        </div>

                        <div id="sidebar-mapel" class="flex flex-col gap-2.5 overflow-y-auto pr-2 custom-scrollbar flex-1 pb-2">
                            {{-- Daftar mapel akan dimuat via AJAX --}}
                        </div>
                    </div>
                </div>

                {{-- KANAN: TABEL JADWAL --}}
                <div class="flex-1 w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-5 lg:p-8 relative">
                    <div class="flex justify-between items-center mb-5 shrink-0 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="font-extrabold text-lg text-slate-800">Papan Jadwal Kelas</h2>
                            <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-wider" id="info-status-jadwal">Area Drag & Drop</p>
                        </div>
                        <button onclick="clearSchedule()" class="px-4 py-2 bg-red-50 text-red-500 border border-red-200 rounded-lg text-xs font-bold hover:bg-red-500 hover:text-white transition-all shadow-sm">
                            <i class="fas fa-trash-alt mr-1"></i> Kosongkan Papan
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-inner custom-scrollbar pb-2 relative">
                        <table class="w-full min-w-[800px] lg:min-w-full border-collapse bg-white text-left">
                            <thead id="thead-jadwal" class="bg-gradient-to-r from-[#005B94] to-[#0071BC]">
                                {{-- Header Kolom Hari akan digenerate otomatis --}}
                            </thead>
                            <tbody id="tbody-jadwal" class="divide-y divide-slate-100 text-sm">
                                {{-- Baris Waktu akan digenerate otomatis --}}
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3 text-center lg:hidden animate-pulse font-bold">
                        <i class="fas fa-arrows-alt-h mr-1"></i> Geser tabel ke kanan/kiri untuk melihat hari lainnya
                    </p>
                </div>
            </div>
            
            {{-- OVERLAY SEBELUM KELAS DIPILIH --}}
            <div id="schedule-overlay" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-10 mt-6 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-5 border-4 border-white shadow-sm">
                    <i class="fas fa-lock text-3xl text-slate-300"></i>
                </div>
                <h4 class="font-extrabold text-slate-800 text-lg sm:text-xl mb-2">Jadwal Terkunci</h4>
                <p class="text-sm font-medium text-slate-500 max-w-md">Silakan pilih kelas pada menu *dropdown* di atas untuk mulai menyusun waktu dan menarik jadwal guru.</p>
            </div>

        </div>
    </div>
@endif

<style>
    /* Styling Dasar JS Generator */
    .bg-stripes-amber { background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(245, 158, 11, 0.05) 10px, rgba(245, 158, 11, 0.05) 20px); }
    .drop-zone.drag-over .slot-content { background-color: #eff6ff; border-color: #0071BC; border-style: dashed; transform: scale(0.96); box-shadow: inset 0 0 0 2px rgba(0, 113, 188, 0.1); }
    .drop-zone.drag-over .slot-content i { opacity: 1; color: #0071BC; transform: scale(1.3); }
    
    .mapel-pill { cursor: grab; display: flex; flex-direction: column; overflow: hidden; position: relative; user-select: none; }
    .mapel-pill:active { cursor: grabbing; transform: scale(0.98); }
    
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; margin: 2px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentClassId = null;
    let className = "";
    let timeConfig = []; 
    const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']; // Tambah 'Sabtu' jika perlu
    let allOtherSchedules = @json($allSchedules ?? []);
    let globalSchedules = [];

    const classSelector = document.getElementById('class-selector');
    const overlay = document.getElementById('schedule-overlay');
    const workspace = document.getElementById('workspace-jadwal');
    const sidebar = document.getElementById('sidebar-mapel');

    // 1. Handle Ganti Kelas
    classSelector.addEventListener('change', async function() {
        currentClassId = this.value;
        className = this.options[this.selectedIndex].getAttribute('data-name');
        
        overlay.classList.add('hidden');
        workspace.classList.remove('hidden');
        workspace.classList.add('flex');
        
        loadGuruMapel(currentClassId);
    });

    // ==========================================
    // 2. GENERATOR TABEL WAKTU DINAMIS
    // ==========================================
    function generateGrid() {
        const startStr = document.getElementById('set_jam_mulai').value; // "07:00"
        const durasiSesi = parseInt(document.getElementById('set_durasi_sesi').value);
        const totalSesi = parseInt(document.getElementById('set_total_sesi').value);
        
        const ist1Setelah = parseInt(document.getElementById('set_ist1_setelah').value) || -1;
        const ist1Durasi = parseInt(document.getElementById('set_ist1_durasi').value) || 0;
        
        const ist2Setelah = parseInt(document.getElementById('set_ist2_setelah').value) || -1;
        const ist2Durasi = parseInt(document.getElementById('set_ist2_durasi').value) || 0;

        timeConfig = [];
        let [h, m] = startStr.split(':').map(Number);
        let currDate = new Date(2000, 0, 1, h, m); 

        let hitungSesi = 0;
        let isIst1Done = false;
        let isIst2Done = false;

        for (let i = 1; i <= totalSesi + 2; i++) { 
            if (hitungSesi === totalSesi) break;

            if (hitungSesi === ist1Setelah && ist1Durasi > 0 && !isIst1Done) {
                let sStart = formatTime(currDate);
                currDate.setMinutes(currDate.getMinutes() + ist1Durasi);
                timeConfig.push({ type: 'break', label: 'Istirahat 1', start: sStart, end: formatTime(currDate) });
                isIst1Done = true;
                continue; 
            }

            if (hitungSesi === ist2Setelah && ist2Durasi > 0 && !isIst2Done) {
                let sStart = formatTime(currDate);
                currDate.setMinutes(currDate.getMinutes() + ist2Durasi);
                timeConfig.push({ type: 'break', label: 'Istirahat 2', start: sStart, end: formatTime(currDate) });
                isIst2Done = true;
                continue;
            }

            hitungSesi++;
            let sStart = formatTime(currDate);
            currDate.setMinutes(currDate.getMinutes() + durasiSesi);
            timeConfig.push({ type: 'session', session_no: hitungSesi, start: sStart, end: formatTime(currDate) });
        }

        renderTableGrid();
        if(currentClassId) loadGuruMapel(currentClassId); // Reload jadwal masuk slot
    }

    function formatTime(dateObj) {
        return String(dateObj.getHours()).padStart(2, '0') + ':' + String(dateObj.getMinutes()).padStart(2, '0');
    }

    function renderTableGrid() {
        const thead = document.getElementById('thead-jadwal');
        const tbody = document.getElementById('tbody-jadwal');
        
        // MENGGAMBAR HEADER (HARI)
        let thHtml = `<tr>
            <th class="p-3 sm:p-4 border-r border-[#005B94]/30 text-[10px] sm:text-xs font-bold text-white uppercase tracking-wider w-20 sm:w-24 text-center sticky left-0 bg-gradient-to-r from-[#005B94] to-[#00609a] shadow-[2px_0_5px_rgba(0,0,0,0.1)] z-30">Waktu</th>`;
        
        days.forEach(day => {
            thHtml += `<th class="p-3 sm:p-4 border-r last:border-r-0 border-[#005B94]/30 text-[10px] sm:text-xs font-extrabold text-white uppercase tracking-wider text-center min-w-[140px] shadow-inner">${day}</th>`;
        });
        thHtml += `</tr>`;
        thead.innerHTML = thHtml;

        // MENGGAMBAR BARIS (WAKTU & SLOT)
        let tbHtml = '';
        timeConfig.forEach(slot => {
            if (slot.type === 'break') {
                tbHtml += `<tr class="bg-[#FFF8ED] border-b border-amber-200">
                    <td class="p-2 sm:p-3 border-r border-amber-200 text-center text-[10px] sm:text-[11px] font-bold text-amber-700 bg-[#FFF3D6] sticky left-0 z-20 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                        <span class="block text-[8px] uppercase tracking-widest opacity-50 mb-0.5"><i class="fas fa-mug-hot"></i></span>
                        ${slot.start} <br> <span class="opacity-50 text-[9px] mt-0.5">${slot.end}</span>
                    </td>
                    <td colspan="${days.length}" class="p-2 sm:p-3 text-center text-[10px] sm:text-xs font-extrabold text-amber-600 tracking-[0.3em] uppercase bg-stripes-amber">
                        ${slot.label}
                    </td>
                </tr>`;
            } else {
                tbHtml += `<tr class="hover:bg-blue-50/20 transition-colors border-b border-slate-200">
                    <td class="p-2 sm:p-3 border-r border-slate-200 text-center text-[10px] sm:text-[11px] font-bold text-slate-600 bg-slate-50 sticky left-0 z-20 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                        <span class="block text-[8px] text-blue-500 bg-blue-100 rounded-md w-fit mx-auto px-1.5 mb-1">S-${slot.session_no}</span>
                        ${slot.start} <br> <span class="text-[9px] text-slate-400 font-medium">${slot.end}</span>
                    </td>`;
                
                days.forEach(day => {
                    tbHtml += `<td class="drop-zone p-1.5 sm:p-2 border-r last:border-r-0 border-slate-200 h-24 sm:h-28 align-top"
                                   data-day="${day}" data-start="${slot.start}"
                                   ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="drop(event)">
                                   
                                   <div class="slot-content group w-full h-full rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-300 hover:bg-blue-50 hover:border-[#0071BC] hover:text-[#0071BC] transition-all duration-200 cursor-pointer shadow-sm relative">
                                        <i class="fas fa-plus opacity-0 group-hover:opacity-100 transition-opacity transform scale-75 group-hover:scale-100"></i>
                                   </div>
                               </td>`;
                });
                tbHtml += `</tr>`;
            }
        });
        tbody.innerHTML = tbHtml;
    }

    // ==========================================
    // 3. MUAT DATA GURU & JADWAL (AJAX) - TELAH DIPERBAIKI (URL ABSOLUT)
    // ==========================================
    async function loadGuruMapel(classId) {
        document.getElementById('loading-guru').classList.remove('hidden');
        sidebar.innerHTML = '';

        try {
            // URL Absolut sesuai dengan di web.php
            const url = `/lms/{{ $schoolId }}/teacher-schedule/get-data/${classId}`;
            const response = await fetch(url);
            
            // Cek jika server tidak mengembalikan status 200 OK (contoh: 404 atau 500)
            if (!response.ok) {
                const errHtml = await response.text();
                throw new Error(`Server Error (${response.status})`);
            }
            
            const res = await response.json();

            if (res.success) {
                document.getElementById('mapel-count').textContent = `${res.available_mapels.length} Guru`;
                document.getElementById('mapel-count').classList.remove('hidden');

                res.available_mapels.forEach(m => {
                    sidebar.innerHTML += `
                        <div draggable="true" ondragstart="dragStart(event)" 
                             data-tid="${m.id}" data-tname="${m.name}" data-sid="${m.subject_id}" data-sname="${m.subject}" data-color="${m.color}"
                             class="mapel-pill p-3 sm:p-4 bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-200 gap-1.5 group">
                            <div class="absolute top-0 left-0 w-1.5 h-full" style="background:${m.color}"></div>
                            <div class="flex justify-between items-start pl-2">
                                <div class="flex-1 pr-2">
                                    <p class="text-[12px] sm:text-[13px] font-extrabold text-slate-800 leading-tight mb-1 group-hover:text-[#0071BC] transition-colors">${m.subject}</p>
                                    <p class="text-[10px] sm:text-[11px] text-slate-500 font-bold flex items-center gap-1.5">
                                        <i class="fas fa-user-circle opacity-50"></i> ${m.name}
                                    </p>
                                </div>
                                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-slate-50 flex items-center justify-center border border-slate-100 group-hover:bg-blue-50 transition-colors">
                                    <i class="fas fa-grip-vertical text-slate-300 group-hover:text-[#0071BC] text-[10px]"></i>
                                </div>
                            </div>
                        </div>`;
                });

                // Masukkan jadwal yang ada di database ke Global Memori
                if(res.data) {
                    globalSchedules = allOtherSchedules.filter(x => x.class_id != classId); 
                    
                    res.data.forEach(item => {
                        globalSchedules.push({
                            class_id: classId,
                            day_of_week: item.day_of_week,
                            start_time: item.start_time,
                            subject_id: item.subject_id,
                            teacher_id: item.teacher_id
                        });

                        const zone = document.querySelector(`.drop-zone[data-day="${item.day_of_week}"][data-start="${item.start_time}"]`);
                        if(zone) {
                            renderItemToSlot(zone.querySelector('.slot-content'), {
                                teacher_id: item.teacher_id, teacher_name: item.teacher_name,
                                subject_id: item.subject_id, subject_name: item.subject_name,
                                color: item.color
                            });
                        }
                    });
                }
            } else {
                throw new Error(res.message || "Gagal mengambil data dari server");
            }
        } catch (error) {
            console.error("Error Detail:", error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat Data',
                text: error.message || 'Pastikan server merespon dengan benar.',
                confirmButtonColor: '#0071BC'
            });
        } finally {
            document.getElementById('loading-guru').classList.add('hidden');
        }
    }

    // ==========================================
    // 4. DRAG & DROP LOGIC
    // ==========================================
    function dragStart(e) {
        const t = e.currentTarget;
        draggedData = {
            tid: t.dataset.tid, tname: t.dataset.tname,
            sid: t.dataset.sid, sname: t.dataset.sname, color: t.dataset.color
        };
        setTimeout(() => e.target.classList.add('opacity-50'), 0);
    }
    
    document.addEventListener('dragend', (e) => { if(e.target.classList) e.target.classList.remove('opacity-50'); });
    function dragOver(e) { e.preventDefault(); e.currentTarget.classList.add('drag-over'); }
    function dragLeave(e) { e.currentTarget.classList.remove('drag-over'); }
    
    function drop(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('drag-over');
        if(!draggedData) return;

        const dayTarget = e.currentTarget.dataset.day;
        const timeTarget = e.currentTarget.dataset.start;
        const subjectId = draggedData.sid.toString();
        const teacherId = draggedData.tid.toString();
        const currClass = classSelector.value.toString();

        // VALIDASI 1: BENTROK GURU LINTAS KELAS
        const isTeacherBusy = globalSchedules.some(s => s.day_of_week === dayTarget && s.start_time === timeTarget && s.teacher_id.toString() === teacherId && s.class_id.toString() !== currClass);
        if (isTeacherBusy) {
            Swal.fire({ icon: 'warning', title: 'Guru Bentrok!', text: `Bpk/Ibu ${draggedData.tname} sudah mengajar di kelas lain pada hari ${dayTarget} jam ${timeTarget}.`, confirmButtonColor: '#d33' });
            return;
        }

        // VALIDASI 2: MAX 3 SESI MAPEL YANG SAMA / HARI
        let countSubjectToday = 0;
        document.querySelectorAll(`.drop-zone[data-day="${dayTarget}"] .slot-content[data-saved="true"]`).forEach(slot => {
            if (slot.dataset.sid === subjectId && slot.closest('.drop-zone').dataset.start !== timeTarget) countSubjectToday++;
        });

        if (countSubjectToday >= 3) {
            Swal.fire({ icon: 'error', title: 'Batas Tercapai!', text: `Mapel ${draggedData.sname} sudah dimasukkan 3 sesi di hari ${dayTarget}.`, confirmButtonColor: '#f59e0b' });
            return;
        }

        // SUKSES RENDER
        const currentSlot = e.currentTarget.querySelector('.slot-content');
        renderItemToSlot(currentSlot, {
            teacher_id: draggedData.tid, teacher_name: draggedData.tname,
            subject_id: draggedData.sid, subject_name: draggedData.sname, color: draggedData.color
        });

        // UPDATE MEMORI JADWAL
        globalSchedules = globalSchedules.filter(s => !(s.day_of_week === dayTarget && s.start_time === timeTarget && s.class_id.toString() === currClass));
        globalSchedules.push({ class_id: currClass, day_of_week: dayTarget, start_time: timeTarget, subject_id: subjectId, teacher_id: teacherId });
    }

    function renderItemToSlot(slot, data) {
        slot.className = "slot-content w-full h-full rounded-xl p-2 flex flex-col justify-center text-left relative group transition-all cursor-default shadow-sm border";
        slot.style.backgroundColor = data.color + '15'; 
        slot.style.borderColor = data.color + '40';
        
        slot.dataset.saved = "true";
        Object.keys(data).forEach(key => slot.dataset[key === 'teacher_id' ? 'tid' : key === 'teacher_name' ? 'tname' : key === 'subject_id' ? 'sid' : key === 'subject_name' ? 'sname' : key] = data[key]);

        slot.innerHTML = `
            <div class="absolute top-0 left-0 w-1 sm:w-1.5 h-full rounded-l-xl" style="background:${data.color}"></div>
            <div class="pl-1.5 sm:pl-2 flex flex-col h-full justify-center">
                <p class="text-[9px] sm:text-[11px] font-extrabold text-slate-800 leading-tight mb-1 uppercase line-clamp-2" style="color: ${data.color}">${data.subject_name}</p>
                <p class="text-[8px] sm:text-[10px] font-bold text-slate-600 w-full flex items-center gap-1.5 truncate">
                    <i class="fas fa-user-circle opacity-50"></i> ${data.teacher_name}
                </p>
            </div>
            <button onclick="clearSlot(this, event)" class="absolute -top-1.5 -right-1.5 bg-red-500 text-white w-5 h-5 sm:w-6 sm:h-6 rounded-full text-[9px] sm:text-[11px] opacity-0 group-hover:opacity-100 transition-all shadow-md transform scale-75 group-hover:scale-100 flex items-center justify-center hover:bg-red-600 z-10 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        `;
    }

    function clearSlot(btn, e) {
        e.stopPropagation();
        const slot = btn.closest('.slot-content');
        const zone = slot.closest('.drop-zone');
        
        globalSchedules = globalSchedules.filter(s => !(s.day_of_week === zone.dataset.day && s.start_time === zone.dataset.start && s.class_id.toString() === currentClassId));

        slot.innerHTML = '<i class="fas fa-plus opacity-0 group-hover:opacity-100 transition-opacity transform scale-75 group-hover:scale-100"></i>';
        slot.className = 'slot-content group w-full h-full rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-300 hover:bg-blue-50 hover:border-[#0071BC] hover:text-[#0071BC] transition-all duration-200 cursor-pointer relative';
        slot.style = '';
        delete slot.dataset.saved;
    }

    function clearSchedule() {
        Swal.fire({ title: 'Kosongkan Papan?', text: "Semua mapel di layar akan dihapus.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Ya, Bersihkan' })
        .then((res) => {
            if (res.isConfirmed) {
                globalSchedules = globalSchedules.filter(s => s.class_id.toString() !== currentClassId);
                document.querySelectorAll('.cell-content, .slot-content').forEach(cell => {
                    if(cell.dataset.saved === "true") clearSlot(cell.querySelector('button'), new Event('click'));
                });
            }
        });
    }

    // ==========================================
    // 5. PENYIMPANAN DATA (AJAX) - URL ABSOLUT
    // ==========================================
    async function saveScheduleData(status) {
        if (!currentClassId) return;

        let finalSchedules = [];
        document.querySelectorAll('.slot-content[data-saved="true"]').forEach(slot => {
            const zone = slot.closest('.drop-zone');
            finalSchedules.push({
                day: zone.dataset.day, start_time: zone.dataset.start,
                teacher_id: slot.dataset.tid, teacher_name: slot.dataset.tname,
                subject_id: slot.dataset.sid, subject_name: slot.dataset.sname, color: slot.dataset.color
            });
        });

        if (finalSchedules.length === 0) {
            Swal.fire({icon: 'warning', title: 'Jadwal Kosong', text: 'Tarik minimal 1 mapel ke jadwal.'});
            return;
        }

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            // URL Absolut sesuai dengan di web.php
            const url = `/lms/{{ $role }}/{{ $schoolName }}/{{ $schoolId }}/teacher-schedule/save`;
            const response = await fetch(url, {
                method: 'POST', 
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ 
                    class_id: currentClassId, 
                    class_name: className, 
                    status: status, 
                    schedules: finalSchedules 
                })
            });

            if (!response.ok) {
                const errHtml = await response.text();
                throw new Error(`Server Error (${response.status})`);
            }

            const res = await response.json();
            
            if (res.success) {
                Swal.fire({icon: 'success', title: 'Berhasil!', text: res.message, confirmButtonColor: '#0071BC'});
            } else {
                throw new Error(res.message);
            }
        } catch (e) { 
            console.error("Error Detail:", e);
            Swal.fire('Error', e.message || 'Kesalahan jaringan. Gagal menyimpan jadwal.', 'error'); 
        }
    }

    // Auto-generate saat load pertama kali
    generateGrid();
</script>