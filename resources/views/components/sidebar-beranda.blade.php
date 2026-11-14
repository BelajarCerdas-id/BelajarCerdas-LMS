<x-script></x-script>

@php
    $user = Auth::user();
    $role = $user?->role;
    $roleLower = strtolower($role ?? '');

    $rolePartial = match (true) {
        $role === 'Siswa' => 'siswa',
        in_array($roleLower, ['administrator', 'admin'], true) => 'administrator',
        $role === 'Finance' => 'finance',
        $role === 'Guru' => 'guru',
        $role === 'Admin Sekolah' => 'admin-sekolah',
        in_array($role, ['Kepala Sekolah', 'Wakil Kepala Sekolah'], true) => 'kepala-sekolah',
        $role === 'Wakil Kesiswaan' => 'wakil-kesiswaan',
        $role === 'Orang Tua' => 'orang-tua',
        $role === 'Yayasan' => 'yayasan',
        default => null,
    };
@endphp

@if ($rolePartial)
    @include("components.sidebar.roles.{$rolePartial}", [
        'headerSideNav' => $headerSideNav ?? '',
        'backButton' => $backButton ?? null,
        'linkBackButton' => $linkBackButton ?? null,
    ])
@else
    @include('components.sidebar.access-denied')
@endif

<script src="{{ asset('assets/js/components/sidebar-administrator.js') }}"></script> 
<script src="{{ asset('assets/js/components/sidebar-student.js') }}"></script> 
<script src="{{ asset('assets/js/components/navbar-button-profile.js') }}"></script>
<script src="{{ asset('assets/js/components/form-submit-school-logo.js') }}"></script>
<script src="{{ asset('assets/js/components/sidebar-resize.js') }}?v={{ file_exists(public_path('assets/js/components/sidebar-resize.js')) ? filemtime(public_path('assets/js/components/sidebar-resize.js')) : time() }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const dropdown = document.querySelector(".library-dropdown");
    const submenu = document.getElementById("librarySubmenu");

    if (dropdown && submenu) {
        dropdown.addEventListener("click", function () {
            submenu.classList.toggle("hidden");
        });
    }
});
</script>

<script>
function openLibraryMenu() {
    localStorage.setItem("openLibrary", "true");
}

document.addEventListener("DOMContentLoaded", function () {
    const openLibrary = localStorage.getItem("openLibrary");

    if (openLibrary === "true") {
        const el = document.getElementById("libraryDropdown");
        if (el) el.classList.remove("hidden");
    }
});
</script>