@include('components/sidebar-beranda', [
    'headerSideNav' => 'LMS',
]);

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main>
                <section>
                    <div class="">
                        <h3 class="font-bold opacity-70 text-xl pb-4">Pilihan Mapel</h3>
                        <hr class="border border-gray-300 w-40">
                    </div>

                    <div id="container-paginate-lms-student" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">

                        <div id="grid-list-mapel" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-14 mt-10">
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

<script src="{{ asset('assets/js/features/lms/student/paginate-lms-student.js') }}"></script>