@include('components/sidebar-beranda', ['headerSideNav' => 'Proctor'])

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
    <div class="mt-4 sm:mt-6 mb-10 mx-7.5">
        <main class="bg-white rounded-2xl shadow-lg p-8 border border-gray-300">
            <style>
@keyframes proctorBlink { 0%,100% { background-color: transparent; } 50% { background-color: #fef3c7; } }
.proctor-blink { animation: proctorBlink 1.2s ease-in-out 3; }
</style>

<div id="proctor-root" class="space-y-6"
                data-snapshot-url="{{ route('lms.proctor.snapshot', [$role, $schoolName, $schoolId]) }}"
                data-token-url="{{ route('lms.proctor.token', [$role, $schoolName, $schoolId]) }}"
                data-action-base="{{ url('/lms/'.$role.'/{schoolName}/{schoolId}/proctor') }}"
                data-session-url="{{ url('/lms/'.$role.'/'.$schoolName.'/'.$schoolId.'/proctor/assessments') }}"
                data-school-name="{{ $schoolName }}"
                data-school-id="{{ $schoolId }}"
                data-timeline-url="{{ route('lms.proctor.timeline.view', [$role, $schoolName, $schoolId]) }}"
                data-approvers-url="{{ route('lms.proctor.approvers.search', [$role, $schoolName, $schoolId]) }}"
                data-approver-toggle-url="{{ url('/lms/'.$role.'/'.$schoolName.'/'.$schoolId.'/proctor/approvers') }}"
                data-can-approve="{{ $canApprove ? '1' : '0' }}"
                data-is-guru="{{ \App\Services\Proctor\ProctorService::hasRole(Auth::user(), ['guru']) ? '1' : '0' }}"
                data-is-admin="{{ $isAdmin ? '1' : '0' }}"
                data-csrf="{{ csrf_token() }}">

                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Proctor Ujian</h1>
                    <p class="text-gray-500 mt-1 text-sm">Ringkasan ujian hari ini. Buka satu ujian untuk memantau siswa dan mengendalikan sesinya.</p>
                    <a id="proctor-timeline-link" href="{{ route('lms.proctor.timeline.view', [$role, $schoolName, $schoolId]) }}" class="inline-block mt-2 text-sm text-blue-700 hover:underline">Lihat timeline →</a>
                </div>

                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <label for="proctor-date">Tanggal</label>
                    <input id="proctor-date" type="date" value="{{ date('Y-m-d') }}"
                        class="border border-gray-300 rounded-lg px-2 py-1 text-sm bg-white">
                </div>

                <div id="proctor-status" class="text-sm text-gray-500">Menghubungkan...</div>

                <section id="proctor-cards" class="space-y-6"></section>

                @if ($isAdmin)
                    <section class="border-t border-gray-200 pt-6">
                        <h2 class="text-base font-semibold text-gray-700 mb-1">Approver buka kunci</h2>
                        <p class="text-gray-500 text-sm mb-3">Hanya pengguna yang diberi izin ini yang dapat menyetujui permintaan buka kunci.</p>
                        <input id="proctor-approver-search" type="text" placeholder="Cari email (min. 3 huruf)"
                            class="w-full md:w-96 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <ul id="proctor-approver-list" class="mt-3 space-y-2 text-sm"></ul>
                    </section>
                @endif
            </div>
        </main>
    </div>
</div>

<script src="{{ asset('assets/js/features/lms/teacher/proctor/proctor-dashboard.js') }}?v={{ file_exists(public_path('assets/js/features/lms/teacher/proctor/proctor-dashboard.js')) ? filemtime(public_path('assets/js/features/lms/teacher/proctor/proctor-dashboard.js')) : time() }}"></script>
