
@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda'])

@if (Auth::user()->role === 'Admin Sekolah')

    @php
        /*
        |--------------------------------------------------------------------------
        | DATA SEKOLAH
        |--------------------------------------------------------------------------
        */

        $schoolPartner = Auth::user()->SchoolStaffProfile->SchoolPartner;

        $schoolRole = Auth::user()->role;
        $schoolName = $schoolPartner->nama_sekolah;
        $schoolId = $schoolPartner->id;


        /*
        |--------------------------------------------------------------------------
        | TOTAL GURU
        |--------------------------------------------------------------------------
        */

        $jumlahGuru = 0;

        try {

            $jumlahGuru = \App\Models\UserAccount::query()
                ->where('school_partner_id', $schoolPartner->id)
                ->whereRaw('LOWER(role) = ?', ['guru'])
                ->count();

        } catch (\Throwable $e) {

            $jumlahGuru = 0;

        }


        /*
        |--------------------------------------------------------------------------
        | URL ACADEMIC DRIVE
        |--------------------------------------------------------------------------
        */

        $academicDriveUrl = url(
            '/lms/' .
            rawurlencode(Auth::user()->role) . '/' .
            rawurlencode($schoolPartner->nama_sekolah) . '/' .
            $schoolPartner->id .
            '/registrasi-guru/academic-drive'
        );

    @endphp



    {{-- ============================================================= --}}
    {{-- CONTAINER UTAMA --}}
    {{-- ============================================================= --}}

    <div
        class="relative left-0 md:left-62.5
               w-full md:w-[calc(100%-250px)]
               transition-all duration-500 ease-in-out
               z-20"
    >

        <div class="my-15 mx-7.5">

            <main>


                {{-- ================================================= --}}
                {{-- SATU BLOCK DASHBOARD --}}
                {{-- ================================================= --}}

                <section
                    id="dashboardPerangkatGuru"
                    class="group cursor-pointer
                           bg-white
                           rounded-3xl
                           shadow-sm
                           border border-gray-200
                           p-7
                           transition-all duration-300
                           hover:shadow-lg
                           hover:border-blue-300
                           hover:-translate-y-0.5"
                    onclick="openDashboardPerangkatGuru()"
                >


                    {{-- ============================================= --}}
                    {{-- HEADER BLOCK --}}
                    {{-- ============================================= --}}

                    <div
                        class="flex flex-col
                               lg:flex-row
                               lg:items-center
                               lg:justify-between
                               gap-6"
                    >


                        {{-- ========================================= --}}
                        {{-- BAGIAN KIRI --}}
                        {{-- ========================================= --}}

                        <div class="flex items-center gap-5">


                            {{-- ICON --}}
                            <div
                                class="w-16 h-16
                                       rounded-2xl
                                       bg-blue-100
                                       text-blue-600
                                       flex items-center justify-center
                                       shrink-0
                                       group-hover:bg-blue-600
                                       group-hover:text-white
                                       transition duration-300"
                            >
                                <i class="fa-solid fa-book-open text-2xl"></i>
                            </div>


                            {{-- JUDUL --}}
                            <div>

                                <div class="flex items-center gap-3">

                                    <h2
                                        class="text-xl md:text-2xl
                                               font-bold
                                               text-gray-800
                                               group-hover:text-blue-600
                                               transition"
                                    >
                                        Perangkat Pembelajaran Guru
                                    </h2>

                                    <span
                                        class="hidden sm:inline-flex
                                               items-center
                                               px-3 py-1
                                               rounded-full
                                               bg-blue-50
                                               text-blue-600
                                               text-xs
                                               font-semibold"
                                    >
                                        Dashboard
                                    </span>

                                </div>


                                <p class="text-sm text-gray-500 mt-1">
                                    Pengelolaan perangkat pembelajaran sekolah
                                </p>


                                <p class="text-xs text-gray-400 mt-3">
                                    Klik untuk membuka dashboard perangkat pembelajaran
                                </p>

                            </div>

                        </div>



                        {{-- ========================================= --}}
                        {{-- BAGIAN KANAN --}}
                        {{-- ========================================= --}}

                        <div class="flex items-center gap-4">


                            {{-- TOTAL GURU --}}
                            <div
                                class="bg-blue-50
                                       border border-blue-100
                                       rounded-2xl
                                       px-6 py-4
                                       min-w-[150px]"
                            >

                                <p class="text-xs text-blue-500 font-medium">
                                    Total Guru
                                </p>

                                <p class="text-2xl font-bold text-blue-600 mt-1">
                                    {{ $jumlahGuru }}
                                </p>

                            </div>


                            {{-- BUTTON BUKA DASHBOARD --}}
                            <button
                                type="button"
                                onclick="event.stopPropagation(); openDashboardPerangkatGuru();"
                                class="inline-flex items-center gap-2
                                       rounded-2xl
                                       bg-blue-600
                                       px-5 py-4
                                       text-sm
                                       font-semibold
                                       text-white
                                       shadow-sm
                                       transition duration-300
                                       hover:bg-blue-700
                                       hover:shadow-md"
                            >

                                <i class="fa-solid fa-chart-pie"></i>

                                <span>
                                    Buka Dashboard
                                </span>

                                <i
                                    class="fa-solid fa-arrow-right
                                           text-xs
                                           transition-transform duration-300"
                                ></i>

                            </button>

                        </div>

                    </div>



                    {{-- ================================================= --}}
                    {{-- PEMBATAS --}}
                    {{-- ================================================= --}}

                    <div class="border-t border-gray-100 my-6"></div>



                    {{-- ================================================= --}}
                    {{-- INFORMASI MENU --}}
                    {{-- ================================================= --}}

                    <div class="flex flex-wrap items-center gap-3">


                        {{-- CP ATP --}}
                        <div
                            class="inline-flex items-center gap-2
                                   rounded-xl
                                   bg-gray-50
                                   px-4 py-2.5
                                   text-sm
                                   text-gray-600"
                        >

                            <i class="fa-solid fa-chart-line text-blue-500"></i>

                            <span>
                                Analisis CP hingga ATP
                            </span>

                        </div>


                        {{-- PROTA PROSEM --}}
                        <div
                            class="inline-flex items-center gap-2
                                   rounded-xl
                                   bg-gray-50
                                   px-4 py-2.5
                                   text-sm
                                   text-gray-600"
                        >

                            <i class="fa-solid fa-calendar-days text-blue-500"></i>

                            <span>
                                PROTA & PROSEM
                            </span>

                        </div>


                        {{-- RPPM --}}
                        <div
                            class="inline-flex items-center gap-2
                                   rounded-xl
                                   bg-gray-50
                                   px-4 py-2.5
                                   text-sm
                                   text-gray-600"
                        >

                            <i class="fa-solid fa-file-lines text-blue-500"></i>

                            <span>
                                RPPM
                            </span>

                        </div>


                        {{-- REFLEKSI --}}
                        <div
                            class="inline-flex items-center gap-2
                                   rounded-xl
                                   bg-gray-50
                                   px-4 py-2.5
                                   text-sm
                                   text-gray-600"
                        >

                            <i class="fa-solid fa-comments text-blue-500"></i>

                            <span>
                                Refleksi Guru
                            </span>

                        </div>


                        {{-- ACADEMIC DRIVE --}}
                        <div
                            class="inline-flex items-center gap-2
                                   rounded-xl
                                   bg-blue-50
                                   px-4 py-2.5
                                   text-sm
                                   text-blue-600
                                   font-medium"
                        >

                            <i class="fa-solid fa-box-archive"></i>

                            <span>
                                Academic Drive
                            </span>

                        </div>

                    </div>

                </section>

            </main>

        </div>

    </div>



    {{-- ============================================================= --}}
    {{-- MODAL DASHBOARD --}}
    {{-- ============================================================= --}}

    <div
        id="modalDashboardPerangkatGuru"
        class="fixed inset-0 z-[9999] hidden"
        aria-hidden="true"
    >


        {{-- ========================================================= --}}
        {{-- BACKDROP --}}
        {{-- ========================================================= --}}

        <div
            class="absolute inset-0
                   bg-black/50
                   backdrop-blur-sm"
            onclick="closeDashboardPerangkatGuru()"
        ></div>



        {{-- ========================================================= --}}
        {{-- WRAPPER MODAL --}}
        {{-- ========================================================= --}}

        <div
            class="relative
                   min-h-screen
                   flex items-center justify-center
                   p-4 sm:p-6"
        >


            {{-- ===================================================== --}}
            {{-- CONTENT MODAL --}}
            {{-- ===================================================== --}}

            <div
                id="dashboardModalContent"
                class="relative
                       w-full
                       max-w-6xl
                       max-h-[90vh]
                       overflow-y-auto
                       bg-white
                       rounded-3xl
                       shadow-2xl
                       border border-gray-200
                       transform
                       scale-95
                       opacity-0
                       transition-all duration-300"
            >


                {{-- ================================================= --}}
                {{-- HEADER MODAL --}}
                {{-- ================================================= --}}

                <div
                    class="sticky top-0
                           z-20
                           bg-white/95
                           backdrop-blur
                           border-b border-gray-200
                           px-6 py-5
                           flex items-center
                           justify-between"
                >


                    {{-- ============================================= --}}
                    {{-- TITLE --}}
                    {{-- ============================================= --}}

                    <div class="flex items-center gap-4">


                        {{-- ICON --}}
                        <div
                            class="w-12 h-12
                                   rounded-xl
                                   bg-blue-100
                                   text-blue-600
                                   flex items-center justify-center"
                        >
                            <i class="fa-solid fa-book-open text-xl"></i>
                        </div>


                        {{-- TEXT --}}
                        <div>

                            <h2
                                class="text-lg md:text-xl
                                       font-bold
                                       text-gray-800"
                            >
                                Perangkat Pembelajaran Guru
                            </h2>

                            <p class="text-sm text-gray-500">
                                Pilih perangkat pembelajaran yang ingin dikelola
                            </p>

                        </div>

                    </div>



                    {{-- ============================================= --}}
                    {{-- CLOSE BUTTON --}}
                    {{-- ============================================= --}}

                    <button
                        type="button"
                        onclick="closeDashboardPerangkatGuru()"
                        class="w-10 h-10
                               rounded-xl
                               bg-gray-100
                               text-gray-500
                               flex items-center justify-center
                               hover:bg-red-50
                               hover:text-red-500
                               transition"
                    >

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>



                {{-- ================================================= --}}
                {{-- BODY MODAL --}}
                {{-- ================================================= --}}

                <div class="p-6">


                    <div
                        class="grid
                               grid-cols-1
                               sm:grid-cols-2
                               lg:grid-cols-3
                               gap-5"
                    >


                        {{-- ================================================= --}}
                        {{-- 1. ANALISIS CP HINGGA ATP --}}
                        {{-- ================================================= --}}

                        <a
                            href="{{ route('lms.schoolAdmin.registrasiGuru.analisis', [
                                'role' => $schoolRole,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}"
                            class="group block"
                        >

                            <div
                                class="h-full
                                       bg-white
                                       border border-gray-200
                                       rounded-2xl
                                       p-5
                                       shadow-sm
                                       hover:shadow-lg
                                       hover:border-blue-300
                                       transition duration-300"
                            >

                                <div
                                    class="w-12 h-12
                                           rounded-xl
                                           bg-blue-100
                                           text-blue-600
                                           flex items-center justify-center
                                           mb-5
                                           group-hover:bg-blue-600
                                           group-hover:text-white
                                           transition"
                                >

                                    <i
                                        class="fa-solid fa-chart-line text-xl"
                                    ></i>

                                </div>


                                <h3
                                    class="text-base
                                           font-bold
                                           text-gray-800
                                           group-hover:text-blue-600
                                           transition"
                                >
                                    Analisis CP hingga ATP
                                </h3>


                                <p
                                    class="text-sm
                                           text-gray-500
                                           mt-2
                                           leading-relaxed"
                                >
                                    Analisis Capaian Pembelajaran hingga Alur
                                    Tujuan Pembelajaran.
                                </p>


                                <div
                                    class="flex items-center gap-2
                                           mt-5
                                           text-sm
                                           font-medium
                                           text-blue-600"
                                >

                                    <span>
                                        Lihat Detail
                                    </span>

                                    <i
                                        class="fa-solid fa-arrow-right
                                               text-xs
                                               group-hover:translate-x-1
                                               transition"
                                    ></i>

                                </div>

                            </div>

                        </a>



                        {{-- ================================================= --}}
                        {{-- 2. PROTA DAN PROSEM --}}
                        {{-- ================================================= --}}

                        <a
                            href="{{ route('lms.schoolAdmin.registrasiGuru.prota-prosem', [
                                'role' => $schoolRole,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}"
                            class="group block"
                        >

                            <div
                                class="h-full
                                       bg-white
                                       border border-gray-200
                                       rounded-2xl
                                       p-5
                                       shadow-sm
                                       hover:shadow-lg
                                       hover:border-blue-300
                                       transition duration-300"
                            >

                                <div
                                    class="w-12 h-12
                                           rounded-xl
                                           bg-blue-100
                                           text-blue-600
                                           flex items-center justify-center
                                           mb-5
                                           group-hover:bg-blue-600
                                           group-hover:text-white
                                           transition"
                                >

                                    <i
                                        class="fa-solid fa-calendar-days text-xl"
                                    ></i>

                                </div>


                                <h3
                                    class="text-base
                                           font-bold
                                           text-gray-800
                                           group-hover:text-blue-600
                                           transition"
                                >
                                    PROTA dan PROSEM
                                </h3>


                                <p
                                    class="text-sm
                                           text-gray-500
                                           mt-2
                                           leading-relaxed"
                                >
                                    Pengelolaan Program Tahunan dan Program
                                    Semester guru.
                                </p>


                                <div
                                    class="flex items-center gap-2
                                           mt-5
                                           text-sm
                                           font-medium
                                           text-blue-600"
                                >

                                    <span>
                                        Lihat Detail
                                    </span>

                                    <i
                                        class="fa-solid fa-arrow-right
                                               text-xs
                                               group-hover:translate-x-1
                                               transition"
                                    ></i>

                                </div>

                            </div>

                        </a>



                        {{-- ================================================= --}}
                        {{-- 3. RPPM --}}
                        {{-- ================================================= --}}

                        <a
                            href="{{ route('lms.schoolAdmin.registrasiGuru.rppm', [
                                'role' => $schoolRole,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}"
                            class="group block"
                        >

                            <div
                                class="h-full
                                       bg-white
                                       border border-gray-200
                                       rounded-2xl
                                       p-5
                                       shadow-sm
                                       hover:shadow-lg
                                       hover:border-blue-300
                                       transition duration-300"
                            >

                                <div
                                    class="w-12 h-12
                                           rounded-xl
                                           bg-blue-100
                                           text-blue-600
                                           flex items-center justify-center
                                           mb-5
                                           group-hover:bg-blue-600
                                           group-hover:text-white
                                           transition"
                                >

                                    <i
                                        class="fa-solid fa-file-lines text-xl"
                                    ></i>

                                </div>


                                <h3
                                    class="text-base
                                           font-bold
                                           text-gray-800
                                           group-hover:text-blue-600
                                           transition"
                                >
                                    Analisis RPPM
                                </h3>


                                <p
                                    class="text-sm
                                           text-gray-500
                                           mt-2
                                           leading-relaxed"
                                >
                                    Analisis dan pengelolaan perangkat RPPM
                                    guru.
                                </p>


                                <div
                                    class="flex items-center gap-2
                                           mt-5
                                           text-sm
                                           font-medium
                                           text-blue-600"
                                >

                                    <span>
                                        Lihat Detail
                                    </span>

                                    <i
                                        class="fa-solid fa-arrow-right
                                               text-xs
                                               group-hover:translate-x-1
                                               transition"
                                    ></i>

                                </div>

                            </div>

                        </a>



                        {{-- ================================================= --}}
                        {{-- 4. REFLEKSI GURU --}}
                        {{-- ================================================= --}}

                        <a
                            href="{{ route('lms.schoolAdmin.registrasiGuru.refleksiGuru', [
                                'role' => $schoolRole,
                                'schoolName' => $schoolName,
                                'schoolId' => $schoolId
                            ]) }}"
                            class="group block"
                        >

                            <div
                                class="h-full
                                       bg-white
                                       border border-gray-200
                                       rounded-2xl
                                       p-5
                                       shadow-sm
                                       hover:shadow-lg
                                       hover:border-blue-300
                                       transition duration-300"
                            >

                                <div
                                    class="w-12 h-12
                                           rounded-xl
                                           bg-blue-100
                                           text-blue-600
                                           flex items-center justify-center
                                           mb-5
                                           group-hover:bg-blue-600
                                           group-hover:text-white
                                           transition"
                                >

                                    <i
                                        class="fa-solid fa-comments text-xl"
                                    ></i>

                                </div>


                                <h3
                                    class="text-base
                                           font-bold
                                           text-gray-800
                                           group-hover:text-blue-600
                                           transition"
                                >
                                    Refleksi Guru
                                </h3>


                                <p
                                    class="text-sm
                                           text-gray-500
                                           mt-2
                                           leading-relaxed"
                                >
                                    Refleksi guru terhadap proses dan hasil
                                    pembelajaran.
                                </p>


                                <div
                                    class="flex items-center gap-2
                                           mt-5
                                           text-sm
                                           font-medium
                                           text-blue-600"
                                >

                                    <span>
                                        Lihat Detail
                                    </span>

                                    <i
                                        class="fa-solid fa-arrow-right
                                               text-xs
                                               group-hover:translate-x-1
                                               transition"
                                    ></i>

                                </div>

                            </div>

                        </a>



                        {{-- ================================================= --}}
                        {{-- 5. ACADEMIC DRIVE / ARSIP --}}
                        {{-- ================================================= --}}

                        <a
                            href="{{ $academicDriveUrl }}"
                            class="group block"
                        >

                            <div
                                class="h-full
                                       bg-white
                                       border border-blue-200
                                       rounded-2xl
                                       p-5
                                       shadow-sm
                                       hover:shadow-lg
                                       hover:border-blue-400
                                       transition duration-300"
                            >

                                <div
                                    class="w-12 h-12
                                           rounded-xl
                                           bg-blue-100
                                           text-blue-600
                                           flex items-center justify-center
                                           mb-5
                                           group-hover:bg-blue-600
                                           group-hover:text-white
                                           transition"
                                >

                                    <i
                                        class="fa-solid fa-box-archive text-xl"
                                    ></i>

                                </div>


                                <h3
                                    class="text-base
                                           font-bold
                                           text-gray-800
                                           group-hover:text-blue-600
                                           transition"
                                >
                                    Academic Drive
                                </h3>


                                <p
                                    class="text-sm
                                           text-gray-500
                                           mt-2
                                           leading-relaxed"
                                >
                                    Akses arsip dan penyimpanan perangkat
                                    pembelajaran guru.
                                </p>


                                <div
                                    class="flex items-center gap-2
                                           mt-5
                                           text-sm
                                           font-medium
                                           text-blue-600"
                                >

                                    <span>
                                        Masuk ke Arsip
                                    </span>

                                    <i
                                        class="fa-solid fa-arrow-right
                                               text-xs
                                               group-hover:translate-x-1
                                               transition"
                                    ></i>

                                </div>

                            </div>

                        </a>


                    </div>

                </div>



                {{-- ================================================= --}}
                {{-- FOOTER MODAL --}}
                {{-- ================================================= --}}

                <div
                    class="border-t border-gray-200
                           px-6 py-4
                           flex justify-end"
                >

                    <button
                        type="button"
                        onclick="closeDashboardPerangkatGuru()"
                        class="px-5 py-2.5
                               rounded-xl
                               bg-gray-100
                               text-gray-600
                               text-sm
                               font-medium
                               hover:bg-gray-200
                               transition"
                    >
                        Tutup
                    </button>

                </div>

            </div>

        </div>

    </div>



    {{-- ============================================================= --}}
    {{-- JAVASCRIPT MODAL --}}
    {{-- ============================================================= --}}

    <script>

        function openDashboardPerangkatGuru() {

            const modal = document.getElementById(
                'modalDashboardPerangkatGuru'
            );

            const content = document.getElementById(
                'dashboardModalContent'
            );

            if (!modal || !content) {
                return;
            }


            // Tampilkan modal
            modal.classList.remove('hidden');

            modal.setAttribute('aria-hidden', 'false');


            // Lock scroll halaman
            document.body.classList.add('overflow-hidden');


            // Animasi masuk
            requestAnimationFrame(() => {

                content.classList.remove(
                    'scale-95',
                    'opacity-0'
                );

                content.classList.add(
                    'scale-100',
                    'opacity-100'
                );

            });

        }



        function closeDashboardPerangkatGuru() {

            const modal = document.getElementById(
                'modalDashboardPerangkatGuru'
            );

            const content = document.getElementById(
                'dashboardModalContent'
            );

            if (!modal || !content) {
                return;
            }


            // Animasi keluar
            content.classList.remove(
                'scale-100',
                'opacity-100'
            );

            content.classList.add(
                'scale-95',
                'opacity-0'
            );


            setTimeout(() => {

                modal.classList.add('hidden');

                modal.setAttribute(
                    'aria-hidden',
                    'true'
                );

                document.body.classList.remove(
                    'overflow-hidden'
                );

            }, 200);

        }



        // =============================================================
        // ESCAPE UNTUK MENUTUP MODAL
        // =============================================================

        document.addEventListener(
            'keydown',
            function (event) {

                if (event.key !== 'Escape') {
                    return;
                }


                const modal =
                    document.getElementById(
                        'modalDashboardPerangkatGuru'
                    );


                if (
                    modal &&
                    !modal.classList.contains('hidden')
                ) {

                    closeDashboardPerangkatGuru();

                }

            }
        );

    </script>


@else


    {{-- ============================================================= --}}
    {{-- AKSES DITOLAK --}}
    {{-- ============================================================= --}}

    <div
        class="flex
               flex-col
               min-h-screen
               items-center
               justify-center"
    >

        <p
            class="text-lg
                   font-semibold
                   text-gray-800"
        >
            ALERT SEMENTARA
        </p>

        <p
            class="text-sm
                   text-gray-500
                   mt-2"
        >
            You do not have access to this page.
        </p>

    </div>

@endif
