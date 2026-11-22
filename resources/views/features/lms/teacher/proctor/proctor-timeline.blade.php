@include('components/sidebar-beranda', ['headerSideNav' => 'Timeline Proctor'])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
    <div class="mt-4 sm:mt-6 mb-10 mx-7.5">
        <main class="bg-white rounded-2xl shadow-lg p-8 border border-gray-300">
            <div id="timeline-root" class="space-y-6"
                data-data-url="{{ route('lms.proctor.timeline.data', [$role, $schoolName, $schoolId]) }}"
                data-students-url="{{ route('lms.proctor.timeline.students', [$role, $schoolName, $schoolId]) }}">

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Timeline Proctor</h1>
                        <p class="text-gray-500 mt-1 text-sm">Riwayat tindakan guru, PIC buka kunci, dan administrator, beserta pelanggaran siswa.</p>
                    </div>
                    <a href="{{ route('lms.proctor.view', [$role, $schoolName, $schoolId]) }}" class="text-sm text-blue-700 hover:underline">← Kembali ke Proctor</a>
                </div>

                <div class="flex flex-wrap gap-2" role="tablist">
                    <button type="button" data-scope="all" class="scope-btn px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-600 text-white">Semua ujian</button>
                    <button type="button" data-scope="exam" class="scope-btn px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-200 text-gray-800">Per ujian</button>
                    <button type="button" data-scope="student" class="scope-btn px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-200 text-gray-800">Per siswa</button>
                </div>

                <div id="scope-exam" class="hidden">
                    <select id="timeline-exam" class="w-full md:w-96 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Pilih ujian...</option>
                        @foreach ($assessments as $assessment)
                            <option value="{{ $assessment->id }}">{{ $assessment->title }} ({{ $assessment->start_date->timezone('Asia/Jakarta')->format('d M Y') }})</option>
                        @endforeach
                    </select>
                </div>

                <div id="scope-student" class="hidden space-y-2">
                    <input id="timeline-student-search" type="text" placeholder="Cari nama siswa (min. 2 huruf)"
                        class="w-full md:w-96 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <ul id="timeline-student-list" class="text-sm space-y-1"></ul>
                    <p id="timeline-student-selected" class="text-sm text-gray-600"></p>
                </div>

                <p id="timeline-status" class="text-sm text-gray-500"></p>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b border-gray-200">
                                <th class="py-2 pr-3">Waktu</th>
                                <th class="py-2 pr-3">Kejadian</th>
                                <th class="py-2 pr-3">Oleh</th>
                                <th class="py-2 pr-3">Siswa</th>
                                <th class="py-2 pr-3">Ujian</th>
                                <th class="py-2">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="timeline-rows"></tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="{{ asset('assets/js/features/lms/teacher/proctor/proctor-timeline.js') }}?v={{ file_exists(public_path('assets/js/features/lms/teacher/proctor/proctor-timeline.js')) ? filemtime(public_path('assets/js/features/lms/teacher/proctor/proctor-timeline.js')) : time() }}"></script>
