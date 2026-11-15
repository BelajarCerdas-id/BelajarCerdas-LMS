@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS',
]);

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">
            <main>
                <section>
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <h3 class="font-bold text-slate-800 text-lg pb-4">Pilihan Mapel</h3>
                        <div class="relative w-full sm:w-64">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input id="mapel-search" type="text" placeholder="Cari mata pelajaran..." class="w-full h-11 pl-9 pr-4 text-sm bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-[#0071BC] focus:ring-2 focus:ring-sky-100 transition">
                        </div>
                    </div>

                    <div id="container-paginate-lms-student" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">

                        <div id="grid-list-mapel" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 mt-8">
                            <!-- show data in ajax -->
                        </div>

                    </div>

                    <div id="empty-message-list-mapel" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada mata pelajaran.
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

<script src="{{ asset('assets/js/features/lms/student/paginate-lms-student.js') }}?v=3"></script>