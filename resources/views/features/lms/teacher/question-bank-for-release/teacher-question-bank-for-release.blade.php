@include('components/sidebar-beranda', ['headerSideNav' => 'Question Bank For Release']);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-create-question-for-release"></div>

            <main class="mb-10 space-y-6">
                <form id="teacher-create-question-bank-for-release-form">

                    <input type="hidden" id="total-weight-input" name="total_weight" value="">

                    <section id="container-form-teacher-question-bank-for-release" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">
                        <div class="space-y-6">
                                <div>
                                    <div class="flex items-center justify-between mb-10">
                                        <div>
                                            <h2 class="text-xl font-semibold text-gray-800">
                                                Question For Release Management
                                            </h2>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Atur distribusi bank soal untuk setiap rombel yang akan diujikan.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Academic Information -->
                                    <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-300 mb-10 space-y-6">
                                        <h2 class="text-lg text-[#0071BC] font-bold mb-6">
                                            <i class="fas fa-graduation-cap mr-2"></i>
                                            Academic Information
                                        </h2>

                                        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">

                                            <!-- Tahun Ajaran -->
                                            <div id="container-dropdown-tahun-ajaran">
                                                <!-- show data in ajax -->
                                            </div>

                                            <!-- Mapel -->
                                            <div id="container-dropdown-subject-rombel-class">
                                                <!-- show data in ajax -->
                                            </div>

                                            <!-- Assessment Type -->
                                            <div id="container-dropdown-assessment-type">
                                                <!-- show data in ajax -->
                                            </div>

                                            <!-- Semester -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">
                                                    Filter Semester
                                                </label>
                                                <select id="dropdown-filter-semester" name="semester" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm 
                                                    cursor-pointer outline-none">
                                                    <option value="" hidden>Pilih Semester</option>
                                                    <option value="1">Semester 1</option>
                                                    <option value="2">Semester 2</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Assessment Details -->
                                    <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-300 mb-10 space-y-6">

                                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
                                            <div>
                                                <h2 class="text-lg text-[#0071BC] font-bold mb-6">
                                                    <i class="fas fa-pen-to-square"></i>  
                                                    Assessment Details
                                                </h2>
                                            </div>
                                            
                                            <div id="container-dropdown-class">
                                                <!-- show data in ajax -->
                                            </div>
                                        </div>
                                        <span id="error-school_assessment_id"
                                            class="text-red-500 text-xs font-semibold hidden">
                                        </span>
            
                                        <div class="overflow-x-auto mt-2">
                                            <div class="max-h-80 overflow-y-auto">
                                                <table id="table-rombel-class-teacher-assessment" class="min-w-175 lg:min-w-full text-sm border-collapse">
                                                    <thead class="thead-table-rombel-class-teacher-assessment bg-gray-50 hidden shadow-inner">
                                                        <tr>
                                                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                                                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Rombel Kelas</th>
                                                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tipe Asesmen</th>
                                                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Judul Asesmen</th>
                                                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Semester</th>
                                                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tanggal Asesmen</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbody-rombel-class-teacher-assessment">
                                                        <!-- show data in ajax -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div id="empty-message-rombel-class-teacher-assessment-list" class="w-full h-80 hidden">
                                            <span class="flex h-full items-center justify-center text-gray-500">
                                                Tidak ada asesmen yang terdaftar.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </section>

                    <section>
                        <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-300 mb-10 space-y-6">
        
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="text-lg text-[#0071BC] font-bold">
                                        Manage Question For Assessment
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        Pilih soal dari bank soal untuk dimasukkan ke asesmen.
                                    </p>
                                </div>
                            </div>
        
                            <div id="container" data-school-id="{{ $schoolId }}" class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10"> 
            
                                <!-- FILTER PANEL -->
                                <div class="col-span-4 xl:col-span-1 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
                                    <h3 class="text-sm font-semibold text-gray-700">
                                        Filter Soal
                                    </h3>
            
                                    <div class="">
                                        <!--- search bar --->
                                        <label class="input input-bordered outline-none border-gray-300 flex items-center gap-2 w-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                            </svg>
                                            <input id="search_question" type="search" class="grow text-sm" placeholder="Cari Soal..." autocomplete="OFF" />
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
        
                                    <!--- Sub Bab --->
                                    <div class="flex flex-col">
                                        <label class="mb-2 text-sm">
                                            Sub Bab
                                        </label>
                                        <select name="sub_bab_id" id="id_sub_bab"
                                            class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                                            <option class="hidden">Pilih Sub Bab</option>
                                        </select>
                                    </div>
                                </div>
            
                                <!-- QUESTION LIST -->
                                <div class="col-span-4 xl:col-span-3 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        
                                    <!-- Header -->
                                    <div class="flex items-center justify-between mb-4">
        
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-700">
                                                Pilih Soal
                                            </h3>
        
                                            <p id="question-visibility-info"
                                            class="text-xs text-gray-500 mt-1">
                                                Menampilkan 0 soal
                                            </p>
                                        </div>
        
                                        <div class="text-right">
                                            <span id="total-selected"
                                                class="text-blue-600 font-semibold text-sm">
                                                0 Total aktif
                                            </span>
        
                                            <p id="selected-detail"
                                            class="text-xs text-gray-400 mt-1 hidden">
                                                (0 terlihat di halaman ini)
                                            </p>
                                        </div>
        
                                    </div>
        
                                    <span id="error-question_id" class="text-red-500 text-xs font-semibold hidden"></span>
        
                                    <div class="overflow-x-auto mt-2">
                                        <div class="max-h-125 overflow-y-auto">
                                            <table id="table-question-bank-list" class="min-w-175 lg:min-w-full text-sm border-collapse">
                                                <thead class="thead-table-question-bank-list bg-gray-50 hidden shadow-inner">
                                                    <tr>
                                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                                            <input type="checkbox" class="question-all-checkbox cursor-pointer">
                                                        </th>
                                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Soal</th>
                                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Difficulty</th>
                                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tipe Soal</th>
                                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Bobot</th>
                                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Sumber Soal</th>
                                                        <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Preview</th>
                                                    </tr>
                                                </thead>
        
                                                <tbody id="tbody-question-bank-list">
                                                    <!-- show data in ajax -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
        
                                    <div id="empty-message-question-bank-list" class="w-full h-80 hidden">
                                        <span class="flex h-full items-center justify-center text-gray-500">
                                            Tidak ada bank soal yang terdaftar.
                                        </span>
                                    </div>

                                    <!-- STICKY SUMMARY -->
                                    <div class="bg-white border-t border-gray-300 mt-6 pt-4 overflow-hidden">

                                        <!-- Progress -->
                                        <div class="mb-3">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div id="weight-progress" class="h-2 rounded-full transition-all" style="width: 0%;">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center">

                                            <div>
                                                <p class="text-sm font-semibold">
                                                    Total Bobot:
                                                    <span id="total-weight">0</span> / 100
                                                </p>

                                                <p id="error-total_weight" class="text-xs text-red-600 hidden"></p>
                                            </div>
                                        </div>
                                    </div>
        
                                </div>
        
                                <!-- SUBMIT BUTTON -->
                                <div class="bg-[#0071BC] rounded-2xl p-6 text-white flex flex-col lg:flex-row items-center justify-between gap-4 col-span-4">
            
                                    <div>
                                        <p class="text-sm font-semibold text-center lg:text-left">
                                            Siap Dipublish
                                        </p>
                                    </div>
            
                                    <div class="flex flex-col lg:flex-row gap-3 w-full lg:w-auto">
                                        <button id="submit-button-publish-question-for-release" type="button" data-status="publish" class="px-5 py-2 text-sm rounded-xl bg-white text-blue-700 
                                            font-semibold hover:bg-gray-100 transition shadow cursor-pointer default:cursor-default">
                                            Simpan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </form>

                <!-- MODAL PREVIEW -->
                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box max-w-3xl max-h-[80vh] p-0 overflow-hidden bg-white">
        
                        <!-- Header (No extra padding from modal) -->
                        <div class="bg-[#0071BC] text-white px-6 py-4">
                            <h3 class="font-semibold text-lg m-0">
                                Question Preview
                            </h3>
                        </div>
        
                        <!-- Content (Normal padding) -->
                        <div class="p-6 overflow-y-auto max-h-[60vh]">
                            <div id="modal-preview-content"class="prose max-w-none text-sm leading-relaxed"></div>
                        </div>
        
                        <!-- Footer (Normal padding) -->
                        <div class="px-6 py-4 border-t border-gray-300 flex justify-end bg-white">
                            <form method="dialog">
                                <button class="btn btn-sm">Tutup</button>
                            </form>
                        </div>
                    </div>
        
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <section id="container-paginate-teacher-question-bank-for-release-list" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                    class="mt-10 bg-white shadow-sm border border-gray-300 rounded-2xl p-6">

                    <h2 class="text-xl font-semibold text-gray-800">
                        Question For Release List
                    </h2>

                    <!-- FILTER CONTAINER -->
                    <div class="my-6 bg-gray-50 shadow-sm border border-gray-300 rounded-2xl p-6">

                        <!-- Header Filter -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-filter text-[#0071BC]"></i>
                                <h3 class="text-base font-semibold text-gray-800">
                                    Filter Soal
                                </h3>
                            </div>
                        </div>

                        <!-- Filter Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">

                            <div id="container-dropdown-tahun-ajaran-paginate-question-bank-for-release"></div>

                            <div id="container-dropdown-class-paginate-question-bank-for-release"></div>

                            <div id="container-dropdown-assessment-type-paginate-question-bank-for-release"></div>

                        </div>
                    </div>

                    <div class="overflow-x-auto mt-6 pb-5">
                        <table id="table-paginate-teacher-question-bank-for-release-list" class="min-w-175 lg:min-w-full text-sm border-collapse">
                            <thead class="thead-table-paginate-teacher-question-bank-for-release-list bg-gray-50 hidden shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Rombel Kelas</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tahun Ajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tipe Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Judul Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Semester</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tanggal Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Total Soal</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody-paginate-teacher-question-bank-for-release-list">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-paginate-teacher-question-bank-for-release-list flex justify-center my-10"></div>

                    <div id="empty-message-paginate-teacher-question-bank-for-release-list" class="w-full h-96 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada soal yang terdaftar.
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

<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/paginate-teacher-question-bank-for-release.js') }}"></script> <!--- paginate question bank for release ---->
<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/form-teacher-question-bank-for-release.js') }}"></script> <!--- form question bank for reelase ---->
<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/teacher-question-bank-for-release-weight-calculator.js') }}"></script> <!--- question bank for reelase weight calculator ---->
<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/teacher-question-bank-for-release-selection.js') }}"></script> <!--- question bank for reelase selection ---->
<script src="{{ asset('assets/js/features/lms/teacher/question-bank-for-release/teacher-question-bank-for-release-matching-renderer.js') }}"></script> <!--- question bank for reelase matching renderer ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown curriculum core ---->