@include('components/sidebar-beranda', [
    'headerSideNav' => 'Edit Question',
    'linkBackButton' => $schoolId
        ? route('lms.question_bank_management_detail.view.school_partner', [$role, $schoolName, $schoolId,  $kurikulumId, $kelasId, $mapelId, $source, $questionType, $questionCategory, $babId, $subBabId])
        : route('lms.question_bank_management_detail.view.no_school_partner', [$role, $kurikulumId, $kelasId, $mapelId, $source, $questionType, $questionCategory, $babId, $subBabId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'administrator' || Auth::user()->role === 'Admin Sekolah')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
        <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">
            <!-- alert success -->
            <div id="alert-success-bank-soal-edit-question"></div>

            @include('Features.Lms.Components.QuestionBankManagement.LmsQuestionBankManagementEdit')
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/Lms/Components/QuestionBankManagement/FormActionQuestionBankEdit.js') }}"></script> <!--- form action question bank edit ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/PusherListener/Lms/QuestionBank/EditQuestionPgListener.js') }}"></script> <!--- pusher listener pada saat edit bank soal ---->