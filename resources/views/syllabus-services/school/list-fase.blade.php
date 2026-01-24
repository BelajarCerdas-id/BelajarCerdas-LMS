@include('components/sidebar-beranda', [
    'linkBackButton' => route('schoolCurriculumManagement.view', [$schoolName, $schoolId, $curriculumName, $curriculumId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'headerSideNav' => 'Fase',
]);
@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- DETAIL SEKOLAH -->
            <div id="school-detail-card"
                class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden">
            </div>

            <!---- Table list data fase  ---->
            <div id="container-fase-management" class="overflow-x-auto mt-8 pb-24" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" 
                    data-curriculum-name="{{ $curriculumName }}" data-curriculum-id="{{ $curriculumId }}">
                <table id="table-fase-management" class="min-w-full text-sm border-collapse">
                    <thead class="thead-table-fase-management hidden bg-gray-50 shadow-inner">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs w-[60%] lg:w-[80%]">
                                Fase
                            </th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                Detail
                            </th>
                        </tr>
                    </thead>
                        <tbody id="tbody-fase-management">
                            <!-- show data in ajax -->
                        </tbody>
                </table>
            </div>
            <div class="pagination-container-fase-management flex justify-center my-4 sm:my-0"></div>

            <div id="empty-message-fase-management" class="w-full h-96 hidden">
                <span class="w-full h-full flex items-center justify-center">
                    Tidak ada fase.
                </span>
            </div>
        </div>
    </div>
@else
    <p>You do not have access to this pages.</p>
@endif

<script src="{{ asset('assets/js/syllabus-services/school/fase-management.js') }}"></script> <!--- paginate fase ---->