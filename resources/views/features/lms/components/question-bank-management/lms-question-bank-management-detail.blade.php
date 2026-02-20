<main>
    <!---- list bank soal ---->
    <section>

        <h3 class="font-bold opacity-70 text-xl">Daftar Soal</h3>

        <!--- daftar list soal --->
        <div id="container-bank-soal-detail" data-role="{{ Auth::user()->role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-source="{{ $source }}"
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