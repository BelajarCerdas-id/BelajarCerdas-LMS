@include('components/sidebar-beranda', [
    'headerSideNav' => 'Form',
    'linkBackButton' => route('lms.office.tka-tryout-period-management.view', [$role]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- ALERT -->
            <div id="alert-success-create-tka-tryout-period"></div>

            <main id="container" data-role="{{ $role }}">

                <!-- HEADER -->
                <section class="mb-6">
                    <div class="relative overflow-hidden rounded-3xl bg-[linear-gradient(to_left,#0071BC_45%,#003456_100%)] p-5 shadow-xl lg:p-8">
                        <i class="fa-solid fa-calendar-plus absolute -right-6 -top-8 rotate-12 text-[120px] text-white/5 pointer-events-none lg:text-[180px]"></i>
                        <i class="fa-solid fa-clipboard-list absolute -bottom-10 -left-6 -rotate-12 text-[90px] text-white/5 pointer-events-none lg:text-[140px]"></i>

                        <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

                            <!-- LEFT -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 lg:gap-4">

                                    <!-- Icon -->
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/15 shadow-lg 
                                        backdrop-blur-sm lg:h-16 lg:w-16">
                                        <i class="fa-solid fa-calendar-plus text-xl text-white lg:text-3xl"></i>
                                    </div>

                                    <!-- Title -->
                                    <div class="inline-block">
                                        <h1 class="text-xl font-bold leading-tight text-white">
                                            Buat Periode Tryout TKA
                                        </h1>

                                        <div class="mt-2 h-1 w-full rounded-full bg-cyan-300"></div>
                                    </div>
                                </div>

                                <p class="mt-5 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base">
                                    Buat dan atur periode Tryout TKA yang akan digunakan sebagai jadwal utama untuk sekolah.
                                </p>
                            </div>

                            <!-- RIGHT -->
                            <div class="w-full lg:w-auto">
                                <a href="{{ route('lms.office.tka-tryout-period-management.view', [
                                    'role' => $role
                                ]) }}">
                                    <button
                                        type="button"
                                        class="btn w-full border-white bg-white text-[#005A9C] shadow-lg hover:border-slate-100 hover:bg-slate-100 lg:w-auto">
                                        <i class="fa-solid fa-arrow-left"></i>
                                        Kembali ke Daftar Periode
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FORM CREATE PERIODE TRYOUT TKA -->
                <section class="mb-6">
                    <div class="overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-sm">

                        <!-- FORM HEADER -->
                        <div class="border-b border-slate-200 bg-slate-50 px-5 py-5 sm:px-6">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                                    <i class="fa-solid fa-calendar-plus text-blue-600"></i>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-slate-800">
                                        Informasi Periode Tryout TKA
                                    </h2>

                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                        Tentukan periode pelaksanaan dan pengaturan dasar untuk periode Tryout TKA.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- FORM CONTENT -->
                        <div class="px-5 py-6 sm:px-6">

                            <!-- PERIOD TYPE INFO -->
                            <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 shrink-0">
                                        <i class="fa-solid fa-circle-info text-blue-600"></i>
                                    </div>

                                    <div>
                                        <p class="text-sm font-semibold text-blue-800">
                                            Periode Default
                                        </p>

                                        <p class="mt-1 text-xs leading-relaxed text-blue-700 sm:text-sm">
                                            Periode yang dibuat dari halaman ini akan menjadi periode default yang dapat digunakan oleh seluruh sekolah. 
                                            Sekolah dapat memiliki periode custom yang akan menggantikan periode default.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FORM -->
                            <form id="create-tka-tryout-period-form">

                                <!-- PERIODE -->
                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Periode
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Tentukan kapan periode Tryout TKA mulai dan berakhir.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
                                        
                                        <div class="w-full">
                                            <label for="academic-year" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tahun Ajaran
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <select id="academic-year" name="tahun_ajaran" class="w-full h-13 select select-bordered bg-white border border-gray-300 
                                                    rounded-lg py-4 text-sm shadow-sm outline-none transition duration-200 cursor-pointer">

                                                    <option value="" class="hidden">
                                                        Pilih Tahun Ajaran
                                                    </option>

                                                    @foreach ($academicYears as $academicYear)
                                                        <option value="{{ $academicYear }}">
                                                            {{ $academicYear }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <span id="error-edit-tahun_ajaran" class="text-xs font-semibold text-red-500"></span>
                                            </div>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Periode yang dibuat akan menjadi bagian dari tahun ajaran yang dipilih.
                                            </p>
                                        </div>

                                        <div class="w-full">
                                            <label for="start-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Mulai
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input 
                                                    type="text" id="start-date" name="start_date" 
                                                        class="w-full bg-white border border-gray-300 rounded-lg px-3 py-4 text-sm shadow-sm outline-none
                                                        transition duration-200" placeholder="Pilih Tanggal">
                                                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400 pointer-events-none">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-start_date" class="text-red-500 text-xs font-semibold"></span>

                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal dimulainya periode Tryout TKA.
                                            </p>
                                        </div>

                                        <div class="w-full">
                                            <label for="end-date" class="mb-2 block text-sm font-medium text-slate-700">
                                                Tanggal Selesai
                                                <span class="text-red-500">&#42;</span>
                                            </label>

                                            <div class="relative">
                                                <input 
                                                    type="text" id="end-date" name="end_date" 
                                                        class="w-full bg-white border border-gray-300 rounded-lg px-3 py-4 text-sm shadow-sm outline-none
                                                        transition duration-200" placeholder="Pilih Tanggal">
                                                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400 pointer-events-none">
                                                    <i class="fa-regular fa-calendar-days text-sm"></i>
                                                </span>
                                            </div>
                                            <span id="error-end_date" class="text-red-500 text-xs font-semibold"></span>
                                            
                                            <p class="mt-2 text-xs text-slate-400">
                                                Tanggal berakhirnya periode Tryout TKA.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-6 border-t border-slate-200"></div>

                                <!-- PENGATURAN REVIEW -->
                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-800">
                                            Pengaturan Review
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                            Tentukan apakah peserta dapat melihat pembahasan atau hasil review setelah mengerjakan Tryout.
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                                        <label
                                            for="is_review"
                                            class="flex cursor-pointer items-start gap-4">

                                            <input id="is-review" name="is_review" type="checkbox" class="mt-1 h-5 w-5 shrink-0 cursor-pointer rounded 
                                                border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500">

                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-700">
                                                    Aktifkan Review Tryout
                                                </p>

                                                <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-sm">
                                                    Peserta dapat melakukan review terhadap hasil pengerjaan sesuai dengan konfigurasi review yang tersedia.
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- FORM ACTION -->
                                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                                    <button type="button" id="button-save-tka-tryout-period" class="btn w-full rounded-xl border-[#0071BC] bg-[#0071BC] 
                                        text-white shadow-sm hover:border-[#005A9C] hover:bg-[#005A9C] sm:w-auto">

                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Simpan Periode
                                    </button>
                                </div>
                            </form>
                        </div>
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

<script src="{{ asset('assets/js/features/lms/administrator/tka-tryout/tka-tryout-period/tka-tryout-period-form.js') }}"></script> <!--- tryout tka period form ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->