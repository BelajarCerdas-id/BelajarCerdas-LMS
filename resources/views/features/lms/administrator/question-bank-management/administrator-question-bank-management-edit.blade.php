@include('components/sidebar-beranda', [
    'headerSideNav' => 'Edit Question',
    'linkBackButton' => $schoolId
        ? route('lms.questionBankManagementDetail.view.schoolPartner', [$source, $questionType, $subBabId, $schoolName, $schoolId])
        : route('lms.questionBankManagementDetail.view.noSchoolPartner', [$source, $questionType, $subBabId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <!-- alert success -->
            <div id="alert-success-bank-soal-edit-question"></div>

            @include('features.lms.components.question-bank-management.lms-question-bank-management-edit')
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/lms/components/question-bank-management/form-action-question-bank-edit.js') }}"></script> <!--- form action question bank edit ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/question-bank/edit-question-pg-listener.js') }}"></script> <!--- pusher listener pada saat edit bank soal ---->
