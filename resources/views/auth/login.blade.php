<x-script></x-script>

@if(session('failed'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: @json(session('failed'))
    });
</script>
@endif

<div class="w-full min-h-screen bg-cover bg-[#0071BC] flex items-center justify-center p-4 sm:p-6" style="background-image: url('{{ asset('assets/images/components/background-bc.svg') }}')">
    <div class="w-full max-w-[420px] flex flex-col items-center justify-center my-auto py-2 sm:py-4 mx-auto" style="max-width: 420px;">
        <!-- Logo -->
        <div class="flex justify-center">
            <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="Belajar Cerdas" 
                class="login-logo h-12 sm:h-14 md:h-16 max-h-[64px] mb-3 sm:mb-4 lg:mb-5 object-contain">
        </div>

        <div class="login-box w-full max-w-[420px] bg-white rounded-2xl shadow-xl p-5 sm:p-6 md:p-7.5" style="max-width: 420px; width: 100%;">
            <!-- Header -->
            <div class="flex flex-col items-center mb-4 sm:mb-5">
                <h1 class="text-base sm:text-lg font-bold opacity-70 text-gray-800 text-center">
                    Selamat Datang di LMS Belajar Cerdas!
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 text-center mt-0.5 sm:mt-1">
                    Silahkan masuk menggunakan akun Anda
                </p>
            </div>

            <!-- Form -->
            <form id="form-login" class="space-y-3 sm:space-y-3.5">
                <!-- alert -->

                <div id="container-error-attempt-login" class="bg-red-100 border-l-4 border-red-500 text-red-700 font-bold opacity-70 p-3 h-max mb-2 rounded-r-lg hidden text-xs sm:text-sm">
                    <div class="flex justify-between items-center">
                        <span id="text-error-attempt-login"></span>
                        <i id="xmark-icon" class="fa-solid fa-circle-xmark text-lg cursor-pointer"></i>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs sm:text-sm font-bold opacity-70 text-gray-700 mb-1">
                        Email
                        <sup class="text-red-500 pl-0.5">&#42;</sup>
                    </label>
                    <input type="email" name="email" placeholder="nama@email.com" class="w-full h-10 sm:h-11 px-3.5 sm:px-4 rounded-xl border border-gray-300 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 outline-none text-xs sm:text-sm transition-all duration-200"
                    autocomplete="OFF">
                    <span id="error-email" class="error-text text-red-500 text-xs mt-1 font-bold block"></span>
                </div>

                <div>
                    <label for="password" class="block text-xs sm:text-sm font-bold opacity-70 text-gray-700 mb-1">
                        Password
                        <sup class="text-red-500 pl-0.5">&#42;</sup>
                    </label>
                    <div class="relative w-full flex items-center" style="position: relative; width: 100%;">
                        <input id="passwordInput" type="password" name="password" placeholder="Masukkan password" class="w-full h-10 sm:h-11 px-3.5 sm:px-4 pr-11 rounded-xl border border-gray-300 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 outline-none text-xs sm:text-sm transition-all duration-200" 
                            style="padding-right: 2.75rem;"
                            autocomplete="OFF">
                        <button type="button" onclick="togglePassword('passwordInput', this)"
                            class="toggle-password-btn text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                            style="position: absolute; right: 12px; top: 0; bottom: 0; margin-top: auto; margin-bottom: auto; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border: none; background: transparent; padding: 0; cursor: pointer; z-index: 10; transform: none; translate: none;">
                            <i class="fa-solid fa-eye-slash text-sm"></i>
                        </button>
                    </div>
                    <span id="error-password" class="error-text text-red-500 text-xs mt-1 font-bold block"></span>
                </div>

                <!-- Button -->
                <button id="submit-button" type="submit" class="w-full mt-3 sm:mt-4 h-10 sm:h-11 rounded-xl bg-[#0071BC] hover:bg-blue-600 text-white font-semibold shadow-sm hover:shadow transition-all text-xs sm:text-sm flex items-center justify-center cursor-pointer disabled:cursor-default">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/auth/form-action-login.js') }}"></script> <!--- form action login ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/show-password-input.js') }}"></script> <!--- show password input ---->