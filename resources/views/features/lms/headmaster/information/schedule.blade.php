@include('components/sidebar-beranda', [
    'headerSideNav' => 'Jadwal Pelajaran'
])

@if (in_array(Auth::user()->role, ['Kepala Sekolah', 'Wakil Kepala Sekolah']))
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-slate-100 min-h-screen pb-12">
        <div class="pt-6 sm:pt-8 mx-4 sm:mx-6 lg:mx-10">
            
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

            <div class="flex flex-col xl:flex-row gap-6 lg:gap-8 items-start">
                
                <div class="w-full xl:w-[320px] shrink-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6 h-fit">
                        <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate-100">
                            <h3 class="font-extrabold text-slate-800 text-[17px] flex items-center gap-2">
                                <i class="fas fa-layer-group text-[#0071BC]"></i> Data Mapel
                            </h3>
                            <span id="mapel-count" class="bg-blue-100 text-[#0071BC] text-[10px] font-bold px-2 py-1 rounded-md hidden">0 Tersedia</span>
                        </div>
                        
                        <div id="sidebar-mapel" class="flex flex-col gap-4 max-h-[300px] md:max-h-[400px] xl:max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                            <div class="flex flex-col items-center justify-center text-center py-8 sm:py-12 px-4 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white rounded-full flex items-center justify-center shadow-sm mb-3 sm:mb-4 text-slate-300 text-xl sm:text-2xl">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-500">Belum ada data</p>
                                <p class="text-xs font-medium text-slate-400 mt-1">Silakan pilih kelas terlebih dahulu.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sm:p-5 lg:p-8 relative">
                    <div id="schedule-overlay" class="absolute inset-0 bg-slate-900/5 backdrop-blur-[2px] z-20 flex items-center justify-center transition-all rounded-2xl">
                        <div class="text-center bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-slate-100 max-w-sm w-full mx-4">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5">
                                <i class="fas fa-lock text-2xl sm:text-3xl text-slate-400"></i>
                            </div>
                            <h4 class="font-extrabold text-slate-800 text-base sm:text-lg mb-2">Jadwal Terkunci</h4>
                            <p class="text-xs sm:text-sm font-medium text-slate-500">Pilih kelas pada menu di atas untuk mulai menyusun jadwal.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm custom-scrollbar pb-2">
                        <table class="w-full min-w-[800px] lg:min-w-full border-collapse bg-white">
                            <thead class="bg-gradient-to-r from-[#005B94] to-[#0071BC]">
                                <tr>
                                    <th class="p-3 sm:p-4 border-r border-[#005B94]/30 text-[10px] sm:text-xs font-bold text-white uppercase tracking-wider w-20 sm:w-24 text-center">Waktu</th>
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                                        <th class="p-3 sm:p-4 border-r last:border-r-0 border-[#005B94]/30 text-[10px] sm:text-xs font-bold text-white uppercase tracking-wider text-center w-[16%] shadow-inner">{{ $day }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $slot)
                                    @if($slot['is_break'])
                                        <tr class="bg-[#FFF8ED] border-b border-amber-200">
                                            <td class="p-2 sm:p-3 border-r border-amber-200 text-center text-[10px] sm:text-[11px] font-bold text-amber-700 bg-[#FFF3D6]">{{ $slot['start'] }}</td>
                                            <td colspan="5" class="p-2 sm:p-3 text-center text-[10px] sm:text-xs font-extrabold text-amber-600 tracking-[0.2em] uppercase bg-stripes-amber">
                                                <div class="flex items-center justify-center gap-2">
                                                    <i class="fas fa-utensils"></i> Waktu Istirahat
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="group/row hover:bg-blue-50/30 transition-colors border-b border-slate-200 last:border-b-0">
                                            <td class="p-2 sm:p-3 border-r border-slate-200 text-center text-[10px] sm:text-[11px] font-bold text-slate-600 bg-slate-50">
                                                {{ $slot['start'] }} <br> 
                                                <span class="text-[9px] text-slate-400 font-medium mt-1 inline-block">{{ $slot['end'] }}</span>
                                            </td>
                                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                                                <td class="drop-zone p-1.5 sm:p-2 border-r last:border-r-0 border-slate-200 h-24 sm:h-28 align-top"
                                                    data-day="{{ $day }}" data-start="{{ $slot['start'] }}"
                                                    ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="drop(event)">
                                                    
                                                    <div class="slot-content group w-full h-full rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-300 hover:bg-blue-50 hover:border-[#0071BC] hover:text-[#0071BC] transition-all duration-200 cursor-pointer">
                                                        <i class="fas fa-plus opacity-0 group-hover:opacity-100 transition-opacity transform scale-75 group-hover:scale-100"></i>
                                                    </div>

                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 text-center lg:hidden animate-pulse">
                        <i class="fas fa-arrows-alt-h mr-1"></i> Geser tabel ke kanan/kiri
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
    /* Styling strip untuk jam istirahat */
    .bg-stripes-amber { 
        background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(245, 158, 11, 0.05) 10px, rgba(245, 158, 11, 0.05) 20px); 
    }
    /* Efek saat slot di-drag over */
    .drop-zone.drag-over .slot-content { 
        background-color: #eff6ff;
        border-color: #0071BC; 
        border-style: solid;
        transform: scale(0.98);
        box-shadow: inset 0 0 0 2px rgba(0, 113, 188, 0.1);
    }
    .drop-zone.drag-over .slot-content i {
        opacity: 1;
        color: #0071BC;
        transform: scale(1.2);
    }
    /* Scrollbar minimalis untuk sidebar & tabel */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; margin: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const classSelector = document.getElementById('class-selector');
    const overlay = document.getElementById('schedule-overlay');
    const sidebar = document.getElementById('sidebar-mapel');
    const mapelCount = document.getElementById('mapel-count');
    let draggedData = null;

    // ==========================================
    // DATA GLOBAL UNTUK VALIDASI LINTAS KELAS
    // ==========================================
    // Variabel ini akan diisi dengan data dari server, atau kosong di awal
    let globalSchedules = @json($allSchedules ?? []);

    // 1. Handle Ganti Kelas
    classSelector.addEventListener('change', async function() {
        const classId = this.value;
        if(!classId) {
            overlay.classList.remove('hidden');
            mapelCount.classList.add('hidden');
            return;
        }

        overlay.classList.add('hidden');
        sidebar.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12">
                <i class="fas fa-circle-notch fa-spin text-[#0071BC] text-3xl mb-3"></i>
                <p class="text-xs font-bold text-slate-500">Memuat Data Guru...</p>
            </div>
        `;
        mapelCount.classList.add('hidden');
        
        document.querySelectorAll('.slot-content').forEach(slot => {
            slot.innerHTML = '<i class="fas fa-plus opacity-0 group-hover:opacity-100 transition-opacity transform scale-75 group-hover:scale-100"></i>';
            slot.className = 'slot-content group w-full h-full rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-300 hover:bg-blue-50 hover:border-[#0071BC] hover:text-[#0071BC] transition-all duration-200 cursor-pointer';
            slot.style = '';
            delete slot.dataset.saved;
        });

        try {
            // Memanggil AJAX sesuai dengan jalur Rute
            const response = await fetch(`/lms/{{ $schoolId }}/teacher-schedule/get-data/${classId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();

            sidebar.innerHTML = '';
            
            if (result.success === false) {
                sidebar.innerHTML = `
                    <div class="text-center py-4 px-3 border-2 border-red-200 bg-red-50 rounded-xl">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                        <p class="text-xs font-bold text-red-600 mb-1">Error Server:</p>
                        <p class="text-[10px] text-red-500 font-medium break-words text-left">${result.message}</p>
                    </div>`;
                mapelCount.classList.add('hidden');
                return;
            }

            if(!result.available_mapels || result.available_mapels.length === 0) {
                sidebar.innerHTML = `
                    <div class="text-center py-10 border-2 border-dashed border-red-200 rounded-xl bg-red-50">
                        <i class="fas fa-exclamation-triangle text-red-400 text-3xl mb-3"></i>
                        <p class="text-sm font-bold text-red-500">Tidak ada guru tersedia</p>
                    </div>`;
            } else {
                mapelCount.textContent = `${result.available_mapels.length} Tersedia`;
                mapelCount.classList.remove('hidden');

                result.available_mapels.forEach(m => {
                    sidebar.innerHTML += `
                        <div draggable="true" ondragstart="dragStart(event)" 
                             data-tid="${m.id}" data-tname="${m.name}" data-sid="${m.subject_id}" data-sname="${m.subject}" data-color="${m.color}"
                             class="relative p-3 sm:p-4 bg-white border border-slate-200 rounded-xl shadow-sm cursor-grab hover:shadow-md hover:border-slate-300 hover:-translate-y-1 transition-all duration-200 flex flex-col gap-1 overflow-hidden group">
                            <div class="absolute top-0 left-0 w-1.5 h-full" style="background:${m.color}"></div>
                            <div class="flex justify-between items-start pl-2 sm:pl-3">
                                <div class="flex-1 pr-2 sm:pr-3">
                                    <p class="text-[12px] sm:text-[14px] font-extrabold text-slate-800 leading-tight mb-1">${m.subject}</p>
                                    <p class="text-[10px] sm:text-[12px] text-slate-500 font-semibold flex items-center gap-1.5">
                                        <i class="fas fa-user-circle text-slate-300"></i> ${m.name}
                                    </p>
                                </div>
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-slate-50 flex items-center justify-center group-hover:bg-blue-50 transition-colors border border-slate-100 mt-0.5 sm:mt-1">
                                    <i class="fas fa-grip-vertical text-slate-400 group-hover:text-[#0071BC] text-[10px] sm:text-[12px]"></i>
                                </div>
                            </div>
                        </div>`;
                });
            }

            // RE-RENDER JADWAL YANG ADA DARI DATABASE KE UI
            if(result.data) {
                result.data.forEach(item => {
                    const zone = document.querySelector(`.drop-zone[data-day="${item.day_of_week}"][data-start="${item.start_time}"]`);
                    if(zone) renderItemToSlot(zone.querySelector('.slot-content'), item);
                });
            }
        } catch (e) { 
            console.error(e); 
            sidebar.innerHTML = `
                <div class="text-center py-5 border-2 border-red-200 bg-red-50 rounded-xl">
                    <p class="text-sm font-bold text-red-600 mb-1">Gagal memuat UI</p>
                    <p class="text-xs text-red-500 break-words px-3">${e.message}</p>
                </div>`;
        }
    });

    function dragStart(e) {
        const t = e.currentTarget;
        draggedData = {
            tid: t.dataset.tid, tname: t.dataset.tname,
            sid: t.dataset.sid, sname: t.dataset.sname,
            color: t.dataset.color
        };
        setTimeout(() => e.target.classList.add('opacity-50'), 0);
    }
    
    document.addEventListener('dragend', (e) => {
        if(e.target.classList) e.target.classList.remove('opacity-50');
    });

    function dragOver(e) { e.preventDefault(); e.currentTarget.classList.add('drag-over'); }
    function dragLeave(e) { e.currentTarget.classList.remove('drag-over'); }
    
    // ==========================================
    // FUNGSI DROP DENGAN 3 LAPIS VALIDASI KETAT
    // ==========================================
    function drop(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('drag-over');
        
        if(!draggedData) return;

        const dayTarget = e.currentTarget.dataset.day;
        const timeTarget = e.currentTarget.dataset.start;
        const subjectId = draggedData.sid.toString();
        const teacherId = draggedData.tid.toString();
        const currentClassId = classSelector.value.toString();

        // ---------------------------------------------------------
        // VALIDASI 1: GURU BENTROK
        // Guru tidak boleh mengajar di kelas lain pada hari & jam yang sama
        // ---------------------------------------------------------
        const isTeacherBusy = globalSchedules.some(schedule => {
            return schedule.day_of_week === dayTarget && 
                   schedule.start_time === timeTarget && 
                   schedule.teacher_id.toString() === teacherId &&
                   schedule.class_id.toString() !== currentClassId; 
        });

        if (isTeacherBusy) {
            Swal.fire({ 
                icon: 'warning', 
                title: 'Guru Bentrok!', 
                text: `Guru ${draggedData.tname} sudah dijadwalkan mengajar di kelas lain pada hari ${dayTarget} jam ${timeTarget}.`, 
                confirmButtonColor: '#d33' 
            });
            return; // 🛑 Hentikan Drop
        }

        // ---------------------------------------------------------
        // VALIDASI 2: MAPEL GANDA DALAM 1 HARI (DI KELAS INI)
        // ---------------------------------------------------------
        let isSubjectDuplicateToday = false;
        
        document.querySelectorAll(`.drop-zone[data-day="${dayTarget}"] .slot-content[data-saved="true"]`).forEach(slot => {
            if (slot.dataset.sid === subjectId && slot.closest('.drop-zone').dataset.start !== timeTarget) {
                isSubjectDuplicateToday = true;
            }
        });

        if (isSubjectDuplicateToday) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Mapel Ganda!', 
                text: `Mata pelajaran ${draggedData.sname} sudah ada di hari ${dayTarget}. Harap atur di hari lain.`, 
                confirmButtonColor: '#f59e0b' 
            });
            return; // 🛑 Hentikan Drop
        }

        // ---------------------------------------------------------
        // VALIDASI 3: DATA SAMA PERSIS (DUPLIKASI SLOT)
        // ---------------------------------------------------------
        const currentSlot = e.currentTarget.querySelector('.slot-content');
        if (currentSlot.dataset.saved === "true") {
            if (currentSlot.dataset.sid === subjectId && currentSlot.dataset.tid === teacherId) {
                Swal.fire({
                    icon: 'info',
                    title: 'Jadwal Sudah Ada',
                    text: `Mata pelajaran dan Guru tersebut sudah berada di slot ini.`,
                    confirmButtonColor: '#0071BC',
                    timer: 2000,
                    timerProgressBar: true
                });
                return; // 🛑 Hentikan Drop
            }
        }

        // ==========================================
        // LOLOS VALIDASI: RENDER DATA & UPDATE MEMORI
        // ==========================================
        
        renderItemToSlot(currentSlot, {
            teacher_id: draggedData.tid, teacher_name: draggedData.tname,
            subject_id: draggedData.sid, subject_name: draggedData.sname,
            color: draggedData.color
        });

        // Filter out (buang) jadwal lama yang ada di slot ini dari memori
        globalSchedules = globalSchedules.filter(s => !(s.day_of_week === dayTarget && s.start_time === timeTarget && s.class_id.toString() === currentClassId));
        
        // Push (masukkan) jadwal baru ke dalam memori
        globalSchedules.push({
            class_id: currentClassId,
            day_of_week: dayTarget,
            start_time: timeTarget,
            subject_id: subjectId,
            teacher_id: teacherId
        });
    }

    function renderItemToSlot(slot, data) {
        slot.className = "slot-content w-full h-full rounded-xl p-1.5 sm:p-2 flex flex-col justify-center text-left relative group transition-all cursor-default shadow-sm";
        slot.style.backgroundColor = data.color + '15'; 
        slot.style.border = `1px solid ${data.color}50`;
        
        slot.dataset.saved = "true";
        slot.dataset.tid = data.teacher_id;
        slot.dataset.tname = data.teacher_name;
        slot.dataset.sid = data.subject_id;
        slot.dataset.sname = data.subject_name;
        slot.dataset.color = data.color;

        slot.innerHTML = `
            <div class="absolute top-0 left-0 w-1 sm:w-1.5 h-full rounded-l-xl" style="background:${data.color}"></div>
            <div class="pl-1.5 sm:pl-2 flex flex-col h-full justify-center">
                <p class="text-[9px] sm:text-[11px] font-extrabold text-slate-800 leading-tight mb-0.5 sm:mb-1 uppercase line-clamp-2" style="color: ${data.color}">${data.subject_name}</p>
                <p class="text-[8px] sm:text-[10px] font-bold text-slate-600 truncate w-full flex items-center gap-1">
                    <i class="fas fa-user-circle text-[8px] sm:text-[9px] opacity-70"></i> ${data.teacher_name}
                </p>
            </div>
            
            <button onclick="clearSlot(this, event)" class="absolute -top-1.5 -right-1.5 sm:-top-2 sm:-right-2 bg-red-500 text-white w-5 h-5 sm:w-6 sm:h-6 rounded-full text-[9px] sm:text-[11px] opacity-0 group-hover:opacity-100 transition-all shadow-lg transform scale-75 group-hover:scale-100 flex items-center justify-center hover:bg-red-600 z-10">
                <i class="fas fa-times"></i>
            </button>
        `;
    }

    function clearSlot(btn, e) {
        e.stopPropagation();
        const slot = btn.closest('.slot-content');
        const zone = slot.closest('.drop-zone');
        const dayTarget = zone.dataset.day;
        const timeTarget = zone.dataset.start;
        const currentClassId = classSelector.value.toString();

        // Hapus dari memori UI
        globalSchedules = globalSchedules.filter(s => !(s.day_of_week === dayTarget && s.start_time === timeTarget && s.class_id.toString() === currentClassId));

        slot.innerHTML = '<i class="fas fa-plus opacity-0 group-hover:opacity-100 transition-opacity transform scale-75 group-hover:scale-100"></i>';
        slot.className = 'slot-content group w-full h-full rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-300 hover:bg-blue-50 hover:border-[#0071BC] hover:text-[#0071BC] transition-all duration-200 cursor-pointer';
        slot.style = '';
        delete slot.dataset.saved;
    }

    async function saveScheduleData(status) {
        const classId = classSelector.value;
        const className = classSelector.options[classSelector.selectedIndex]?.dataset.name;
        
        if (!classId) {
            Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Pilih kelas terlebih dahulu sebelum menyimpan!' });
            return;
        }

        const scheduleItems = [];
        document.querySelectorAll('.slot-content[data-saved="true"]').forEach(slot => {
            const zone = slot.closest('.drop-zone');
            scheduleItems.push({
                day: zone.dataset.day,
                start_time: zone.dataset.start,
                teacher_id: slot.dataset.tid,
                teacher_name: slot.dataset.tname,
                subject_id: slot.dataset.sid,
                subject_name: slot.dataset.sname,
                color: slot.dataset.color
            });
        });

        Swal.fire({ title: 'Menyimpan Jadwal...', text: 'Mohon tunggu sebentar', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const response = await fetch(`/lms/{{ $role }}/{{ $schoolName }}/{{ $schoolId }}/teacher-schedule/save`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify({
                    class_id: classId,
                    class_name: className,
                    status: status,
                    schedules: scheduleItems
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const res = await response.json();
            
            Swal.fire({ 
                icon: res.success ? 'success' : 'error', 
                title: res.success ? 'Berhasil!' : 'Oops...', 
                text: res.message,
                confirmButtonColor: '#0071BC'
            });
        } catch (e) {
            console.error(e);
            Swal.fire({ icon: 'error', title: 'Error Server', text: 'Terjadi kesalahan sistem saat menyimpan jadwal.', confirmButtonColor: '#0071BC' });
        }
    }
</script>