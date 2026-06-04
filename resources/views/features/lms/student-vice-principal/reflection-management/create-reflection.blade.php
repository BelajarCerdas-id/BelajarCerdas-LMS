@include('components/sidebar-beranda', [
    'headerSideNav' => 'Create Reflection',
    'linkBackButton' => route('lms.student-vice-principal.reflection-management.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role == 'Wakil Kesiswaan')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-[#F8FAFC] transition-all duration-500 ease-in-out overflow-x-hidden">
        <div class="p-4 sm:p-6 md:p-8 xl:p-10 mx-auto space-y-8">

            <!---- ALERT SUCCESS ---->
            <div id="alert-success-create-reflection"></div>

            <!-- HERO SECTION -->
            <div class="relative overflow-hidden rounded-4xl md:rounded-[2.5rem] bg-[#0071BC] p-6 sm:p-8 md:p-10 text-white 
                shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]"
                style="background-image: url('{{ asset('assets/images/components/background-bc.svg') }}'); background-size: cover; background-position: center;">

                <div class="absolute -top-10 -right-10 w-52 h-52 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-20 w-40 h-40 rounded-full bg-cyan-300/10 blur-3xl"></div>

                <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-8">

                    <div class="max-w-3xl">

                        <div class="flex flex-wrap items-center gap-3 mb-4">

                            <div class="px-4 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 
                                text-[10px] sm:text-xs font-bold uppercase tracking-widest">
                                Pembuatan Refleksi Harian
                            </div>

                            <div class="flex items-center gap-2 text-xs sm:text-sm text-blue-100">

                                <div class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></div>

                                Siap Dipublikasikan

                            </div>

                        </div>

                        <h1 class="text-3xl md:text-4xl font-black leading-tight tracking-tight">
                            Buat Refleksi Baru
                        </h1>

                        <p class="mt-4 text-blue-100 max-w-2xl leading-relaxed text-sm md:text-base font-medium">
                            Susun pertanyaan refleksi harian yang akan dikirim kepada siswa untuk membantu sekolah
                            memahami kondisi emosional, pengalaman belajar, serta kebutuhan siswa secara lebih dekat
                            dan berkelanjutan.
                        </p>

                    </div>

                </div>
            </div>

            <!-- MAIN GRID -->
            <div id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">

                <!-- FORM SECTION -->
                <form id="create-reflection-form" class="2xl:col-span-8 space-y-8">
                    <div class="bg-white rounded-4xl md:rounded-[2.5rem] p-6 md:p-8 border border-gray-300 shadow-sm">

                        <!-- HEADER -->
                        <div class="mb-8">

                            <div class="flex items-start justify-between gap-5 flex-wrap">

                                <div>
                                    <h2 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">
                                        Form Refleksi Harian
                                    </h2>

                                    <p class="text-slate-500 mt-2 text-sm">
                                        Buat pertanyaan refleksi yang ringan, nyaman, dan mudah dipahami siswa.
                                    </p>
                                </div>

                                <!-- TAHUN AJARAN -->
                                <div id="container-dropdown-tahun-ajaran" class="w-full sm:w-72">

                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-2">
                                        Tahun Ajaran
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>

                                    <div class="relative">

                                        <select id="dropdown-tahun-ajaran" name="tahun_ajaran" class="appearance-none w-full rounded-2xl border border-gray-300 bg-white px-5 
                                            py-4 pr-12 text-sm font-bold text-slate-700 outline-none focus:border-[#2563EB] focus:ring-4 focus:ring-blue-100 transition-all
                                            cursor-pointer">
                                            <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                            <!-- show data in ajax -->
                                        </select>
                                        <span id="error-tahun_ajaran" class="text-red-500 text-xs mt-1 font-bold"></span>

                                        <div class="absolute top-1/2 right-5 -translate-y-1/2 text-slate-400">
                                            <i class="fas fa-chevron-down text-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TITLE -->
                        <div class="mb-8">

                            <label class="block text-sm font-black text-slate-700 mb-3">
                                Judul Refleksi
                                <sup class="text-red-500">&#42;</sup>
                            </label>

                            <input type="text" placeholder="Contoh: Persiapan Ujian Semester" name="title"
                                class="w-full rounded-3xl border border-gray-300 px-5 py-4 outline-none focus:border-[#2563EB] focus:ring-4 focus:ring-blue-100 
                                transition-all text-sm font-medium">
                            <span id="error-title" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>

                        <!-- QUESTION -->
                        <div class="mb-8">

                            <label class="block text-sm font-black text-slate-700 mb-3">
                                Pertanyaan Refleksi
                                <sup class="text-red-500">&#42;</sup>
                            </label>

                            <textarea rows="6" placeholder="Apa hal yang paling kamu rasakan hari ini?" name="question"
                                class="w-full rounded-3xl border border-gray-300 px-5 py-4 outline-none focus:border-[#2563EB] focus:ring-4 focus:ring-blue-100 
                                transition-all text-sm font-medium resize-none"></textarea>
                            <span id="error-question" class="text-red-500 text-xs mt-1 font-bold"></span>

                            <p class="text-xs text-slate-400 mt-3 font-medium leading-relaxed">
                                Gunakan pertanyaan yang ringan, jelas, dan mudah dipahami siswa agar siswa nyaman mengisi refleksi.
                            </p>
                        </div>

                        <!-- TARGET SECTION -->
                        <div class="mb-8">

                            <!-- HEADER -->
                            <div class="mb-5">

                                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

                                    <!-- LEFT -->
                                    <div class="flex-1 min-w-0">

                                        <label class="block text-sm font-black text-slate-700">
                                            Target Jenjang Kelas
                                        </label>

                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                            Pilih jenjang kelas yang akan menerima refleksi harian ini.
                                        </p>
                                    </div>

                                    <!-- RIGHT -->
                                    <div class="w-full lg:w-auto flex justify-end mt-4 lg:mt-0">

                                        <div class="flex items-center gap-2 flex-wrap justify-end">

                                            <!-- COUNT -->
                                            <div id="total-jenjang-kelas-selected"
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl 
                                                bg-blue-50 text-[#2563EB] text-xs font-black">

                                                <i class="fas fa-users"></i>

                                                <span>0 Jenjang Dipilih</span>
                                            </div>

                                            <!-- SELECT & UNSELECT -->
                                            <button type="button" id="toggle-select-rombel" class="px-4 py-2 rounded-2xl border border-blue-200 bg-white text-[#2563EB]
                                                text-xs font-black hover:bg-blue-50 transition-all cursor-pointer">
                                                Select All
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <span id="error-target_class_id"
                                class="text-red-500 text-xs font-semibold hidden">
                            </span>

                            <!-- CLASS LIST -->
                            <div id="target-jenjang-kelas" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 mt-2">
                                <!-- show data in ajax -->
                            </div>
                        </div>

                        <!-- TARGET PREVIEW -->
                        <div id="preview-target-reflection-container" class="hidden">
                            <!-- show data in ajax -->
                        </div>

                        <!-- ACTION -->
                        <div class="pt-2">

                            <button id="submit-btn-create-reflection" class="w-full px-6 py-4 rounded-2xl bg-[#2563EB] text-white font-black shadow-xl shadow-blue-100 
                                hover:scale-[1.01] active:scale-[0.99] transition-all cursor-pointer disabled:cursor-default">

                                <div class="flex items-center justify-center gap-3">

                                    <div
                                        class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                                        <i class="fas fa-paper-plane text-sm"></i>
                                    </div>

                                    <div class="text-left">
                                        <p class="text-base font-black leading-none">
                                            Publikasikan Refleksi
                                        </p>

                                        <p class="text-xs text-blue-100 mt-1 font-semibold">
                                            Refleksi akan langsung dikirim ke siswa terpilih
                                        </p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/form-submit-reflection-management.js') }}"></script> <!--- form submit reflection management ---->
<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/paginate-reflection-management-history-recent.js') }}"></script> <!--- paginate reflection management history recent ---->
<script src="{{ asset('assets/js/features/lms/student-vice-principal/reflection-management/daily-reflection-live-preview.js') }}"></script> <!--- paginate reflection management history recent ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/student-vice-principal/daily-reflection/daily-reflection-live-preview-listener.js') }}"></script> <!--- pusher listener daily reflection live preview ---->