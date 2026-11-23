@include('components/sidebar-beranda', ['headerSideNav' => 'Proctor'])

@if (Auth::user()->role !== 'Siswa' && Auth::user()->role !== 'Finance')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">
            <main>
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-shield-halved text-[#0071BC]"></i>
                    <h2 class="text-base sm:text-lg font-bold text-gray-800">Persetujuan Buka Kunci (PIC)</h2>
                </div>
                <p class="text-sm text-gray-500 mb-6">Ringkasan ujian hari ini. Setujui atau tolak permintaan buka kunci; buka detail kelas untuk aksi per siswa.</p>

                <section id="container-pic-exams" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">
                    <div id="grid-pic-exams" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-10">
                        <!-- exam cards: show in ajax -->
                    </div>

                    <h3 class="text-sm font-bold text-gray-700 mb-3">Permintaan Buka Kunci Menunggu</h3>
                    <div class="overflow-x-auto border border-gray-200 rounded-2xl shadow-sm">
                        <table class="min-w-full text-sm border-collapse">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border border-gray-200 px-3 py-2 text-xs text-gray-600">Siswa</th>
                                    <th class="border border-gray-200 px-3 py-2 text-xs text-gray-600">Ujian</th>
                                    <th class="border border-gray-200 px-3 py-2 text-xs text-gray-600">Kelas</th>
                                    <th class="border border-gray-200 px-3 py-2 text-xs text-gray-600">Jenis</th>
                                    <th class="border border-gray-200 px-3 py-2 text-xs text-gray-600">Pelanggaran</th>
                                    <th class="border border-gray-200 px-3 py-2 text-xs text-gray-600">Sisa waktu</th>
                                    <th class="border border-gray-200 px-3 py-2 text-xs text-gray-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-pic-requests">
                                <!-- requests: show in ajax -->
                            </tbody>
                        </table>
                    </div>
                    <div id="empty-message-pic-requests" class="hidden text-center text-gray-500 text-sm py-8">
                        Tidak ada permintaan buka kunci yang menunggu.
                    </div>
                </section>
            </main>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/teacher/proctor/proctor-pic.js') }}?v={{ file_exists(public_path('assets/js/features/lms/teacher/proctor/proctor-pic.js')) ? filemtime(public_path('assets/js/features/lms/teacher/proctor/proctor-pic.js')) : time() }}"></script>
