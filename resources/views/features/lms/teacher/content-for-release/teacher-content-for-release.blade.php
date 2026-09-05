@include('components/sidebar-beranda', ['headerSideNav' => 'Content For Release'])

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-content-for-release"></div>

            <main>
                <section id="container-form-content-for-release" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                    class="border-b border-gray-300 pb-10">
                    <div class="space-y-6">
                        <form id="content-for-release-form">
                            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6 mb-8">
                                <div class="flex flex-col gap-6">
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-800">
                                            Content For Release Management
                                        </h2>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Atur distribusi materi dan jadwal release untuk rombel serta beberapa pertemuan sekaligus.
                                        </p>
                                    </div>

                                    <div id="form-content-for-release" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

                                        <!-- Tahun Ajaran -->
                                        <div id="container-dropdown-tahun-ajaran">
                                            <div class="flex flex-col w-full">
                                                <label for="dropdown-tahun-ajaran" class="text-sm font-medium text-gray-600 mb-2">
                                                    Pilih Tahun Ajaran
                                                    <sup class="text-red-500">&#42;</sup>
                                                </label>

                                                <select id="dropdown-tahun-ajaran" name="tahun_ajaran" class="w-full h-12 bg-white border border-gray-300 
                                                    rounded-xl px-4 text-sm outline-none cursor-pointer transition focus:border-[#0071BC] focus:ring-2 focus:ring-blue-100">
                                                    <option value="" class="hidden">
                                                        Pilih Tahun Ajaran
                                                    </option>
                                                </select>

                                                <span id="error-tahun-ajaran" class="text-red-500 text-xs font-semibold mt-1.5"></span>
                                            </div>
                                        </div>

                                        <!-- Semester -->
                                        <div id="container-dropdown-semester">
                                            <div class="flex flex-col w-full">
                                                <label for="dropdown-semester" class="text-sm font-medium text-gray-600 mb-2">
                                                    Pilih Semester
                                                    <sup class="text-red-500">&#42;</sup>
                                                </label>

                                                <select id="dropdown-semester" name="semester" class="w-full h-12 bg-white border border-gray-300 rounded-xl px-4 
                                                    text-sm outline-none cursor-pointer transition focus:border-[#0071BC] focus:ring-2 focus:ring-blue-100">
                                                    <option value="" class="hidden">
                                                        Pilih Semester
                                                    </option>

                                                    <option value="1">
                                                        Semester 1
                                                    </option>

                                                    <option value="2">
                                                        Semester 2
                                                    </option>
                                                </select>

                                                <span id="error-semester" class="text-red-500 text-xs font-semibold mt-1.5"></span>
                                            </div>
                                        </div>

                                        <!-- Filter Kelas -->
                                        <div id="container-dropdown-class">
                                            <div class="flex flex-col w-full">
                                                <label for="dropdown-filter-class" class="text-sm font-medium text-gray-600 mb-2">
                                                    Filter Kelas
                                                    <sup class="text-red-500">&#42;</sup>
                                                </label>

                                                <select id="dropdown-filter-class" name="class" class="w-full h-12 bg-white border border-gray-300 rounded-xl px-4 
                                                    text-sm outline-none cursor-pointer transition focus:border-[#0071BC] focus:ring-2 focus:ring-blue-100">
                                                    <option value="" class="hidden">
                                                        Filter Kelas
                                                    </option>
                                                </select>

                                                <span id="error-class" class="text-red-500 text-xs font-semibold mt-1.5"></span>
                                            </div>
                                        </div>
                                        
                                        <div id="container-dropdown-rombel">
                                            <div class="flex flex-col">
                                                <label class="text-sm font-medium text-gray-600 mb-2">
                                                    Rombel Kelas
                                                    <sup class="text-red-500">&#42;</sup>
                                                </label>

                                                <div class="relative">
                                                    <select id="dropdown-school-class" name="school_class_id" class="w-full h-12 bg-white border border-gray-300 rounded-xl 
                                                        px-4 pr-10 text-sm outline-none cursor-pointer appearance-none transition focus:border-[#0071BC] focus:ring-2 
                                                        focus:ring-blue-100">

                                                        <option value="" class="hidden">
                                                            Pilih Rombel Kelas
                                                        </option>
                                                    </select>

                                                    <span class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400">
                                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                                    </span>
                                                </div>

                                                <span id="error-school_class_id" class="text-red-500 text-xs font-semibold mt-1.5"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- target release -->
                            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6 mb-8">
                                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 pb-5 border-b border-gray-100">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 shrink-0 rounded-xl bg-blue-50 text-[#0071BC] flex items-center justify-center">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </div>

                                        <div>
                                            <h3 class="text-base font-semibold text-gray-800">
                                                Target Jadwal Rilis
                                            </h3>

                                            <p class="text-xs text-gray-500 mt-1">
                                                Tentukan target rombel, pilih pertemuan, dan atur jadwal rilis untuk setiap pertemuan.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex items-center gap-2">
                                        <span class="text-xs text-gray-500">
                                            Pertemuan dipilih:
                                        </span>

                                        <span id="total-meeting-selected"
                                            class="px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-100 text-[#0071BC] text-xs font-semibold">
                                            0 Pertemuan
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-5 space-y-5">

                                    <!-- pertemuan -->
                                    <div class="border border-gray-200 rounded-2xl overflow-hidden">
                                        <div class="px-4 md:px-5 py-4 bg-gray-50 border-b border-gray-200">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 shrink-0 rounded-xl bg-white border border-gray-200 text-gray-500 flex items-center justify-center">
                                                        <i class="fa-solid fa-list-check text-sm"></i>
                                                    </div>

                                                    <div>
                                                        <h4 class="text-sm font-semibold text-gray-800">
                                                            Pertemuan & Jadwal Rilis
                                                            <sup class="text-red-500">&#42;</sup>
                                                        </h4>

                                                        <p class="text-xs text-gray-500 mt-0.5">
                                                            Centang pertemuan yang akan menerima materi dan atur jadwal rilis nya.
                                                        </p>
                                                    </div>
                                                </div>

                                                <span class="text-[11px] text-gray-400 shrink-0">
                                                    Maksimal 16 pertemuan
                                                </span>
                                            </div>
                                        </div>

                                        <!-- validation -->
                                        <span id="error-pertemuan" class="text-red-500 text-xs font-semibold px-4 md:px-5 pt-4 block"></span>

                                        <!-- meeting list -->
                                        <div id="meeting-release-list" class="max-h-125 overflow-y-auto">
                                            @for ($i = 1; $i <= 16; $i++)
                                                <div class="meeting-row grid grid-cols-1 md:grid-cols-12 gap-4 items-center px-4 md:px-5 py-4 border-b border-gray-100 
                                                    last:border-b-0 hover:bg-gray-50 transition">

                                                    <!-- meeting checbkox -->
                                                    <div class="md:col-span-5">
                                                        <label class="flex items-center gap-3 cursor-pointer group">
                                                            <input type="checkbox" value="{{ $i }}" name="pertemuan[]" class="meeting-checkbox h-4 w-4 shrink-0 rounded 
                                                                border-gray-300 text-[#0071BC] cursor-pointer">

                                                            <div class="flex items-center gap-3 min-w-0">
                                                                <div class="w-9 h-9 shrink-0 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center transition 
                                                                    group-hover:bg-blue-50 group-hover:text-[#0071BC]">
                                                                    <span class="text-xs font-bold">
                                                                        {{ $i }}
                                                                    </span>
                                                                </div>

                                                                <div class="min-w-0">
                                                                    <p class="text-sm font-semibold text-gray-700">
                                                                        Pertemuan {{ $i }}
                                                                    </p>

                                                                    <p class="text-xs text-gray-400 mt-0.5">
                                                                        Aktifkan untuk menentukan jadwal release.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>

                                                    <div class="hidden md:flex md:col-span-1 justify-center text-gray-300">
                                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                                    </div>

                                                    <!-- release date -->
                                                    <div class="md:col-span-6">
                                                        <div class="relative">
                                                            <input type="text" name="meeting_date[]" class="meeting-release-date w-full bg-gray-100 border border-gray-200 
                                                                rounded-xl px-4 py-3 pr-10 text-sm outline-none transition disabled:cursor-not-allowed disabled:text-gray-400 
                                                                disabled:bg-gray-100" data-meeting="{{ $i }}" placeholder="Pilih tanggal dan waktu release" disabled>

                                                            <span class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400">
                                                                <i class="fa-regular fa-calendar text-sm"></i>
                                                            </span>
                                                        </div>

                                                        <span class="meeting-error-date text-red-500 text-xs font-semibold mt-1.5 block"></span>
                                                    </div>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- content -->
                            <div id="container" data-school-id="{{ $schoolId }}" class="grid grid-cols-1 xl:grid-cols-4 gap-6 mb-8">
                                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0071BC] flex items-center justify-center">
                                            <i class="fa-solid fa-filter text-sm"></i>
                                        </div>

                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-800">
                                                Filter Materi
                                            </h3>

                                            <p class="text-xs text-gray-400 mt-0.5">
                                                Gunakan filter untuk menemukan materi.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 mb-2 block">
                                                Cari Materi
                                            </label>

                                            <label class="flex items-center gap-2 h-12 bg-white border border-gray-300 rounded-xl px-3 transition focus-within:border-[#0071BC] 
                                                focus-within:ring-2 focus-within:ring-blue-100">
                                                <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>

                                                <input id="search_materi" type="search" class="w-full bg-transparent text-sm outline-none" 
                                                    placeholder="Cari nama materi..." autocomplete="off">
                                            </label>
                                        </div>

                                        <!-- kurikulum -->
                                        <div class="flex flex-col">
                                            <label class="text-sm font-medium text-gray-600 mb-2">
                                                Kurikulum
                                            </label>

                                            <select name="kurikulum_id" id="id_kurikulum" class="w-full h-12 bg-white border border-gray-300 rounded-xl px-3 text-sm 
                                                outline-none cursor-pointer transition focus:border-[#0071BC] focus:ring-2 focus:ring-blue-100">
                                                <option value="" class="hidden">
                                                    Pilih Kurikulum
                                                </option>

                                                @foreach ($getCurriculum as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->nama_kurikulum }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- service -->
                                        <div class="flex flex-col">
                                            <label class="text-sm font-medium text-gray-600 mb-2">
                                                Service
                                            </label>

                                            <select name="service_id" id="id_service" class="w-full h-12 bg-white border border-gray-300 rounded-xl px-3 text-sm 
                                                outline-none transition opacity-50 cursor-not-allowed" disabled>
                                                <option value="" class="hidden">
                                                    Pilih Service
                                                </option>
                                            </select>
                                        </div>

                                        <!-- kelas -->
                                        <div class="flex flex-col">
                                            <label class="text-sm font-medium text-gray-600 mb-2">
                                                Kelas
                                            </label>

                                            <select name="kelas_id" id="id_kelas" class="w-full h-12 bg-white border border-gray-300 rounded-xl px-3 text-sm outline-none 
                                                transition opacity-50 cursor-not-allowed" disabled>
                                                <option value="" class="hidden">
                                                    Pilih Kelas
                                                </option>
                                            </select>
                                        </div>

                                        <!-- mapel -->
                                        <div class="flex flex-col">
                                            <label class="text-sm font-medium text-gray-600 mb-2">
                                                Mata Pelajaran
                                            </label>

                                            <select name="mapel_id" id="id_mapel" class="w-full h-12 bg-white border border-gray-300 rounded-xl px-3 text-sm outline-none 
                                                transition opacity-50 cursor-not-allowed" disabled>
                                                <option value="" class="hidden">
                                                    Pilih Mata Pelajaran
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Bab --}}
                                        <div class="flex flex-col">
                                            <label class="text-sm font-medium text-gray-600 mb-2">
                                                Bab
                                            </label>

                                            <select name="bab_id" id="id_bab" class="w-full h-12 bg-white border border-gray-300 rounded-xl px-3 text-sm outline-none 
                                                transition opacity-50 cursor-not-allowed" disabled>
                                                <option value="" class="hidden">
                                                    Pilih Bab
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- content list -->
                                <div class="xl:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-6">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 pb-5 border-b border-gray-100">
                                        <div class="flex items-start gap-3">
                                            <div class="w-9 h-9 shrink-0 rounded-xl bg-blue-50 text-[#0071BC] flex items-center justify-center">
                                                <i class="fa-solid fa-book-open text-sm"></i>
                                            </div>

                                            <div>
                                                <h3 class="text-sm font-semibold text-gray-800">
                                                    Pilih Materi
                                                </h3>

                                                <p class="text-xs text-gray-400 mt-1">
                                                    Materi yang dipilih akan digunakan pada seluruh pertemuan yang telah dipilih.
                                                </p>
                                            </div>
                                        </div>

                                        <span id="total-selected"
                                            class="shrink-0 px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-100 text-[#0071BC] text-xs font-semibold">
                                            0 Dipilih
                                        </span>
                                    </div>

                                    <span id="error-lms_content_id" class="text-red-500 text-xs font-semibold mt-4 block"></span>

                                    <!-- content list -->
                                    <div id="content-list-container" class="hidden space-y-3 max-h-125 overflow-y-auto pr-1 mt-4">
                                        <!-- show data in ajax -->
                                    </div>

                                    <!-- empty state -->
                                    <div id="empty-message-content-list" class="hidden">
                                        <div class="min-h-100 flex items-center justify-center">
                                            <div class="text-center">
                                                <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center">
                                                    <i class="fa-solid fa-book-open text-lg"></i>
                                                </div>
    
                                                <p class="text-sm font-medium text-gray-600 mt-4">
                                                    Materi tidak ditemukan
                                                </p>
    
                                                <p class="text-xs text-gray-400 mt-1">
                                                    Tidak terdapat materi untuk dipilih.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- submit summary -->
                            <div class="bg-[#0071BC] rounded-2xl p-5 md:p-6 text-white">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                                    <div>
                                        <p class="text-base font-semibold">
                                            Siap Dipublish
                                        </p>

                                        <p class="text-sm text-blue-100 mt-1">
                                            Pastikan target rombel, pertemuan, jadwal release, dan materi sudah sesuai.
                                        </p>

                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-3 text-xs text-blue-100">
                                            <span id="text-semester">
                                                Belum memilih semester
                                            </span>

                                            <i class="fa-solid fa-circle text-[3px] opacity-50"></i>

                                            <span id="text-rombel">
                                                Belum memilih rombel
                                            </span>

                                            <i class="fa-solid fa-circle text-[3px] opacity-50"></i>

                                            <span id="text-meeting">
                                                0 Pertemuan
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Action --}}
                                    <div class="flex flex-col sm:flex-row gap-3 shrink-0">
                                        <button id="submit-button-draft-content-for-release" type="button" data-status="draft" class="h-11 px-5 rounded-xl 
                                            bg-white/15 border border-white/20 text-sm font-medium text-white hover:bg-white/25 transition cursor-pointer">
                                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                                            Simpan Draft
                                        </button>

                                        <button id="submit-button-publish-content-for-release" type="button" data-status="publish" class="h-11 px-5 rounded-xl bg-white 
                                            text-[#0071BC] text-sm font-semibold hover:bg-gray-100 transition shadow-sm cursor-pointer">
                                            <i class="fa-solid fa-paper-plane mr-2"></i>
                                            Publish Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>

                <section id="container-content-for-release-list" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="mt-10">
                    <h2 class="text-xl font-semibold text-gray-800">
                        Content For Release List
                    </h2>
                    <div class="overflow-x-auto mt-2 pb-20">
                        <table id="table-content-for-release-list" class="min-w-175 lg:min-w-full text-sm border-collapse">
                            <thead class="thead-table-content-for-release-list bg-gray-50 hidden shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Rombel Kelas</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tahun Ajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Semester</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Total Pertemuan</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Service</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody-content-for-release-list">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-content-for-release-list flex justify-center my-10"></div>

                    <div id="empty-message-content-for-release-list" class="w-full h-96 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada content for release yang terdaftar.
                        </span>
                    </div>
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

<script src="{{ asset('assets/js/features/lms/teacher/content-for-release/paginate-teacher-content-for-release.js') }}"></script> <!--- paginate content for release ---->
<script src="{{ asset('assets/js/features/lms/teacher/content-for-release/teacher-form-content-for-release.js') }}"></script> <!--- form content for release ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown curriculum core ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-service-dropdown-only.js') }}"></script> <!--- dependent dropdown service only (without dynamic form) ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->