@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS Management Students',
    'linkBackButton' => $majorId
        ? route('lms.managementClass.view.major', [$schoolName, $schoolId, $role, $majorId])
        : route('lms.managementClass.view.noMajor', [$schoolName, $schoolId, $role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-promote-to-next-class"></div>
            <div id="alert-success-repeat-class"></div>
            <div id="alert-success-move-class"></div>
            <div id="alert-success-move-major"></div>

            <main>
                <!---- table list school partner lms subscription ---->
                <section id="container-management-lms-students-list" class="relative pb-6"
                    data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}"
                    data-role="{{ $role }}" data-class-id="{{ $classId }}"
                    data-major-id="{{ $majorId }}">
                    <div class="overflow-x-auto pb-14">
                        <!-- DETAIL SEKOLAH -->
                        <div id="school-detail-card"
                            class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
                        </div>

                        <!-- DETAIL KELAS & GURU -->
                        <div id="class-teacher-card"
                            class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
                        </div>

                        <div id="container-bulk-action-promote-to-next-class" class="hidden">

                        </div>

                        <table id="table-management-lms-students" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-management-lms-students hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <input type="checkbox" id="check-all"
                                            class="w-4 h-4 rounded border-gray-300 text-[#4189E0] focus:ring-[#4189E0] cursor-pointer">
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Siswa</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Enrollment Type</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Status Siswa di Kelas</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="table-management-lms-students-list">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>

                        <div class="pagination-container-lms-students flex justify-center my-4 sm:my-0"></div>

                        <div id="empty-message-lms-students" class="w-full h-96 hidden">
                            <span class="w-full h-full flex items-center justify-center">
                                Tidak ada user yang terdafatar pada sekolah ini.
                            </span>
                        </div>
                    </div>
                </section>

                <!---- modal menaikkan kelas ---->
                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box bg-white max-w-200">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-lg font-bold text-center mb-4">Naik Kelas Siswa</h3>

                        <form id="form-promote-students" class="space-y-5">
                            <input type="hidden" id="school-partner-id-promote-class" name="school_partner_id">
                            <input type="hidden" id="student-ids-promote-class" name="student_id">
                            <input type="hidden" id="major-id-promote-class" name="major_id">

                            <!-- INFO KELAS ASAL -->
                            <div class="bg-gray-50 border border-gray-300 rounded-xl p-4 space-y-2">
                                <p class="text-sm font-semibold text-gray-700">Kelas Asal</p>

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Kelas</p>
                                        <p class="font-medium text-gray-800" id="from-class-name">-</p>
                                    </div>
                                    <div class="text-none md:text-center">
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Tahun Ajaran</p>
                                        <p class="font-medium text-gray-800" id="from-class-year">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Jumlah Siswa</p>
                                        <p class="font-medium text-gray-800">
                                            <span id="promote-student-count">0</span> siswa
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- TAHUN AJARAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-bold opacity-70 mb-1">
                                    Tahun Ajaran Tujuan
                                </label>
                                <select id="target-school-year-promote" name="tahun_ajaran"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-2 cursor-pointer">
                                    <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-tahun_ajaran"class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!-- KELAS TUJUAN -->
                            <div>
                                <label class="block text-sm font-bold opacity-70 mb-1">
                                    Kelas Tujuan
                                </label>
                                <select id="target-class-id-promote" name="school_class_id"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-2 cursor-default opacity-50" disabled>
                                    <option value="" class="hidden">Pilih Kelas Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-school_class_id"class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!-- INFO OTOMATIS -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                                Wali kelas akan mengikuti wali kelas dari kelas tujuan secara otomatis.
                            </div>

                            <!-- ACTION -->
                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-promote-class" type="button"
                                    class="px-5 py-2 rounded-lg bg-[#4189E0] text-white font-semibold cursor-pointer disabled:cursor-default">
                                    Naikkan Kelas
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!---- modal mengulang kelas ---->
                <dialog id="my_modal_2" class="modal">
                    <div class="modal-box bg-white max-w-200">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-lg font-bold text-center mb-4">Mengulang Kelas Siswa</h3>

                        <form id="form-repeat-class-students" class="space-y-5">
                            <input type="hidden" id="school-partner-id-repeat-class" name="school_partner_id">
                            <input type="hidden" id="student-ids-repeat-class" name="student_id">
                            <input type="hidden" id="major-id-repeat-class" name="major_id">

                            <!-- INFO KELAS ASAL -->
                            <div class="bg-gray-50 border border-gray-300 rounded-xl p-4 space-y-2">
                                <p class="text-sm font-semibold text-gray-700">Kelas Asal</p>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Kelas</p>
                                        <p class="font-medium text-gray-800" id="from-class-name-repeat">-</p>
                                    </div>
                                    <div class="text-none md:text-center">
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Tahun Ajaran</p>
                                        <p class="font-medium text-gray-800" id="from-class-year-repeat">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Jumlah Siswa</p>
                                        <p class="font-medium text-gray-800">
                                            <span id="student-count-repeat-class">0</span> siswa
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- TAHUN AJARAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-bold opacity-70 mb-1">
                                    Tahun Ajaran Tujuan
                                </label>
                                <select id="target-school-year-repeat" name="tahun_ajaran"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-2 cursor-pointer">
                                    <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-tahun_ajaran"class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!-- KELAS TUJUAN -->
                            <div>
                                <label class="block text-sm font-bold opacity-70 mb-1">
                                    Kelas Tujuan
                                </label>
                                <select id="target-class-id-repeat" name="school_class_id"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-2 cursor-default opacity-50" disabled>
                                    <option value="" class="hidden">Pilih Kelas Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-school_class_id"class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!-- INFO OTOMATIS -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                                Wali kelas akan mengikuti wali kelas dari kelas tujuan secara otomatis.
                            </div>

                            <!-- ACTION -->
                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-repeat-class" type="button"
                                    class="px-5 py-2 rounded-lg bg-[#4189E0] text-white font-semibold cursor-pointer disabled:cursor-default">
                                    Mengulang Kelas
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!---- modal pindah kelas ---->
                <dialog id="my_modal_3" class="modal">
                    <div class="modal-box bg-white max-w-200">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-lg font-bold text-center mb-4">Pindah Kelas Siswa</h3>

                        <form id="form-move-class-students" class="space-y-5">
                            <input type="hidden" id="school-partner-id-move-class" name="school_partner_id">
                            <input type="hidden" id="student-ids-move-class" name="student_id">
                            <input type="hidden" id="major-id-move-class" name="major_id">

                            <!-- INFO KELAS ASAL -->
                            <div class="bg-gray-50 border border-gray-300 rounded-xl p-4 space-y-2">
                                <p class="text-sm font-semibold text-gray-700">Kelas Asal</p>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Kelas</p>
                                        <p class="font-medium text-gray-800" id="from-class-name-move">-</p>
                                    </div>
                                    <div class="text-none md:text-center">
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Tahun Ajaran</p>
                                        <p class="font-medium text-gray-800" id="from-class-year-move">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Jumlah Siswa</p>
                                        <p class="font-medium text-gray-800">
                                            <span id="student-count-move-class">0</span> siswa
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- TAHUN AJARAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-bold opacity-70 mb-1">
                                    Tahun Ajaran Tujuan
                                </label>
                                <select id="target-school-year-move-class" name="tahun_ajaran"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-2 cursor-pointer">
                                    <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-tahun_ajaran"class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!-- KELAS TUJUAN -->
                            <div>
                                <label class="block text-sm font-bold opacity-70 mb-1">
                                    Kelas Tujuan
                                </label>
                                <select id="target-class-id-move" name="school_class_id"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-2 cursor-default opacity-50" disabled>
                                    <option value="" class="hidden">Pilih Kelas Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-school_class_id"class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <!-- INFO OTOMATIS -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                                Wali kelas akan mengikuti wali kelas dari kelas tujuan secara otomatis.
                            </div>

                            <!-- ACTION -->
                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-move-class" type="button"
                                    class="px-5 py-2 rounded-lg bg-[#4189E0] text-white font-semibold cursor-pointer disabled:cursor-default">
                                    Pindah Kelas
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!---- modal pindah jurusan ---->
                <dialog id="my_modal_4" class="modal">
                    <div class="modal-box bg-white max-w-200">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-lg font-bold text-center mb-4">Pindah Jurusan Siswa</h3>

                        <form id="form-move-major-students" class="space-y-5">
                            <input type="hidden" id="school-partner-id-move-major" name="school_partner_id">
                            <input type="hidden" id="student-ids-move-major" name="student_id">

                            <!-- INFO KELAS ASAL -->
                            <div class="bg-gray-50 border border-gray-300 rounded-xl p-4 space-y-2">
                                <p class="text-sm font-semibold text-gray-700">Kelas Asal</p>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Kelas</p>
                                        <p class="font-medium text-gray-800" id="from-class-name-move-major">-</p>
                                    </div>
                                    <div class="text-none md:text-center">
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Tahun Ajaran</p>
                                        <p class="font-medium text-gray-800" id="from-class-year-move-major">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-700 font-bold opacity-70">Jumlah Siswa</p>
                                        <p class="font-medium text-gray-800">
                                            <span id="student-count-move-major">0</span> siswa
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- TAHUN AJARAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-bold opacity-70 mb-1">
                                    Tahun Ajaran Tujuan
                                </label>
                                <select id="target-school-year-move-major" name="tahun_ajaran"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-2 cursor-pointer">
                                    <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-tahun_ajaran" class="text-xs text-red-500"></span>
                            </div>

                            <!-- JURUSAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-bold opacity-70 mb-1">
                                    Jurusan Tujuan
                                </label>
                                <select id="target-move-major-id" name="major_id"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-2 cursor-default opacity-50" disabled>
                                    <option value="" class="hidden">Pilih Jurusan Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-major_id" class="text-xs text-red-500"></span>
                            </div>

                            <!-- KELAS TUJUAN -->
                            <div>
                                <label class="block text-sm font-bold opacity-70 mb-1">
                                    Kelas Tujuan
                                </label>
                                <select id="target-move-major-class-id" name="school_class_id"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm px-2 cursor-default opacity-50" disabled>
                                    <option value="" class="hidden">Pilih Kelas Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-school_class_id" class="text-xs text-red-500"></span>
                            </div>

                            <!-- INFO OTOMATIS -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                                Wali kelas akan mengikuti wali kelas dari kelas tujuan secara otomatis.
                            </div>

                            <!-- ACTION -->
                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-move-major" type="button"
                                    class="px-5 py-2 rounded-lg bg-[#4189E0] text-white font-semibold cursor-pointer disabled:cursor-default">
                                    Pindah Jurusan
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<!---- paginate lms subscription management users ---->
<script src="{{ asset('assets/js/Features/lms/administrator/lms-subscription-management-students.js') }}"></script> <!--- lms subscription management students ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/administrator/management-student-in-class.js') }}"></script> <!--- pusher listener pada saat activate siswa di kelas ---->
