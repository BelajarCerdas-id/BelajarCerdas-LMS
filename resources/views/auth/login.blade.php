<x-script></x-script>

<div class="w-full h-lvh bg-cover bg-[#0071BC]" style="background-image: url('{{ asset('assets/images/login/background-login.svg') }}')">
    <div class="min-h-lvh flex flex-col items-center justify-center px-4">
        <!-- Logo -->
        <div>
            <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-20 mb-6">
        </div>

        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
            <!-- Header -->
            <div class="flex flex-col items-center mb-6">
                <h1 class="flex flex-col gap-1 sm:flex-row text-lg font-bold opacity-70 text-gray-800 text-center">
                    <span>Selamat Datang di LMS</span> 
                    <span>Belajar Cerdas!</span>
                </h1>
                <p class="text-sm text-gray-500 text-center mt-1">
                    Silahkan masuk menggunakan akun Anda
                </p>
            </div>

            <!-- Form -->
            <form id="form-login" class="space-y-4">
                <!-- alert -->

                <div id="container-error-attempt-login" class="bg-red-100 border-l-4 border-red-500 text-red-700 font-bold opacity-70 p-4 h-max mx-6 hidden">
                    <div class="flex justify-between items-center">
                        <span id="text-error-attempt-login"></span>
                        <i id="xmark-icon" class="fa-solid fa-circle-xmark text-xl cursor-pointer"></i>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-bold opacity-70 text-gray-700 mb-1">
                        Email
                        <sup class="text-red-500">&#42;</sup>
                    </label>
                    <input type="email" name="email" placeholder="nama@email.com" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none text-sm"
                    autocomplete="OFF">
                    <span id="error-email" class="error-text text-red-500 text-xs mt-1 font-bold"></span>
                </div>

                <div class="flex flex-col gap-2 w-full relative">
                    <label for="password" class="block text-sm font-bold opacity-70 text-gray-700 mb-1">
                        Password
                        <sup class="text-red-500">&#42;</sup>
                    </label>
                    <input id="passwordInput" type="password" name="password" placeholder="Masukkan paswword" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none text-sm" 
                        maxlength="16" autocomplete="OFF">
                        <span id="error-password" class="error-text text-red-500 text-xs mt-1 font-bold"></span>
                    <button type="button" onclick="togglePassword('passwordInput', this)"
                        class="absolute right-3 top-14 transform -translate-y-1/2 text-gray-600 focus:outline-none">
                        <i class="fa-solid fa-eye-slash cursor-pointer"></i>
                    </button>
                </div>

                <!-- Button -->
                <button id="submit-button" type="button" class="w-full mt-4 py-3 rounded-xl bg-blue-500 text-white font-semibold transition cursor-pointer disabled:cursor-default">
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