@include('components/sidebar-beranda', ['headerSideNav' => 'Topik Management'])

@if (Auth::user()->role === 'Administrator')

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">

    <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">

        <!-- ALERT -->
        <div id="alert-success-insert-topik"></div>
        <div id="alert-success-edit-topik"></div>
        <div id="alert-success-delete-topik"></div>

        <main>
            <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-6">

                <!-- HEADER -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">

                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-gray-800">Topik Management</h1>
                        <p class="text-sm text-gray-500 font-medium">Kelola semua topik pembelajaran</p>
                    </div>

                    <!-- FILTER + SEARCH -->
                    <div class="flex flex-col lg:flex-row gap-3 w-full lg:w-2/3">

                        <!-- FILTER MAPEL -->
                        <form method="GET" class="w-full lg:w-1/3">
                            <select id="filter-mapel-topik" name="mapel_id"
                                onchange="this.form.submit()"
                                class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">

                                <option value="">All Mapel</option>

                                @foreach($mapels->unique('id') as $mapel)
                                <option value="{{ $mapel->id }}"
                                    {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->mata_pelajaran }}
                                </option>
                            @endforeach

                            </select>
                        </form>

                        <div class="flex flex-col lg:flex-row gap-3 lg:items-center">

                            <!-- RESET BUTTON -->
                            <button
                                type="button"
                                onclick="resetFilterTopik()"
                                class="h-11 px-5 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 hover:border-gray-400 transition flex items-center justify-center gap-2 cursor-pointer">

                                <i class="fa-solid fa-rotate-right text-gray-500"></i>
                                <span>Reset</span>
                            </button>

                            <button
                                type="button"
                                onclick="document.getElementById('modal_add_topik').showModal()"
                                class="h-11 px-5 bg-[#0071BC] hover:bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition flex items-center justify-center gap-2 cursor-pointer">

                                <i class="fa-solid fa-plus"></i>
                                <span>Tambah Topik</span>
                            </button>


                            <dialog id="modal_add_topik" class="modal">

                                <div class="modal-box bg-white max-w-3xl rounded-2xl p-6 shadow-xl">

                                    <form action="{{ route('library.topik.store') }}" method="POST">

                                        @csrf

                                        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">
                                            Tambah Topik Materi
                                        </h3>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                                Mata Pelajaran
                                            </label>

                                            <select
                                                name="mapel_id"
                                                required
                                                class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">

                                                <option value="">Pilih Mapel</option>

                                                @foreach($mapels as $mapel)
                                                    <option value="{{ $mapel->id }}">
                                                        {{ $mapel->mata_pelajaran }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div id="topikContainer" class="mt-4 space-y-2">

                                            <div class="grid grid-cols-12 gap-2 topik-row">

                                                <input
                                                    type="text"
                                                    name="topik[0][nama_topik]"
                                                    placeholder="Nama Topik"
                                                    required
                                                    class="h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all col-span-5">

                                                <input
                                                    type="text"
                                                    name="topik[0][deskripsi]"
                                                    placeholder="Deskripsi Topik"
                                                    class="h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all col-span-5">

                                                <button
                                                    type="button"
                                                    onclick="addTopikRow()"
                                                    class="h-11 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition cursor-pointer col-span-2 flex items-center justify-center">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>

                                            </div>

                                        </div>

                                        <div class="flex justify-end gap-3 mt-6">

                                            <button
                                                type="button"
                                                onclick="modal_add_topik.close()"
                                                class="h-11 px-5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition cursor-pointer">

                                                Batal

                                            </button>

                                            <button
                                                type="submit"
                                                class="h-11 px-6 bg-[#0071BC] hover:bg-blue-600 text-white font-semibold rounded-xl text-sm shadow-sm hover:shadow transition cursor-pointer">

                                                Simpan Topik

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </dialog>

                        </div>

                    </div>

                </div>

               <div class="overflow-x-auto rounded-xl border border-gray-300 shadow-sm">

    <table class="min-w-full text-sm">

        <!-- HEADER -->
        <thead class="bg-gray-100 text-gray-800 text-xs uppercase tracking-wider">
            <tr>
                <th class="px-4 py-3 border-b border-gray-300 text-left font-semibold">
                    Topik
                </th>
                <th class="px-4 py-3 border-b border-gray-300 text-left font-semibold">
                    Deskripsi
                </th>
                <th class="px-4 py-3 border-b border-gray-300 text-left font-semibold">
                    Mapel
                </th>

                <th class="px-4 py-3 border-b border-gray-300 text-center font-semibold">
                    Action
                </th>
            </tr>
        </thead>

        <!-- BODY -->
        <tbody class="bg-white divide-y divide-gray-300">

            @forelse($topiks as $topik)
                <tr class="hover:bg-gray-100 transition">

                    <td class="px-4 py-3 border-r border-gray-200 font-semibold text-gray-900">
                        {{ $topik->nama_topik }}
                    </td>

                    <td class="px-4 py-3 border-r border-gray-200 text-gray-700">
                        {{ $topik->deskripsi ?? '-' }}
                    </td>

                    <td class="px-4 py-3 border-r border-gray-200">
                        <span class="inline-flex px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                            {{ $topik->mapel->mata_pelajaran ?? '-' }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">

                            <button 
                                onclick='openEditTopik(@json($topik->id), 
                                @json($topik->nama_topik), 
                                @json($topik->deskripsi), 
                                @json($topik->mapel_id))'
                                class="px-3 py-1 text-xs rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white shadow-sm transition font-medium">

                                Edit

                            </button>

                            <form id="delete-topik-{{ $topik->id }}"
                                action="{{ route('library.topik.delete', $topik->id) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')

                                <button type="button"
                                    onclick="confirmDeleteTopik({{ $topik->id }})"
                                    class="px-3 py-1 text-xs rounded-lg bg-red-600 hover:bg-red-700 text-white shadow-sm transition font-medium">
                                    Delete
                                </button>
                            </form>
                                                        
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-12 text-gray-600 font-medium">
                        Tidak ada data topik
                    </td>
                </tr>
            @endforelse

        </tbody>

    </table>

