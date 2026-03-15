@include('components/sidebar-beranda', [
    'headerSideNav' => 'Review Meetings',
    'linkBackButton' => route('lms.studentLearning.view', [$role, $schoolName, $schoolId, $curriculumId, $mapelId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main>
                <section>
                    @include('features/lms/student/components/subject-header-progress')

                    <!-- TOP NAV -->
                    <div class="w-full mb-6 mt-10">
                        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                            <!-- LEFT : BACK -->
                            <div class="lg:w-1/3">
                                <button onclick="window.history.back()"
                                    class="flex items-center gap-2 font-bold opacity-70 hover:text-black transition cursor-pointer">
                                    <i class="fa-solid fa-chevron-left text-sm"></i>
                                    <span>Kembali</span>
                                </button>
                            </div>

                            <!-- CENTER : SERVICE NAME -->
                            <div class="lg:absolute lg:left-1/2 lg:-translate-x-1/2 text-center w-full lg:w-auto">
                                <h2 id="service-title"
                                    class="text-base md:text-lg font-semibold text-[#0071BC] pb-1">
                                    {{ $getService->name }}
                                </h2>

                                <div class="border-b-2 border-[#0071BC] w-full lg:w-82.25"></div>
                            </div>

                            <!-- RIGHT SPACER -->
                            <div class="hidden lg:block lg:w-1/3"></div>

                        </div>
                    </div>

                    <div id="container-review-meeting" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                        data-curriculum-id="{{ $curriculumId }}" data-mapel-id="{{ $mapelId }}" data-service-id="{{ $serviceId }}">

                        <div id="grid-list-review-meeting" class="grid grid-cols-1 lg:grid-cols-2 gap-14 mt-10">
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <div id="empty-message-review-content" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada content yang terdaftar.
                        </span>
                    </div>
                </section>

                <!---- modal edit meeting content ---->
                <dialog id="reviewModal" class="modal">
                    <div class="modal-box bg-white max-w-7xl max-h-[90vh] p-0 rounded-2xl overflow-hidden">

                        <!-- HEADER -->
                        <div class="bg-linear-to-r from-[#0071BC] to-[#29ABE2] px-6 py-4 flex justify-between items-center">
                            <h3 class="text-white font-semibold text-lg">
                                Review Content
                            </h3>

                            <form method="dialog">
                                <button onclick="closeModal()" class="text-white text-xl hover:scale-110 transition cursor-pointer">
                                    ✕
                                </button>
                            </form>
                        </div>

                        <!-- BODY -->
                        <div id="modal-content-container"
                            class="p-6 overflow-y-auto max-h-[75vh] space-y-5">
                        </div>

                    </div>

                    <form method="dialog" onclick="closeModal()" class="modal-backdrop">
                        <button>Close</button>
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

<script src="{{ asset('assets/js/features/lms/student/learning/paginate-student-review-meeting.js') }}"></script> <!-- - paginate student review meeting ---->