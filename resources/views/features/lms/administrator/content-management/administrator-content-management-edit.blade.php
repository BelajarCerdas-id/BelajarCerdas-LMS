@include('components/sidebar-beranda', [
    'headerSideNav' => 'Edit Content',
    'linkBackButton' => $schoolId
        ? route('lms.contentManagement.view.schoolPartner', [$schoolName, $schoolId])
        : route('lms.contentManagement.view.noSchoolPartner'),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

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