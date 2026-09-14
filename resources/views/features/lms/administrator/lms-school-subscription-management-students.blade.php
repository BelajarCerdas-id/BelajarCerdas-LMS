@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS Management Students',
    'linkBackButton' => $majorId
        ? route('lms.managementClass.view.major', [$role, $schoolName, $schoolId, $managedRole, $majorId])
        : route('lms.managementClass.view.noMajor', [$role, $schoolName, $schoolId, $managedRole]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">

            <div id="alert-success-promote-to-next-class"></div>
            <div id="alert-success-repeat-class"></div>
            <div id="alert-success-move-class"></div>
            <div id="alert-success-move-major"></div>

            <main>
                <!---- table list school partner lms subscription ---->
                <section id="container-management-lms-students-list" class="relative pb-6"
                    data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}"
                    data-managed-role="{{ $managedRole }}" data-class-id="{{ $classId }}"
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
                                            class="w-4 h-4 rounded border-gray-300 text-[#0071BC] focus:ring-[#0071BC] cursor-pointer">
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
                    <div class="modal-box bg-white max-w-[800px] rounded-2xl p-6 shadow-xl">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-base sm:text-lg font-bold text-center mb-4 text-gray-800">Naik Kelas Siswa</h3>

                        <form id="form-promote-students" class="space-y-5">
                            <input type="hidden" id="school-partner-id-promote-class" name="school_partner_id">
                            <input type="hidden" id="student-ids-promote-class" name="student_id">
                            <input type="hidden" id="major-id-promote-class" name="major_id">

                            <!-- INFO KELAS ASAL -->
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2">
                                <p class="text-sm font-semibold text-gray-700">Kelas Asal</p>

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Kelas</p>
                                        <p class="font-semibold text-gray-800" id="from-class-name">-</p>
                                    </div>
                                    <div class="text-none md:text-center">
                                        <p class="text-xs text-gray-500 font-medium">Tahun Ajaran</p>
                                        <p class="font-semibold text-gray-800" id="from-class-year">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Jumlah Siswa</p>
                                        <p class="font-semibold text-gray-800">
                                            <span id="promote-student-count">0</span> siswa
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- TAHUN AJARAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Tahun Ajaran Tujuan
                                </label>
                                <select id="target-school-year-promote" name="tahun_ajaran"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                    <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-tahun_ajaran" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- JURUSAN TUJUAN -->
                            <div id="container-major-promote" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Jurusan Tujuan
                                </label>
                                <select id="target-school-major-promote" name="major_id"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default" disabled>
                                    <option value="" class="hidden">Pilih Jurusan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-major_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- KELAS TUJUAN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Kelas Tujuan
                                </label>
                                <select id="target-class-id-promote" name="school_class_id"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default" disabled>
                                    <option value="" class="hidden">Pilih Kelas Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-school_class_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- INFO OTOMATIS -->
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3.5 text-xs text-blue-700 font-medium">
                                Wali kelas akan mengikuti wali kelas dari kelas tujuan secara otomatis.
                            </div>

                            <!-- ACTION -->
                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-promote-class" type="button"
                                    class="h-11 px-6 rounded-xl bg-[#0071BC] hover:bg-blue-600 text-white font-semibold text-sm shadow-sm hover:shadow transition-all cursor-pointer disabled:cursor-default disabled:opacity-50">
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
                    <div class="modal-box bg-white max-w-[800px] rounded-2xl p-6 shadow-xl">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-base sm:text-lg font-bold text-center mb-4 text-gray-800">Mengulang Kelas Siswa</h3>

                        <form id="form-repeat-class-students" class="space-y-5">
                            <input type="hidden" id="school-partner-id-repeat-class" name="school_partner_id">
                            <input type="hidden" id="student-ids-repeat-class" name="student_id">
                            <input type="hidden" id="major-id-repeat-class" name="major_id">

                            <!-- INFO KELAS ASAL -->
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2">
                                <p class="text-sm font-semibold text-gray-700">Kelas Asal</p>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Kelas</p>
                                        <p class="font-semibold text-gray-800" id="from-class-name-repeat">-</p>
                                    </div>
                                    <div class="text-none md:text-center">
                                        <p class="text-xs text-gray-500 font-medium">Tahun Ajaran</p>
                                        <p class="font-semibold text-gray-800" id="from-class-year-repeat">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Jumlah Siswa</p>
                                        <p class="font-semibold text-gray-800">
                                            <span id="student-count-repeat-class">0</span> siswa
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- TAHUN AJARAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Tahun Ajaran Tujuan
                                </label>
                                <select id="target-school-year-repeat" name="tahun_ajaran"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                    <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-tahun_ajaran" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- KELAS TUJUAN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Kelas Tujuan
                                </label>
                                <select id="target-class-id-repeat" name="school_class_id"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default" disabled>
                                    <option value="" class="hidden">Pilih Kelas Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-school_class_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- INFO OTOMATIS -->
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3.5 text-xs text-blue-700 font-medium">
                                Wali kelas akan mengikuti wali kelas dari kelas tujuan secara otomatis.
                            </div>

                            <!-- ACTION -->
                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-repeat-class" type="button"
                                    class="h-11 px-6 rounded-xl bg-[#0071BC] hover:bg-blue-600 text-white font-semibold text-sm shadow-sm hover:shadow transition-all cursor-pointer disabled:cursor-default disabled:opacity-50">
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
                    <div class="modal-box bg-white max-w-[800px] rounded-2xl p-6 shadow-xl">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-base sm:text-lg font-bold text-center mb-4 text-gray-800">Pindah Kelas Siswa</h3>

                        <form id="form-move-class-students" class="space-y-5">
                            <input type="hidden" id="school-partner-id-move-class" name="school_partner_id">
                            <input type="hidden" id="student-ids-move-class" name="student_id">
                            <input type="hidden" id="major-id-move-class" name="major_id">

                            <!-- INFO KELAS ASAL -->
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2">
                                <p class="text-sm font-semibold text-gray-700">Kelas Asal</p>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Kelas</p>
                                        <p class="font-semibold text-gray-800" id="from-class-name-move">-</p>
                                    </div>
                                    <div class="text-none md:text-center">
                                        <p class="text-xs text-gray-500 font-medium">Tahun Ajaran</p>
                                        <p class="font-semibold text-gray-800" id="from-class-year-move">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Jumlah Siswa</p>
                                        <p class="font-semibold text-gray-800">
                                            <span id="student-count-move-class">0</span> siswa
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- TAHUN AJARAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Tahun Ajaran Tujuan
                                </label>
                                <select id="target-school-year-move-class" name="tahun_ajaran"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                    <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-tahun_ajaran" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- KELAS TUJUAN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Kelas Tujuan
                                </label>
                                <select id="target-class-id-move" name="school_class_id"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default" disabled>
                                    <option value="" class="hidden">Pilih Kelas Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-school_class_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- INFO OTOMATIS -->
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3.5 text-xs text-blue-700 font-medium">
                                Wali kelas akan mengikuti wali kelas dari kelas tujuan secara otomatis.
                            </div>

                            <!-- ACTION -->
                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-move-class" type="button"
                                    class="h-11 px-6 rounded-xl bg-[#0071BC] hover:bg-blue-600 text-white font-semibold text-sm shadow-sm hover:shadow transition-all cursor-pointer disabled:cursor-default disabled:opacity-50">
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
                    <div class="modal-box bg-white max-w-[800px] rounded-2xl p-6 shadow-xl">

                        <!-- untuk menghilangkan focus input type pada saat open modal  --->
                        <div tabindex="-1"></div> <!-- Tambahkan ini -->

                        <h3 class="text-base sm:text-lg font-bold text-center mb-4 text-gray-800">Pindah Jurusan Siswa</h3>

                        <form id="form-move-major-students" class="space-y-5">
                            <input type="hidden" id="school-partner-id-move-major" name="school_partner_id">
                            <input type="hidden" id="student-ids-move-major" name="student_id">

                            <!-- INFO KELAS ASAL -->
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2">
                                <p class="text-sm font-semibold text-gray-700">Kelas Asal</p>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Kelas</p>
                                        <p class="font-semibold text-gray-800" id="from-class-name-move-major">-</p>
                                    </div>
                                    <div class="text-none md:text-center">
                                        <p class="text-xs text-gray-500 font-medium">Tahun Ajaran</p>
                                        <p class="font-semibold text-gray-800" id="from-class-year-move-major">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Jumlah Siswa</p>
                                        <p class="font-semibold text-gray-800">
                                            <span id="student-count-move-major">0</span> siswa
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- TAHUN AJARAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Tahun Ajaran Tujuan
                                </label>
                                <select id="target-school-year-move-major" name="tahun_ajaran"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                                    <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-tahun_ajaran" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- JURUSAN TUJUAN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Jurusan Tujuan
                                </label>
                                <select id="target-move-major-id" name="major_id"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default" disabled>
                                    <option value="" class="hidden">Pilih Jurusan Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-major_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- KELAS TUJUAN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Kelas Tujuan
                                </label>
                                <select id="target-move-major-class-id" name="school_class_id"
                                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 opacity-50 cursor-default" disabled>
                                    <option value="" class="hidden">Pilih Kelas Tujuan</option>
                                    <!-- show option in ajax -->
                                </select>
                                <span id="error-school_class_id" class="text-red-500 font-bold text-xs pt-1"></span>
                            </div>

                            <!-- INFO OTOMATIS -->
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3.5 text-xs text-blue-700 font-medium">
                                Wali kelas akan mengikuti wali kelas dari kelas tujuan secara otomatis.
                            </div>

                            <!-- ACTION -->
                            <div class="flex justify-end gap-2 pt-4">
                                <button id="submit-button-move-major" type="button"
                                    class="h-11 px-6 rounded-xl bg-[#0071BC] hover:bg-blue-600 text-white font-semibold text-sm shadow-sm hover:shadow transition-all cursor-pointer disabled:cursor-default disabled:opacity-50">
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
<script src="{{ asset('assets/js/features/lms/administrator/lms-subscription-management-students.js') }}"></script> <!--- lms subscription management students ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/administrator/management-student-in-class.js') }}"></script> <!--- pusher listener pada saat activate siswa di kelas ---->
