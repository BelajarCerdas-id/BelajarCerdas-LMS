@include('components/sidebar-beranda', [
    'headerSideNav' => 'Review Content',
    'linkBackButton' => $schoolId
        ? route('lms.contentManagement.view.schoolPartner', [$role, $schoolName, $schoolId])
        : route('lms.contentManagement.view.noSchoolPartner', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
    <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">
        <!-- load review content -->
        @include('features.lms.components.content-management.lms-review-content', ['data' => $data])
    </div>
</div>