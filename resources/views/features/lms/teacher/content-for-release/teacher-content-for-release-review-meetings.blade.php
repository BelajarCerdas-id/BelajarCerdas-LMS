@include('components/sidebar-beranda', [
    'headerSideNav' => 'Review Meetings',
    'linkBackButton' => route('lms.teacherContentForRelease.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-10 mx-6 space-y-8">

            <div id="alert-success-edit-meeting-content"></div>

            <main>
                <section id="container-content-for-release-review-meetings" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                        data-school-class-id="{{ $schoolClassId }}" data-mapel-id="{{ $mapelId }}" data-semester="{{ $semester }}" data-service-id="{{ $serviceId }}">

                    <div id="header-meetings" class="hidden">
                        <!-- show data in ajax -->
                    </div>

                    <div id="grid-list-meeting" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                        <div class="px-6 py-4 border-b border-gray-300 text-sm font-semibold text-gray-700 bg-gray-50">
                            Daftar Pertemuan
                        </div>

                        <div class="divide-y divide-gray-300" id="grid-list-meeting-body">
                            <!-- show data in ajax -->
                        </div>

                    </div>

                    <div id="empty-message-content-for-release-review-meetings" class="w-full h-80 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada content yang terdaftar.
                        </span>
                    </div>
                </section>

                <!---- modal edit meeting content ---->
                <dialog id="my_modal_1" class="modal">
                    <div class="modal-box bg-white w-[85%] md:w-max h-155">
                        <form id="edit-meeting-content-form" autocomplete="OFF">
                            <span class="text-xl font-bold flex justify-center">Edit Meeting Content</span>

                            <input type="hidden" id="edit-meeting-content-id" name="meeting_content_id">
                            <input type="hidden" id="edit-semester" name="semester">
                            <input type="hidden" id="edit-meeting-number" name="meeting_number">
                            <input type="hidden" id="edit-meeting-date" name="meeting_date">

                            <div class="mt-4 w-full md:w-96.25">
                                <label class="text-sm font-medium text-gray-600 mb-1">
                                    Pilih Semester
                                </label>
                                    <select id="edit-semester-name" name="semester" class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm pr-6 cursor-pointer">
                                        <option value="" class="hidden">Pilih Semester</option>
                                        <option value="1">Semester 1</option>
                                        <option value="2">Semester 2</option>
                                    </select>
                                <span id="error-semester" class="text-red-500 text-xs mt-1 font-bold"></span>
                            </div>

                            <div class="mt-4 w-full md:w-96.25">
                                <label class="text-sm font-medium text-gray-500 mb-1">
                                    Pertemuan
                                </label>

                                <select id="edit-pertemuan" name="meeting_number"
                                    class="w-full bg-white shadow-lg rounded-md h-12 outline-none border border-gray-300 text-sm pr-6 cursor-pointer">

                                        <option value="" class="hidden">Pilih Pertemuan</option>
                                        @for ($i = 1; $i <= 16; $i++)
                                            <option value="{{ $i }}">
                                                Pertemuan {{ $i }}
                                            </option>
                                        @endfor
                                </select>
                                    <span id="error-meeting_number" class="text-red-500 text-xs mt-1 font-bold"></span>
                            </div>

                            <div class="w-full relative flex flex-col gap-2 mt-4">
                                <label class="text-sm font-medium text-gray-500 block">
                                    Tanggal Pertemuan
                                </label>

                                <input type="text" id="edit-tanggal-pertemuan" name="meeting_date"
                                    class="rombel-date w-full md:w-96.25 bg-white border border-gray-300 rounded-lg px-2 h-12 text-sm shadow-sm outline-none transition duration-200"
                                    placeholder="Pilih tanggal release">

                                    <i class="fa-regular fa-calendar absolute top-[60%] right-4"></i>
                            </div>

                            <span id="error-meeting_date" class="text-red-500 text-xs font-semibold"></span>

                            <div class="flex justify-end mt-8">
                                <button id="submit-button-edit-meeting-content" type="button"
                                    class="inline-flex items-center gap-2 rounded-lg bg-[#0071BC] px-6 py-2.5 text-sm font-bold text-white cursor-pointer disabled:cursor-default">
                                        Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="dialog" class="modal-backdrop">
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

<script src="{{ asset('assets/js/features/lms/teacher/content-for-release/paginate-teacher-content-for-release-review-meeting.js') }}"></script> <!--- paginate content for release review meeting ---->