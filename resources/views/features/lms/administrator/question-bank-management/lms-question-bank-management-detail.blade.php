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
            <main>
                <!---- list bank soal ---->
                <section>

                    <h3 class="font-bold opacity-70 text-xl">Daftar Soal</h3>

                    <!--- daftar list soal --->
                    <div id="container-bank-soal-detail" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-source="{{ $source }}"
                        data-question-type="{{ $questionType }}" data-sub-bab-id="{{ $subBabId }}">
                        <div id="grid-list-soal" class="container-accordion mb-8">
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <div class="pagination-container-bank-soal-detail flex justify-center my-4 sm:my-0"></div>

                    <div id="emptyMessageBankSoalDetail" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada soal pada bank soal ini.
                        </span>
                    </div>
                </section>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/lms/administrator/question-bank-management/paginate-question-bank-management-detail.js') }}"></script> <!--- paginate lms question bank detail ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/lms/question-bank/review-question-pg-listener.js') }}"></script> <!--- pusher listener insert bank soal and edit soal in bankSoal detail ---->