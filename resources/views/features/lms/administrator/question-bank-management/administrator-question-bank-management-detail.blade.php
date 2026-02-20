@include('components/sidebar-beranda', [
    'headerSideNav' => 'Review Question',
    'linkBackButton' => $schoolId
        ? route('lms.questionBankManagement.view.schoolPartner', [$schoolName, $schoolId])
        : route('lms.questionBankManagement.view.noSchoolPartner'),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative ml-0 md:ml-62.5 w-full transition-[margin] md:w-[calc(100%-250px)] duration-500 ease-in-out">
        <div class="my-15 mx-7.5">
            <!-- load question bank detail -->
            @include('features.lms.components.question-bank-management.lms-question-bank-management-detail')
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/lms/components/question-bank-management/paginate-question-bank-management-detail.js') }}"></script> <!--- paginate lms question bank detail ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/question-bank/review-question-pg-listener.js') }}"></script> <!--- pusher listener insert bank soal and edit soal in bankSoal detail ---->