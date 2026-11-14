{{-- ========================================================================
     DAFTAR GURU (AcademicDocumentController::namaGuru)
     Navigasi antar pemilik perangkat pembelajaran; memilih guru lain
     masuk sebagai Viewer.
     Data: $role, $schoolName, $schoolId, $teachers, $currentTeacherId, $currentRole
     ======================================================================== --}}
@extends('layouts.app')

@section('title', 'Daftar Guru')

@section('content')
<div class="p-6 max-w-5xl mx-auto">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-800">Daftar Guru</h1>
        <p class="text-sm text-gray-500">Pilih guru untuk melihat dokumen pembelajarannya sebagai viewer.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

        @forelse($teachers as $teacher)
            <div class="bg-white rounded-2xl border border-gray-200 p-5 flex flex-col gap-3
                        {{ $teacher['id'] === $currentTeacherId ? 'ring-2 ring-[#0071BC]/40' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="h-11 w-11 rounded-full bg-blue-50 text-[#0071BC] flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(substr($teacher['name'], 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 text-sm truncate">
                            {{ $teacher['name'] }}
                            @if($teacher['id'] === $currentTeacherId)
                                <span class="ml-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2 py-0.5">Anda</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">{{ $teacher['role'] }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    @forelse($teacher['mapels'] as $mapel)
                        <span class="text-[11px] font-medium text-[#0071BC] bg-blue-50 border border-blue-100 rounded-full px-2.5 py-1">
                            {{ $mapel }}
                        </span>
                    @empty
                        <span class="text-[11px] text-gray-400">Belum ada mapel</span>
                    @endforelse
                </div>

                @if($teacher['id'] !== $currentTeacherId)
                    <a href="#"
                       class="mt-auto h-10 px-4 inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-600 hover:text-[#0071BC] hover:border-blue-200 transition">
                        <i class="fa-solid fa-eye text-xs"></i>
                        Lihat Dokumen
                    </a>
                @endif
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-gray-200 p-10 text-center">
                <i class="fa-solid fa-user-slash text-3xl text-gray-300 mb-3"></i>
                <p class="text-sm text-gray-400">Belum ada guru dengan mapel aktif di sekolah ini.</p>
            </div>
        @endforelse

    </div>

</div>
@endsection
