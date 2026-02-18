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
                            <!-- ================= HEADER ================= -->
                            <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-100 mb-10">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-800">
                                            Content For Release Management
                                        </h2>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Atur distribusi materi untuk setiap rombel dalam satu pertemuan.
                                        </p>
                                    </div>
                                </div>
    
                                <div id="form-content-for-release" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    
                                    <!-- tahun ajaran -->
                                    <div id="container-dropdown-tahun-ajaran">
                                        <!-- show data in ajax -->
                                    </div>
    
                                    <!-- Semester -->
                                    <div id="container-dropdown-semester">
                                        <div class="flex flex-col w-full mb-2">
                                            <label class="text-sm font-medium text-gray-600 mb-1">
                                                Pilih Semester
                                            </label>
                                            <select id="dropdown-semester" name="semester" class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm pr-6 cursor-pointer">
                                                <option value="" class="hidden">Pilih Semester</option>
                                                <option value="1">Semester 1</option>
                                                <option value="2">Semester 2</option>
                                            </select>
                                            <span id="error-semester" class="text-red-500 text-xs mt-1 font-bold"></span>
                                        </div>
                                    </div>
    
                                    <!-- Pertemuan -->
                                    <div id="container-dropdown-pertemuan">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">
                                                Pertemuan
                                            </label>

                                            <select name="pertemuan"
                                                class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm pr-6 cursor-pointer">

                                                <option value="" class="hidden">Pilih Pertemuan</option>

                                                @for ($i = 1; $i <= 16; $i++)
                                                    <option value="{{ $i }}">
                                                        Pertemuan {{ $i }}
                                                    </option>
                                                @endfor

                                            </select>

                                            <span id="error-pertemuan"
                                                class="text-red-500 text-xs mt-1 font-bold"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
    
                            <!-- ================= TARGET ROMBEL ================= -->
                            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 mb-10">
    
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-800 pb-2">
                                            Target Rombel & Jadwal Release
                                        </h3>
                                        <p class="text-xs text-gray-500">
                                            Centang rombel yang ingin diberikan materi dan tentukan tanggalnya.
                                        </p>

                                    </div>
                                    
                                    <div id="container-dropdown-class">
                                        <!-- show data in ajax -->
                                    </div>
                                </div>
                                <span id="error-school_class_id"
                                    class="text-red-500 text-xs font-semibold hidden">
                                </span>
    
                                <div class="overflow-x-auto mt-2">
                                    <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-xl">
                                        <table id="table-rombel-class-content-for-release" class="min-w-175 lg:min-w-full text-sm border-collapse">
                                            <thead class="thead-table-rombel-class-content-for-release bg-gray-50 hidden shadow-inner">
                                                <tr>
                                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Rombel Kelas</th>
                                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Status</th>
                                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tanggal Release</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-rombel-class-content-for-release">
                                                <!-- show data in ajax -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="empty-message-rombel-class-content-for-release-list" class="w-full h-80 hidden">
                                    <span class="flex h-full items-center justify-center text-gray-500">
                                        Tidak ada rombel kelas yang terdaftar.
                                    </span>
                                </div>
                            </div>
    
                            <!-- ================= CONTENT SECTION ================= -->
                            <div id="container" data-school-id="{{ $schoolId }}" class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10"> 
    
                                <!-- FILTER PANEL -->
                                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
                                    <h3 class="text-sm font-semibold text-gray-700">
                                        Filter Materi
                                    </h3>
    
                                    <div class="">
                                        <!--- search bar --->
                                        <label class="input input-bordered outline-none border-gray-300 flex items-center gap-2 w-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                            </svg>
                                            <input id="search_materi" type="search" class="grow text-sm"
                                                placeholder="Cari materi..." autocomplete="OFF" />
                                        </label>
                                    </div>
    
                                    <!--- Kurikulum --->
                                    <div class="flex flex-col">
                                        <label class="mb-2 text-sm">
                                            Kurikulum
                                        </label>
                                        <select name="kurikulum_id" id="id_kurikulum"
                                            class="w-full bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 focus:border cursor-pointer">
                                            <option value="" class="hidden">Pilih Kurikulum</option>
                                            @foreach ($getCurriculum as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama_kurikulum }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!--- Service --->
                                    <div class="flex flex-col">
                                        <label class="mb-2 text-sm">
                                            Service
                                        </label>
                                        <select name="service_id" id="id_service"
                                            class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                            <option class="hidden">Pilih Service</option>
                                        </select>
                                    </div>
    
                                    <!--- Kelas --->
                                    <div class="flex flex-col">
                                        <label class="mb-2 text-sm">
                                            Kelas
                                        </label>
                                        <select name="kelas_id" id="id_kelas"
                                            class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                            <option class="hidden">Pilih Kelas</option>
                                        </select>
                                    </div>
    
                                    <!--- Mapel --->
                                    <div class="flex flex-col">
                                        <label class="mb-2 text-sm">
                                            Mata Pelajaran
                                        </label>
                                        <select name="mapel_id" id="id_mapel"
                                            class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                            <option class="hidden">Pilih Mata Pelajaran</option>
                                        </select>
                                    </div>
    
                                    <!--- Bab --->
                                    <div class="flex flex-col">
                                        <label class="mb-2 text-sm">
                                            Bab
                                        </label>
                                        <select name="bab_id" id="id_bab"
                                            class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                            <option class="hidden">Pilih Bab</option>
                                        </select>
                                    </div>
                                </div>
    
                                <!-- MASTER CONTENT LIST -->
                                <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-4 md:p-6 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-700">
                                            Pilih Materi
                                        </h3>
                                        <span id="total-selected"
                                            class="text-blue-600 font-semibold">
                                            0 Dipilih
                                        </span>
                                    </div>

                                    <span id="error-lms_content_id"
                                        class="text-red-500 text-xs font-semibold hidden">
                                    </span>
    
                                    <div id="content-list-container" class="space-y-3 max-h-100 overflow-y-auto pr-1 hidden">
                                        <!-- show data in ajax -->
                                    </div>
    
                                    <div id="empty-message-content-list" class="w-full h-96 hidden">
                                        <span class="flex h-full items-center justify-center text-gray-500">
                                            Tidak ada content yang terdaftar.
                                        </span>
                                    </div>
                                </div>
                            </div>
    
                            <!-- ================= SUBMIT BUTTON ================= -->
                            <div class="bg-[#0071BC] rounded-2xl p-6 text-white flex flex-col lg:flex-row items-center justify-between gap-4">
    
                                <div>
                                    <p class="text-sm font-semibold text-center lg:text-left">
                                        Siap Dipublish
                                    </p>
                                    <div class="text-xs text-blue-100 mt-1 flex items-center gap-1">
                                        <span id="text-semester"></span>
                                        <span id="text-pertemuan"></span>
                                        <span id="total-rombel-selected"></span>
                                    </div>
                                </div>
    
                                <div class="flex gap-3">
                                    <button id="submit-button-draft-content-for-release" type="button" data-status="draft" class="px-5 py-2 text-sm rounded-xl bg-white/20 hover:bg-white/30 
                                        transition cursor-pointer default:cursor-default">
                                        Simpan Draft
                                    </button>

                                    <button id="submit-button-publish-content-for-release" type="button" data-status="publish" class="px-5 py-2 text-sm rounded-xl bg-white text-blue-700 
                                        font-semibold hover:bg-gray-100 transition shadow cursor-pointer default:cursor-default">
                                        Publish Sekarang
                                    </button>
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

<script src="{{ asset('assets/js/features/lms/teacher/content/paginate-teacher-content-for-release.js') }}"></script> <!--- paginate content for release ---->
<script src="{{ asset('assets/js/features/lms/teacher/content/teacher-form-content-for-release.js') }}"></script> <!--- form content for release ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown curriculum core ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-service-dropdown-only.js') }}"></script> <!--- dependent dropdown service only (without dynamic form) ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->