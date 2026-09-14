@include('components/sidebar-beranda', [
    'headerSideNav' => 'Edit Content',
    'linkBackButton' => $schoolId
        ? route('lms.contentManagement.view.schoolPartner', [$role, $schoolName, $schoolId])
        : route('lms.contentManagement.view.noSchoolPartner', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">

            <div id="alert-success-edit-content"></div>

            @include('features.lms.components.content-management.lms-edit-content', ['getCurriculum' => $getCurriculum]) 
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif