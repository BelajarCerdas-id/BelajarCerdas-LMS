@include('components/sidebar-beranda', ['headerSideNav' => 'Topik Management'])

@if (Auth::user()->role === 'Administrator')

<div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">

    <div class="my-15 mx-7.5">

        <!-- ALERT -->
        <div id="alert-success-insert-topik"></div>
        <div id="alert-success-edit-topik"></div>
        <div id="alert-success-delete-topik"></div>

        <main>
            <section class="bg-white shadow-xl rounded-2xl border border-gray-100 p-6">

                <!-- HEADER -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">

                    <div>
                        <h1 class="text-lg font-semibold text-gray-800">Topik Management</h1>
                        <p class="text-sm text-gray-500">Kelola semua topik pembelajaran</p>
                    </div>

                    <!-- FILTER + SEARCH -->
                    <div class="flex flex-col lg:flex-row gap-3 w-full lg:w-2/3">

                        <!-- FILTER MAPEL -->
                        <form method="GET" class="w-full lg:w-1/3">
                            <select name="mapel_id"
                                onchange="this.form.submit()"
                                class="w-full h-11 border border-gray-300 rounded-xl px-3 text-sm focus:ring-2 focus:ring-blue-200">

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

                            <!-- SEARCH -->
                            <input type="text"
                                id="search-topik"
                                placeholder="Search topik..."
                                class="flex-1 h-11 border border-gray-200 rounded-xl px-4 text-sm 
                                    focus:ring-2 focus:ring-blue-200 outline-none">

                            <!-- RESET BUTTON -->
                            <button
                                type="button"
                                onclick="resetFilterTopik()"
                                class="h-11 px-5 rounded-xl border border-red-200 text-red-600 text-sm 
                                    hover:bg-red-50 hover:border-red-300 transition flex items-center justify-center gap-2">

                                🔄 <span>Reset</span>
                            </button>

                            <button
                                type="button"
                                onclick="document.getElementById('modal_add_topik').showModal()"
                                class="h-11 px-5 bg-blue-500 hover:bg-yellow-600 text-white rounded-xl">

                                ➕ Tambah Topik
                            </button>


                            <dialog id="modal_add_topik" class="modal">

                                <div class="modal-box max-w-3xl">

                                    <form action="{{ route('library.topik.store') }}" method="POST">

                                        @csrf

                                        <h3 class="font-bold text-xl mb-5">
                                            ➕ Tambah Topik Materi
                                        </h3>

                                        <div>
                                            <label class="font-semibold block mb-1">
                                                Mata Pelajaran
                                            </label>

                                            <select
                                                name="mapel_id"
                                                required
                                                class="select select-bordered w-full">

                                                <option value="">Pilih Mapel</option>

                                                @foreach($mapels as $mapel)
                                                    <option value="{{ $mapel->id }}">
                                                        {{ $mapel->mata_pelajaran }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div id="topikContainer" class="mt-4">

                                            <div class="grid grid-cols-12 gap-2 topik-row">

                                                <input
                                                    type="text"
                                                    name="topik[0][nama_topik]"
                                                    placeholder="Nama Topik"
                                                    required
                                                    class="input input-bordered col-span-5">

                                                <input
                                                    type="text"
                                                    name="topik[0][deskripsi]"
                                                    placeholder="Deskripsi Topik"
                                                    class="input input-bordered col-span-5">

                                                <button
                                                    type="button"
                                                    onclick="addTopikRow()"
                                                    class="btn btn-success col-span-2">
                                                    +
                                                </button>

                                            </div>

                                        </div>

                                        <div class="flex justify-end gap-3 mt-5">

                                            <button
                                                type="button"
                                                onclick="modal_add_topik.close()"
                                                class="btn">

                                                Batal

                                            </button>

                                            <button
                                                type="submit"
                                                class="btn btn-primary">

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

                            <button class="px-3 py-1 text-xs rounded-lg bg-red-600 hover:bg-red-700 text-white shadow-sm transition font-medium">
                                Delete
                            </button>

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

    <div class="modal-box max-w-xl">

        <h3 class="font-bold text-xl mb-5">
            ✏️ Edit Topik
        </h3>

        <form id="formEditTopik" method="POST">

            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="font-semibold block mb-1">
                    Mata Pelajaran
                </label>

                <select 
                    name="mapel_id"
                    id="edit_mapel_id"
                    required
                    class="select select-bordered w-full">

                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}">
                            {{ $mapel->mata_pelajaran }}
                        </option>
                    @endforeach

                </select>

            </div>


            <div class="mb-4">

                <label class="font-semibold block mb-1">
                    Nama Topik
                </label>

                <input 
                    type="text"
                    name="nama_topik"
                    id="edit_nama_topik"
                    required
                    class="input input-bordered w-full">

            </div>


            <div class="mb-4">

                <label class="font-semibold block mb-1">
                    Deskripsi
                </label>

                <textarea 
                    name="deskripsi"
                    id="edit_deskripsi"
                    class="textarea textarea-bordered w-full"></textarea>

            </div>


            <div class="flex justify-end gap-3">

                <button 
                    type="button"
                    onclick="modal_edit_topik.close()"
                    class="btn">

                    Batal

                </button>


                <button 
                    type="submit"
                    class="btn btn-primary">

                    Update Topik

                </button>

            </div>


        </form>

    </div>

</dialog>
<!-- JS -->
<script src="{{ asset('assets/js/topik-management/topik-list.js') }}"></script>
<script src="{{ asset('assets/js/topik-management/topik-action.js') }}"></script>

<script>
    function resetFilterTopik() {

    // reset select mapel
    const mapel = document.getElementById('filter-mapel-topik');
    if (mapel) mapel.value = '';

    // reset search
    const search = document.getElementById('search-topik');
    if (search) search.value = '';

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
                class="input input-bordered col-span-5">

            <input
                type="text"
                name="topik[${topikIndex}][deskripsi]"
                placeholder="Deskripsi Topik"
                class="input input-bordered col-span-5">

            <button
                type="button"
                onclick="this.closest('.topik-row').remove()"
                class="btn btn-error col-span-2">

                -

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
}function openEditTopik(id, nama, deskripsi, mapel_id)
{
    document.getElementById('edit_nama_topik').value = nama;
    document.getElementById('edit_deskripsi').value = deskripsi ?? '';
    document.getElementById('edit_mapel_id').value = mapel_id;

    document.getElementById('formEditTopik').action =
        `/administrator/library/topik/update/${id}`;

    document.getElementById('modal_edit_topik').showModal();
}

</script>