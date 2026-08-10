@include('components/sidebar-beranda', [
    'headerSideNav' => 'Atur Ulang Sandi',
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
    'linkBackButton' => $schoolId ? route('profile-account-school-partner.view', [$role, $schoolName, $schoolId]) : route('profile-account-non-school-partner.view', [$role]),
])

@if (Auth::user())
    <div class="relative left-0 w-full {{ Auth::user()->role === 'Admin Sekolah' || Auth::user()->role === 'Administrator' ? 'md:left-62.5 md:w-[calc(100%-250px)]' 
        : 'md:left-72.5 md:w-[calc(100%-290px)]' }}  transition-all duration-500 ease-in-out z-20">

        <div class="my-15 mx-7.5">

            <!--- alert success --->
            <div id="alert-success-reset-password"></div>
            
            <main id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">
                <section>
                    <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-lg overflow-hidden">

                        <!-- Header -->
                        <div class="px-8 py-6 border-b border-gray-300 bg-linear-to-r from-blue-50 to-white">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Atur Ulang Kata Sandi
                            </h2>

                            <p class="text-gray-500 text-sm mt-2">
                                Untuk keamanan akun kamu, masukkan kata sandi lama kemudian buat kata sandi baru.
                            </p>
                        </div>

                        <!-- Body -->
                        <div class="p-8">

                            <form id="reset-password-form" class="space-y-7">

                                <!-- Password Lama -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Kata Sandi Lama
                                    </label>

                                    <div class="relative">
                                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                                        <input id="currentPasswordInput" type="password" name="current_password"
                                        
                                            placeholder="Masukkan kata sandi lama" class="w-full h-14 pl-12 pr-12 rounded-xl border border-gray-300 bg-gray-50 
                                            focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

                                        <button type="button" onclick="togglePassword('currentPasswordInput', this)" class="absolute right-4 top-1/2 
                                            -translate-y-1/2 text-gray-500 hover:text-blue-600">

                                            <i class="fa-solid fa-eye-slash cursor-pointer"></i>
                                        </button>
                                    </div>

                                    <span id="error-current_password"
                                        class="text-red-500 text-xs font-medium mt-2 block"></span>
                                </div>

                                <!-- Password Baru -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Kata Sandi Baru
                                    </label>

                                    <div class="relative">
                                        <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                                        <input id="newPasswordInput" type="password" name="new_password"
                                        
                                            placeholder="Masukkan kata sandi baru" class="w-full h-14 pl-12 pr-12 rounded-xl border border-gray-300 bg-gray-50 
                                            focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

                                        <button type="button" onclick="togglePassword('newPasswordInput', this)" class="absolute right-4 top-1/2 -translate-y-1/2 
                                            text-gray-500 hover:text-blue-600">

                                            <i class="fa-solid fa-eye-slash cursor-pointer"></i>
                                        </button>
                                    </div>

                                    <span id="error-new_password"
                                        class="text-red-500 text-xs font-medium mt-2 block"></span>
                                </div>

                                <!-- Konfirmasi -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Konfirmasi Kata Sandi
                                    </label>

                                    <div class="relative">
                                        <i class="fa-solid fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                                        <input id="confirmPasswordInput" type="password" name="new_password_confirmation" 
                                            placeholder="Konfirmasi kata sandi baru" class="w-full h-14 pl-12 pr-12 rounded-xl border border-gray-300 
                                            bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition">

                                        <button type="button" onclick="togglePassword('confirmPasswordInput', this)" 
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600">

                                            <i class="fa-solid fa-eye-slash cursor-pointer"></i>
                                        </button>
                                    </div>

                                    <span id="error-new_password_confirmation"
                                        class="text-red-500 text-xs font-medium mt-2 block"></span>
                                </div>

                                <!-- Button -->
                                <div class="flex justify-end pt-4">

                                    <button id="submit-button-reset-password" type="button" class="w-full sm:w-auto px-10 h-12 rounded-xl bg-[#4189e0] 
                                        hover:bg-blue-600 text-white font-semibold shadow-md hover:shadow-lg transition duration-200
                                        cursor-pointer disabled:cursor-default">
                                        Simpan Perubahan
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

<script src="{{ asset('assets/js/features/lms/profile-account/settings/reset-password.js') }}"></script> <!--- reset password ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/show-password-input.js') }}"></script> <!--- show password input ---->

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('#reset-password-form input').forEach(function (input) {

        input.addEventListener('input', function () {

            // hapus border merah
            input.classList.remove('border-red-400');

            // hapus text error
            const error = document.getElementById(`error-${input.name}`);
            if (error) {
                error.textContent = '';
            }
        });
    });
});
</script>