</div>

                <!-- PAGINATION -->
                <div class="flex justify-center mt-6" id="pagination-topik"></div>

            </section>
        </main>

    </div>
    

</div>

@else
    <p>You do not have access to this page.</p>
@endif

<dialog id="modal_edit_topik" class="modal">

    <div class="modal-box bg-white max-w-xl rounded-2xl p-6 shadow-xl">

        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">
            Edit Topik
        </h3>

        <form id="formEditTopik" method="POST" class="space-y-4">

            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Mata Pelajaran
                </label>

                <select 
                    name="mapel_id"
                    id="edit_mapel_id"
                    required
                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">

                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}">
                            {{ $mapel->mata_pelajaran }}
                        </option>
                    @endforeach

                </select>

            </div>


            <div>

                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Topik
                </label>

                <input 
                    type="text"
                    name="nama_topik"
                    id="edit_nama_topik"
                    required
                    class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all">

            </div>


            <div>

                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Deskripsi
                </label>

                <textarea 
                    name="deskripsi"
                    id="edit_deskripsi"
                    rows="3"
                    class="w-full bg-white border border-gray-300 rounded-xl p-3 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all"></textarea>

            </div>


            <div class="flex justify-end gap-3 pt-2">

                <button 
                    type="button"
                    onclick="modal_edit_topik.close()"
                    class="h-11 px-5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition cursor-pointer">

                    Batal

                </button>


                <button 
                    type="submit"
                    class="h-11 px-6 bg-[#0071BC] hover:bg-blue-600 text-white font-semibold rounded-xl text-sm shadow-sm hover:shadow transition cursor-pointer">

                    Update Topik

                </button>

            </div>


        </form>

    </div>

</dialog>
<!-- JS -->
<script src="{{ asset('assets/js/topik-management/topik-list.js') }}"></script>
<script src="{{ asset('assets/js/topik-management/topik-action.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function resetFilterTopik() {

    // reset select mapel
    const mapel = document.getElementById('filter-mapel-topik');
    if (mapel) mapel.value = '';

    // reload halaman tanpa query
    window.location.href = window.location.pathname;
}
</script>

<script>

let topikIndex = 1;

function addTopikRow() {

    const row = `
        <div class="grid grid-cols-12 gap-2 mt-2 topik-row">

            <input
                type="text"
                name="topik[${topikIndex}][nama_topik]"
                placeholder="Nama Topik"
                class="h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all col-span-5">

            <input
                type="text"
                name="topik[${topikIndex}][deskripsi]"
                placeholder="Deskripsi Topik"
                class="h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 transition-all col-span-5">

            <button
                type="button"
                onclick="this.closest('.topik-row').remove()"
                class="h-11 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm transition cursor-pointer col-span-2 flex items-center justify-center">
                <i class="fa-solid fa-minus"></i>
            </button>

        </div>
    `;

    document
        .getElementById('topikContainer')
        .insertAdjacentHTML('beforeend', row);

    topikIndex++;
}

function openEditTopik(id, nama, deskripsi, mapel_id)
{
    document.getElementById('edit_nama_topik').value = nama;
    document.getElementById('edit_deskripsi').value = deskripsi ?? '';
    document.getElementById('edit_mapel_id').value = mapel_id;

    document.getElementById('formEditTopik').action =
        `/administrator/library/topik/update/${id}`;

    document.getElementById('modal_edit_topik').showModal();
}


function confirmDeleteTopik(id) {
    Swal.fire({
        title: 'Hapus Topik?',
        text: 'Topik yang masih digunakan oleh materi tidak dapat dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-topik-' + id).submit();
        }
    });
}
</script>

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '{{ session("success") }}'
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Tidak Bisa Menghapus',
    text: '{{ session("error") }}'
});
</script>
@endif