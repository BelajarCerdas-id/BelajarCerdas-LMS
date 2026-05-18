@include('components/sidebar-beranda', [
    'linkBackButton' => route('lms.teacherSubjectAttendanceMeetingList.view', [$role, $schoolName, $schoolId, $subjectTeacherId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Subject Attendace',
]);


@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

        <!---- alert success from ajax ---->
        <div id="alert-success-insert-data-announcement"></div>

        <section>
            <div class="bg-white px-6 py-6 md:px-8 border-b border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-125 h-125 bg-linear-to-bl from-slate-50 to-transparent rounded-full -translate-y-1/2 
                    translate-x-1/4 opacity-80 pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl shadow-sm border border-indigo-100 shrink-0">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="flex flex-col justify-center">
                                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                                    Kelas {{ $subjectTeacher->SchoolClass->class_name ?? 'Siswa' }}
                                </h1>
                                <div class="flex flex-wrap items-center gap-2 mt-1.5">
    
                                    <!-- MAPEL -->
                                    <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg text-xs font-bold border border-indigo-100">
                                        <i class="fas fa-book-open"></i>
                                        {{ $subjectTeacher->Mapel->mata_pelajaran ?? '-' }}
                                    </span>
    
                                    <!-- TAHUN AJARAN -->
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1 rounded-lg text-xs font-bold border border-emerald-100">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ $subjectTeacher->SchoolClass->tahun_ajaran ?? '-' }}
                                    </span>
    
                                    <!-- WALI KELAS -->
                                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 px-3 py-1 rounded-lg text-xs font-bold border border-amber-100">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        {{ $subjectTeacher->UserAccount->SchoolStaffProfile->nama_lengkap ?? '-' }}
                                    </span>

                                    <!-- PERTEMUAN -->
                                    <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 px-3 py-1 rounded-lg text-xs font-bold border border-rose-100">
                                        <i class="fas fa-layer-group"></i>
                                        Semester {{ $semester ?? 'tidak diketahui' }} - Pertemuan {{ $meetingNumber ?? 'tidak diketahui' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="my-10 sm:my-12 lg:my-15 mx-3 sm:mx-5 lg:mx-7.5">
            <main>
                <section id="container" data-role="{{ $role }}" data-school-id="{{ $schoolId }}" data-school-name="{{ $schoolName }}" 
                    data-subject-teacher-id="{{ $subjectTeacherId }}" data-meeting-number="{{ $meetingNumber }}" data-semester="{{ $semester }}">

                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 md:gap-8">
                        <div class="xl:col-span-2 flex flex-col w-full">
                            <div class="flex pt-2 pl-4 gap-1">
                                <button onclick="switchTab('pengumuman')" id="btn-tab-pengumuman" class="tab-btn px-6 py-3.5 font-bold text-sm rounded-t-2xl border-t 
                                    border-x border-slate-200 bg-white text-indigo-600 relative z-10 -mb-px flex items-center gap-2 transition-all cursor-pointer">
                                    <i class="fas fa-bullhorn"></i> Pengumuman
                                </button>
                                <button onclick="switchTab('materi')" id="btn-tab-materi" class="tab-btn px-6 py-3.5 font-semibold text-sm rounded-t-2xl border-t 
                                    border-x border-slate-200/60 bg-slate-100 text-slate-500 relative z-0 -mb-px hover:bg-white hover:text-indigo-500 
                                    flex items-center gap-2 transition-all shadow-inner cursor-pointer">
                                    <i class="fas fa-file-alt"></i> Materi
                                </button>
                                <button onclick="switchTab('tugas')" id="btn-tab-tugas" class="tab-btn px-6 py-3.5 font-semibold text-sm rounded-t-2xl border-t 
                                    border-x border-slate-200/60 bg-slate-100 text-slate-500 relative z-0 -mb-px hover:bg-white hover:text-indigo-500 
                                    flex items-center gap-2 transition-all shadow-inner cursor-pointer">
                                    <i class="fas fa-tasks"></i> Asesmen
                                </button>
                            </div>

                            <div class="bg-white border border-slate-200 rounded-b-3xl rounded-tr-3xl shadow-sm p-6 md:p-8 h-150 flex flex-col relative z-0">
                                
                                <div id="tab-pengumuman" class="tab-pane flex flex-col h-full flex-1">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-300 shrink-0 gap-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold text-lg">
                                                <i class="fas fa-bullhorn"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-800 text-lg leading-tight">Papan Pengumuman</h3>
                                                <p class="text-xs text-slate-500 font-medium">Riwayat informasi kelas</p>
                                            </div>
                                        </div>
                                        <button id="btn-create-announcement" class="bg-blue-50 border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white 
                                            px-4 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                                            <i class="fas fa-plus"></i> Tambah Pengumuman
                                        </button>
                                    </div>

                                    <div class="flex flex-col gap-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
                                        <div id="grid-announcement-list" class="grid grid-cols-1 gap-4"></div>

                                        <div id="empty-message-announcement-list" class="bg-white shadow-lg border border-gray-300 rounded-2xl w-full h-96 hidden">
                                            <div class="flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl 
                                                bg-slate-50 h-full">
                                                <i class="fas fa-bullhorn text-4xl mb-4 text-slate-300"></i>
                                                <p class="text-sm font-bold text-slate-600">Belum Ada Pengumuman</p>
                                                <p class="text-xs text-slate-400 mt-1">Klik tombol tambah untuk membuat pengumuman.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB MATERI -->
                                <div id="tab-materi" class="tab-pane hidden h-full flex-1">
                                    <div class="flex flex-col h-full">
                                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-300 shrink-0 gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-lg shrink-0">
                                                    <i class="fas fa-file-alt"></i>
                                                </div>

                                                <div class="min-w-0">
                                                    <h3 class="font-bold text-slate-800 text-base sm:text-lg leading-tight wrap-break-word">
                                                        Materi Pembelajaran
                                                    </h3>
                                                    <p class="text-xs text-slate-500 font-medium">
                                                        Bahan ajar yang dibagikan
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- BUTTON -->
                                            <a href="{{ route('lms.teacherContentForRelease.view', [$role, $schoolName, $schoolId]) }}"
                                                class="w-full sm:w-auto">

                                                <button
                                                    class="w-full sm:w-auto bg-indigo-50 border border-indigo-100 text-indigo-600
                                                    hover:bg-indigo-600 hover:text-white px-4 py-2.5 rounded-xl font-bold
                                                    text-sm transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer">

                                                    <i class="fas fa-upload"></i>
                                                    Upload Materi
                                                </button>
                                            </a>
                                        </div>

                                        <!-- CONTENT -->
                                        <div class="flex flex-col gap-4 overflow-y-auto pr-1 sm:pr-2 custom-scrollbar flex-1" style="overscroll-behavior: contain;">
                                            <div id="grid-material-list" class="grid grid-cols-1 gap-4"></div>
                                            <div id="empty-message-material-list"
                                                class="bg-white shadow-lg border border-gray-300 rounded-2xl w-full min-h-75 sm:h-96 hidden">

                                                <div
                                                    class="flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 h-full px-4">

                                                    <i class="fas fa-file-alt text-3xl sm:text-4xl mb-4 text-slate-300"></i>

                                                    <p class="text-sm font-bold text-slate-600">
                                                        Belum Ada Materi
                                                    </p>

                                                    <p class="text-xs text-slate-400 mt-1 max-w-xs">
                                                        Silakan upload bahan ajar untuk siswa.
                                                    </p>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB TUGAS -->
                                <div id="tab-tugas" class="tab-pane hidden h-full flex-1">
                                    <div class="flex flex-col h-full">
                                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-gray-300 shrink-0">
                                            <div class="flex items-start sm:items-center gap-3">

                                                <div class="w-10 h-10 sm:w-11 sm:h-11 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center shadow-sm shrink-0">
                                                    <i class="fas fa-clipboard-check text-base sm:text-lg"></i>
                                                </div>

                                                <div class="min-w-0">

                                                    <h3 class="font-bold text-slate-800 text-base sm:text-lg leading-tight wrap-break-word">
                                                        Asesmen
                                                    </h3>

                                                    <p class="text-xs text-slate-500 font-medium">
                                                        Kelola dan nilai asesmen siswa pada pertemuan ini
                                                    </p>

                                                </div>
                                            </div>

                                            <!-- RIGHT -->
                                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">

                                                <a href="{{ route('lms.teacherAssessmentManagement.view', [$role, $schoolName, $schoolId]) }}"
                                                    class="w-full sm:w-auto">

                                                    <button class="w-full sm:w-auto bg-amber-50 border border-amber-100 text-amber-600 
                                                        hover:bg-amber-500 hover:text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-all duration-200 shadow-sm
                                                        hover:shadow-md flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer">
                                                        <i class="fas fa-plus"></i>
                                                        Jadwalkan Asesmen
                                                    </button>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- FILTER -->
                                        <div class="my-3 bg-gray-50 shadow-sm border border-gray-300 rounded-2xl p-4 sm:p-6 shrink-0">

                                            <!-- HEADER FILTER -->
                                            <div class="flex items-center justify-between mb-4">

                                                <div class="flex items-center gap-2">

                                                    <i class="fa-solid fa-filter text-[#0071BC]"></i>

                                                    <h3 class="text-sm sm:text-base font-semibold text-gray-800">
                                                        Filter Asesmen
                                                    </h3>

                                                </div>
                                            </div>

                                            <!-- FILTER GRID -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">

                                                <select id="assessmentTypeFilter"
                                                    class="w-full bg-white shadow-sm rounded-xl h-12 border border-gray-300
                                                    text-sm px-4 cursor-pointer outline-none">

                                                    <option value="">
                                                        Semua Jenis Asesmen
                                                    </option>

                                                </select>
                                            </div>
                                        </div>

                                        <!-- LIST -->
                                        <div id="grid-assessment-list"
                                            class="flex flex-col gap-4 overflow-y-auto pr-1 sm:pr-2 custom-scrollbar flex-1"
                                            style="overscroll-behavior: contain;">
                                        </div>

                                        <!-- EMPTY -->
                                        <div id="empty-message-assessment-list"
                                            class="bg-white shadow-lg border border-gray-300 rounded-2xl w-full min-75 sm:h-96 hidden">

                                            <div
                                                class="flex flex-col items-center justify-center py-10 text-center border-2 border-dashed border-slate-200 rounded-2xl
                                                bg-slate-50 h-full px-4">

                                                <i class="fas fa-clipboard-check text-3xl sm:text-4xl mb-4 text-slate-300"></i>

                                                <p class="text-sm font-bold text-slate-600">
                                                    Belum Ada Asesmen
                                                </p>

                                                <p class="text-xs text-slate-400 mt-1 max-w-xs">
                                                    Klik tombol jadwalkan asesmen untuk membuat asesmen.
                                                </p>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-1 flex flex-col h-full">
                            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col h-full relative overflow-hidden">
                                
                                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 mb-6 relative">
                                    <h4 class="font-bold text-slate-800 text-center mb-4 flex items-center justify-center gap-2">
                                        <i class="fas fa-chart-pie text-slate-400"></i> Statistik Kehadiran
                                    </h4>
                                    <div class="relative w-40 h-40 mx-auto transition-transform duration-500 hover:scale-105">
                                        <canvas id="absensiChart"></canvas>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mb-6">

                                    <div class="col-span-2 bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col items-center justify-center text-center">
                                        <h4 id="totalSiswaCount" class="text-2xl font-black text-blue-500 leading-none">
                                            0
                                        </h4>

                                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">
                                            Total Siswa
                                        </p>
                                    </div>

                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col items-center justify-center text-center">
                                        <h4 id="hadirCount" class="text-2xl font-black text-emerald-500 leading-none">
                                            0
                                        </h4>

                                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">
                                            Hadir
                                        </p>
                                    </div>

                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col items-center justify-center text-center">
                                        <h4 id="izinCount" class="text-2xl font-black text-blue-500 leading-none">
                                            0
                                        </h4>

                                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">
                                            Izin
                                        </p>
                                    </div>

                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col items-center justify-center text-center">
                                        <h4 id="sakitCount" class="text-2xl font-black text-amber-500 leading-none">
                                            0
                                        </h4>

                                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">
                                            Sakit
                                        </p>
                                    </div>

                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col items-center justify-center text-center">
                                        <h4 id="alpaCount" class="text-2xl font-black text-red-500 leading-none">
                                            0
                                        </h4>

                                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-2">
                                            Alpa
                                        </p>
                                    </div>

                                </div>

                                <div class="mt-auto">
                                    <button onclick="openAttendanceModal()" class="w-full bg-emerald-50 text-emerald-600 font-bold py-4 rounded-2xl border border-emerald-100 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center gap-2 text-base shadow-sm cursor-pointer">
                                        <i class="fas fa-user-check text-lg"></i> Input Presensi Kelas
                                    </button>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </section>

                <!-- MODAL -->
                <section>

                    <!-- ATTENDANCE MODAL -->
                    <dialog id="attendanceModal" class="modal">
                        <div class="modal-box max-w-4xl p-0 rounded-3xl overflow-hidden bg-white">

                            <!-- HEADER -->
                            <div class="bg-emerald-600 px-6 py-5 flex items-center justify-between text-white">
                                <h3 class="font-bold text-xl flex items-center gap-2">
                                    <i class="fas fa-user-check"></i>
                                    Input Kehadiran Siswa
                                </h3>

                                <form method="dialog">
                                    <button class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20 border-none">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- STUDENT LIST -->
                            <div id="studentListContainer" class="p-6 max-h-[65vh] overflow-y-auto bg-slate-50"></div>

                            <!-- FOOTER -->
                            <div class="px-6 py-5 border-t border-slate-200 bg-white flex justify-end gap-3">
                                <form method="dialog">
                                    <button class="px-6 py-2.5 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-all cursor-pointer">
                                        Batal
                                    </button>
                                </form>

                                <button onclick="submitAttendance()"
                                    class="px-8 py-2.5 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition-all 
                                    shadow-lg shadow-emerald-200 cursor-pointer">
                                    Simpan Presensi
                                </button>
                            </div>

                        </div>

                        <!-- CLICK OUTSIDE -->
                        <form method="dialog" class="modal-backdrop">
                            <button>close</button>
                        </form>
                    </dialog>

                    <!--- ANNOUNCEMENT MODAL --->
                    <dialog id="announcement-modal" class="modal">

                        <div class="modal-box bg-white rounded-3xl p-0 overflow-hidden max-w-2xl">

                            <!-- HEADER -->
                            <div class="bg-blue-600 px-6 py-5 flex items-center justify-between text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-white/15 flex items-center justify-center">
                                        <i class="fas fa-bullhorn text-lg"></i>
                                    </div>

                                    <div>
                                        <h3 class="font-bold text-lg leading-tight">
                                            Buat Pengumuman Baru
                                        </h3>
                                        <p class="text-xs text-blue-100 mt-0.5">
                                            Informasi akan dikirim ke seluruh siswa kelas
                                        </p>
                                    </div>
                                </div>

                                <form method="dialog">
                                    <button class="w-9 h-9 rounded-xl hover:bg-white/10 flex items-center justify-center hover:rotate-90 transition-transform cursor-pointer">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- BODY -->
                            <form id="create-announcemenet-form" class="p-6 space-y-5">

                                <input type="hidden" name="class_id" value="{{ $subjectTeacher->SchoolClass->id ?? '' }}">
                                <input type="hidden" name="school_id" value="{{ $schoolId ?? '' }}">

                                <!-- TITLE -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">
                                        Judul Pengumuman
                                    </label>

                                    <input type="text" name="title" placeholder="Contoh: Informasi Tugas Kelompok" 
                                        class="w-full h-12 px-4 rounded-2xl border border-gray-200 bg-white outline-none text-sm font-medium text-slate-700">
                                    <span id="error-title" class="text-red-500 text-xs font-semibold hidden"></span>
                                </div>

                                <!-- TYPE -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">
                                        Jenis Pengumuman
                                    </label>

                                    <select name="type" class="w-full h-12 px-4 rounded-2xl border border-gray-200 bg-white outline-none text-sm font-medium 
                                        text-slate-700 cursor-pointer">

                                        <option value="" class="hidden">Pilih jenis pengumuman</option>
                                        <option value="info">Info Biasa</option>
                                        <option value="penting">Penting / Urgent</option>
                                    </select>
                                    <span id="error-type" class="text-red-500 text-xs font-semibold hidden"></span>
                                </div>

                                <!-- CONTENT -->
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">
                                        Isi Pengumuman
                                    </label>

                                    <textarea name="content" rows="5" placeholder="Tuliskan isi pengumuman di sini..." 
                                        class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white outline-none text-sm text-slate-700 
                                        custom-scrollbar resize-none"></textarea>
                                    <span id="error-content" class="text-red-500 text-xs font-semibold hidden"></span>
                                </div>

                                <!-- ACTION -->
                                <div class="pt-2">
                                    <button id="submit-btn-create-announcement" type="button" class="btn-submit-pengumuman w-full h-12 rounded-2xl bg-blue-600 hover:bg-blue-700 
                                        text-white font-bold shadow-lg shadow-blue-100 transition-all duration-200 cursor-pointer">

                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Kirim Pengumuman
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- BACKDROP -->
                        <form method="dialog" class="modal-backdrop">
                            <button>close</button>
                        </form>
                    </dialog>

                    <!-- MODAL DETAIL & PREVIEW MATERI -->
                    <dialog id="materiDetailModal" class="modal">
                        <div class="modal-box w-11/12 max-w-7xl h-[95vh] p-0 rounded-3xl overflow-hidden flex flex-col">

                            <!-- HEADER -->
                            <div class="bg-indigo-600 p-4 md:p-5 flex justify-between items-center text-white shrink-0">
                                <div>
                                    <h3 class="font-bold text-lg flex items-center gap-2">
                                        <i class="fas fa-file-alt"></i>
                                        Preview Dokumen Materi
                                    </h3>
                                    <p class="text-indigo-200 text-xs mt-1" id="detailMateriTanggal"></p>
                                </div>

                                <form method="dialog">
                                    <button class="hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- CONTENT -->
                            <div class="p-6 bg-slate-50 flex-1 overflow-y-auto custom-scrollbar flex flex-col">

                                <!-- INFO -->
                                <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 flex items-center gap-4 mb-4 shrink-0 shadow-sm">

                                    <div class="w-12 h-12 bg-white text-indigo-500 rounded-xl flex items-center justify-center text-2xl shadow-sm shrink-0">
                                        <i class="fas fa-book-open"></i>
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-1">
                                            Judul Materi Pembelajaran
                                        </p>

                                        <h4 class="text-base md:text-md font-bold text-slate-800 leading-tight wrap-break-word whitespace-normal" id="detailMateriJudul">
                                            Judul
                                        </h4>

                                    </div>
                                </div>

                                <!-- PREVIEW -->
                                <div id="materiPreviewContainer"
                                    class="w-full flex-1 bg-slate-100 rounded-xl border border-slate-200 shadow-inner overflow-hidden relative flex items-center justify-center">
                                </div>
                            </div>

                            <!-- FOOTER -->
                            <div class="modal-action m-0 p-4 md:p-5 bg-white border-t border-slate-100 shrink-0">
                                <form method="dialog">
                                    <button class="px-8 py-2.5 font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">
                                        Tutup Jendela
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- BACKDROP -->
                        <form method="dialog" class="modal-backdrop">
                            <button>close</button>
                        </form>
                    </dialog>
                </section>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<!--- MAIN ---->
<script src="{{ asset('assets/js/features/lms/teacher/subject-attendance/main/subject-attendance-meeting-management.js') }}"></script> <!--- subject attendance meeting management ---->
<script src="{{ asset('assets/js/features/lms/teacher/subject-attendance/main/subject-attendance-chart.js') }}"></script> <!--- subject attendance chart ---->
<script src="{{ asset('assets/js/features/lms/teacher/subject-attendance/main/subject-attendance-student.js') }}"></script> <!--- subject attendance student ---->

<!--- FORM ACTION ---->
<script src="{{ asset('assets/js/features/lms/teacher/subject-attendance/form-action/submit-meeting-announcement.js') }}"></script> <!--- logic submit meeting announcrment---->

<!--- PAGINATE ---->
<script src="{{ asset('assets/js/features/lms/teacher/subject-attendance/paginate/paginate-meeting-announcement.js') }}"></script> <!--- paginate meeting announcement ---->
<script src="{{ asset('assets/js/features/lms/teacher/subject-attendance/paginate/paginate-meeting-assessment.js') }}"></script> <!--- paginate meeting exam ---->
<script src="{{ asset('assets/js/features/lms/teacher/subject-attendance/paginate/paginate-meeting-material.js') }}"></script> <!--- paginate meeting material ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->