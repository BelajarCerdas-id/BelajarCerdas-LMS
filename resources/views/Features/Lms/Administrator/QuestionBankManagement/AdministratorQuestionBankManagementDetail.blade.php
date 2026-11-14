@include('components/sidebar-beranda', [
    'headerSideNav' => 'Review Question',
    'linkBackButton' => $schoolId
        ? route('lms.question_bank_management.view.school_partner', [$role, $schoolName, $schoolId])
        : route('lms.question_bank_management.view.no_school_partner', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative ml-0 md:ml-62.5 w-full transition-[margin] md:w-[calc(100%-250px)] min-h-screen bg-white duration-500 ease-in-out">
        <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">
            <!-- load question bank detail -->
            @include('Features.Lms.Components.QuestionBankManagement.LmsQuestionBankManagementDetail')
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/Lms/Components/QuestionBankManagement/PaginateQuestionBankManagementDetail.js') }}"></script> <!--- paginate lms question bank detail ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/PusherListener/Lms/QuestionBank/ReviewQuestionPgListener.js') }}"></script> <!--- pusher listener insert bank soal and edit soal in bankSoal detail ---->