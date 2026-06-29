    @include('components/sidebar-beranda', ['headerSideNav' => 'LMS Library'])

    @php
    use Illuminate\Support\Str;
    @endphp

    @if (Auth::user()->role === 'Administrator')

    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)]">
    <div class="my-6 mx-4">

    <main>

    <!-- ================= HEADER ================= -->

    <div class="flex justify-between items-center mb-6">

    <input
    type="search"
    placeholder="Cari buku..."
    class="border rounded px-3 py-2 w-48 sm:w-64 text-sm"
    />

    <button
    onclick="modal_pilih_tipe.showModal()"
    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
    Tambah Buku
    </button>

    </div>


    <!-- ================= TAB ================= -->

    <div class="flex gap-4 mb-4 border-b">

    <button onclick="showTab('buku')" id="tab_buku"
    class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-semibold">
    Buku
    </button>

    <button onclick="showTab('ppt')" id="tab_ppt"
    class="px-4 py-2 text-gray-500">
    PPT
    </button>

    <button onclick="showTab('lks')" id="tab_lks" 
    class="px-4 py-2 text-gray-500">
        
    LKPD
    </button>

    <button onclick="showTab('video')" id="tab_video" 
    class="px-4 py-2 text-gray-500">
    Video
    </button> 

    </div>


    <!-- ================= TABLE BUKU ================= -->

    <div id="table_buku">

    <div class="overflow-x-auto bg-white rounded shadow">

    <table class="min-w-full text-sm">

    <thead class="text-gray-500 text-xs border-b bg-gray-50">

    <tr>
    <th class="px-4 py-3">No</th>
    <th class="px-4 py-3">Cover</th>
    <th class="px-4 py-3">Judul</th>
    <th class="px-4 py-3">Mapel</th>
    <th class="px-4 py-3">Topik Materi</th>
    <th class="px-4 py-3">Deskripsi Topik</th>
    <th class="px-4 py-3">File</th>
    <th class="px-4 py-3">Action</th>
    </tr>

    </thead>

    <tbody class="divide-y">

    @foreach($books->where('tipe','buku') as $book)

    <tr class="hover:bg-gray-50">

    <td class="px-4 py-2">{{ $loop->iteration }}</td>

    <td class="px-4 py-2">
    @if($book->cover)
    <img src="{{ asset('library/sampul/'.$book->cover) }}"
    class="w-16 h-20 object-cover rounded">
    @endif
    </td>

    <td class="px-4 py-2 max-w-[200px] truncate">
    {{ $book->title }}
    </td>


    <td class="px-4 py-2">
    {{ $book->mapel->mata_pelajaran ?? '-' }}
    </td>

    <td>
    {{ $book->topik->nama_topik ?? '-' }}
    </td>

    <td>
    {{ Str::limit($book->topik->deskripsi ?? '-', 50) }}
    </td>

    <td class="px-4 py-2">

    @if($book->file)
    <a href="{{ asset('library/file/'.$book->file) }}"
    target="_blank"
    class="text-blue-500 text-xs">
    Lihat
    </a>
    @endif

    </td>

    <td class="px-4 py-2">

    <div class="flex gap-2">

    <button
    onclick="openEditModal(
    '{{ $book->id }}',
    'buku',
    @js($book->title),
    @js($book->description),
    '{{ $book->kelas_id }}',
    '{{ $book->mapel_id }}',
    '{{ $book->bab_id ?? '' }}',
    '{{ $book->topik_materi_id ?? '' }}'
)"                                                                                                                                                                                                                                                             
    class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs">
    Edit
    </button>

    <form action="{{ route('library.delete',$book->id) }}" method="POST">
    @csrf
    @method('DELETE')

    <button
    class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs">
    Delete
    </button>

    </form>

    </div>

    </td>

    </tr>

    @endforeach

    </tbody>

    </table>

    </div>
    </div>



    <!-- ================= TABLE PPT ================= -->

    <div id="table_ppt" class="hidden">

    <div class="overflow-x-auto bg-white rounded shadow">

    <table class="min-w-full text-sm">

    <thead class="text-gray-500 text-xs border-b bg-gray-50">

    <tr>
    <th class="px-4 py-3">No</th>
    <th class="px-4 py-3">Cover</th>
    <th class="px-4 py-3">Judul</th>
    <th class="px-4 py-3">Mapel</th>
    <th class="px-4 py-3">Topik Materi</th>
    <th class="px-4 py-3">Deskripsi Topik</th>
    <th class="px-4 py-3">File</th>
    <th class="px-4 py-3">Action</th>
    </tr>

    </thead>

    <tbody class="divide-y">

    @foreach($books->where('tipe','ppt') as $book)

    <tr class="hover:bg-gray-50">

    <td class="px-4 py-2">{{ $loop->iteration }}</td>

    <td class="px-4 py-2">
    @if($book->cover)
    <img src="{{ asset('library/sampul/'.$book->cover) }}"
    class="w-16 h-20 object-cover rounded">
    @endif
    </td>

    <td class="px-4 py-2 max-w-[200px] truncate">
    {{ $book->title }}
    </td>


    <td class="px-4 py-2">
    {{ $book->mapel->mata_pelajaran ?? '-' }}
    </td>

    <td>
    {{ $book->topik->nama_topik ?? '-' }}
    </td>

    <td>
    {{ Str::limit($book->topik->deskripsi ?? '-', 50) }}
    </td>

    <td class="px-4 py-2">

    @if($book->file)
    <a href="{{ asset('library/file/'.$book->file) }}"
    target="_blank"
    class="text-blue-500 text-xs">
    Lihat
    </a>
    @endif

    </td>

    <td class="px-4 py-2">

    <div class="flex gap-2">

    <button
    onclick="openEditModal(
    '{{ $book->id }}',
    'ppt',
    @js($book->title),
    @js($book->description),
    '{{ $book->kelas_id }}',
    '{{ $book->mapel_id }}',
    '{{ $book->bab_id ?? '' }}',
    '{{ $book->topik_materi_id ?? '' }}'
)"
    class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs">
    Edit
    </button>

    <form action="{{ route('library.delete',$book->id) }}" method="POST">
    @csrf
    @method('DELETE')

    <button
    class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs">
    Delete
    </button>

    </form>

    </div>

    </td>

    </tr>

    @endforeach

    </tbody>

    </table>

    </div>
    </div>

    <!-- ================= TABLE LKS ================= -->
    <!-- ================= TABLE LKS ================= -->
    <div id="table_lks" class="hidden">

        <div class="overflow-x-auto bg-white rounded shadow">

            <table class="min-w-full text-sm">

                <thead class="text-gray-500 text-xs border-b bg-gray-50">

                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Cover</th>
                        <th class="px-4 py-3">Judul LKS</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-4 py-3">Mapel</th>
                        <th class="px-4 py-3">Bab</th>
                        <th class="px-4 py-3">File LKPD</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>

                </thead>

                <tbody class="divide-y">

                    @forelse($books->where('tipe','lks') as $book)

                    <tr class="hover:bg-gray-50">

                        <td class="px-4 py-2">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-4 py-2">
                            @if($book->cover)
                                <img src="{{ asset('library/sampul/'.$book->cover) }}"
                                    class="w-16 h-20 object-cover rounded">
                            @else
                                <span class="text-xs text-gray-400">No Cover</span>
                            @endif
                        </td>

                        <td class="px-4 py-2 max-w-[200px] truncate">
                            {{ $book->title }}
                        </td>

                        <td class="px-4 py-2">
                            {{ $book->kelas->kelas ?? '-' }}
                        </td>

                        <td class="px-4 py-2">
                            {{ $book->mapel->mata_pelajaran ?? '-' }}
                        </td>

                        <td class="px-4 py-2">
                            {{ $book->bab->nama_bab ?? '-' }}
                        </td>

                        <td class="px-4 py-2">

                            @if($book->file)
                                <a href="{{ asset('library/file/'.$book->file) }}"
                                target="_blank"
                                class="text-blue-500 text-xs hover:underline">
                                    Lihat LKPD
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Tidak ada file</span>
                            @endif

                        </td>

                        <td class="px-4 py-2">

                            <div class="flex gap-2">

                                <button
                                    onclick="openEditModal(
                                        '{{ $book->id }}',
                                        'lks',
                                        '{{ $book->title }}',
                                        '{{ $book->description }}',
                                        '{{ $book->kelas_id }}',
                                        '{{ $book->mapel_id }}',
                                        '{{ $book->bab_id ?? '' }}'
                                        )"
                                    class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs">
                                    Edit
                                </button>

                                <form action="{{ route('library.delete',$book->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        onclick="return confirm('Hapus LKS ini?')"
                                        class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs">
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="8" class="text-center py-6 text-gray-400">
                            Tidak ada data LKS
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>
    <!-- ================= TABLE VIDEO ================= -->

    <div id="table_video" class="hidden">

    <div class="overflow-x-auto bg-white rounded shadow">

    <table class="min-w-full text-sm">

    <thead class="text-gray-500 text-xs border-b bg-gray-50">

    <tr>
    <th class="px-4 py-3">No</th>
    <th class="px-4 py-3">Cover</th>
    <th class="px-4 py-3">Judul</th>
    <th class="px-4 py-3">Kelas</th>
    <th class="px-4 py-3">Mapel</th>
    <th class="px-4 py-3">Bab</th>
    <th class="px-4 py-3">Video</th>
    <th class="px-4 py-3">Action</th>
    </tr>

    </thead>

    <tbody class="divide-y">

    @foreach($books->where('tipe','video') as $book)

    <tr class="hover:bg-gray-50">

    <td class="px-4 py-2">{{ $loop->iteration }}</td>

    <td class="px-4 py-2">

    @php
        $cover = $book->cover;
    @endphp

    @if($cover)

        @if(Str::startsWith($cover, 'http'))
            {{-- cover dari URL (YouTube / Drive) --}}
            <img src="{{ $cover }}"
                class="w-16 h-20 object-cover rounded">

        @else
            {{-- cover dari file lokal --}}
            <img src="{{ asset('library/sampul/'.$cover) }}"
                class="w-16 h-20 object-cover rounded">
        @endif

    @endif

    </td>

    <td class="px-4 py-2 max-w-[200px] truncate">
    {{ $book->title }}
    </td>

    <td class="px-4 py-2">{{ $book->kelas->kelas ?? '-' }}</td>

    <td class="px-4 py-2">{{ $book->mapel->mata_pelajaran ?? '-' }}</td>

    <td class="px-4 py-2">{{ $book->bab->nama_bab ?? '-' }}</td>

    <td class="px-4 py-2">
    <a href="{{ $book->file }}"
    target="_blank"
    class="text-blue-500 text-xs">
    Lihat Video
    </a>
    </td>

    <td class="px-4 py-2">

    <div class="flex gap-2">

        <button
            onclick="openEditModal(
            '{{ $book->id }}',
            'video',
            '{{ $book->title }}',
            '{{ $book->description }}',
            '{{ $book->kelas_id }}',
            '{{ $book->mapel_id }}',
            '{{ $book->bab_id ?? '' }}'
            )"
            class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs">
            Edit
        </button>

    <form action="{{ route('library.delete',$book->id) }}" method="POST">
    @csrf
    @method('DELETE')

    <button class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs">
    Delete
    </button>

    </form>

    </div>

    </td>

    </tr>

    @endforeach

    </tbody>

    </table>

    </div>
    </div>
    @endif


    </main>

    </div>
    </div>



    <dialog id="modal_pilih_tipe" class="modal">

        <div class="modal-box max-w-md">

            <h3 class="font-bold text-xl text-center mb-5">
                Pilih Tipe Library
            </h3>

            <div class="grid grid-cols-2 gap-3">

                <button
                    type="button"
                    onclick="pilihTipe('buku')"
                    class="btn btn-primary">

                    📖 Buku

                </button>

                <button
                    type="button"
                    onclick="pilihTipe('ppt')"
                    class="btn btn-info">

                    📊 PPT

                </button>

                <button
                    type="button"
                    onclick="pilihTipe('lks')"
                    class="btn btn-success">

                    📝 LKPD

                </button>

                <button
                    type="button"
                    onclick="pilihTipe('video')"
                    class="btn btn-warning">

                    🎥 Video

                </button>

            </div>

        </div>

    </dialog>

    <!-- ================= MODAL TAMBAH ================= -->
    <dialog id="modal_add_book" class="modal">

        <div class="modal-box w-[95%] max-w-2xl p-0 overflow-hidden rounded-2xl">

            <!-- HEADER -->
            <div class="bg-gradient-to-r from-blue-500 to-sky-600 px-6 py-5 text-white">
                <h3 class="text-2xl font-bold text-center">
                    📚 Tambah Library
                </h3>
                <p class="text-center text-sm opacity-90 mt-1">
                    Tambahkan materi pembelajaran dengan lengkap
                </p>
            </div>

            <form action="{{ route('library.store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="p-6">

                @csrf

                <!-- AUTO COVER -->
                <input type="hidden" name="auto_cover" id="auto_cover">

                <div class="grid md:grid-cols-2 gap-4">

                    <!-- TITLE -->
                    <div id="title_wrapper" class="md:col-span-2">
                        <label class="text-sm font-semibold mb-1 block">
                            Judul Materi <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="title" name="title" required class="input input-bordered w-full">
                    </div>

                    <!-- DESC -->
                    <div id="wrapper_description" class="md:col-span-2">
                        <label class="text-sm font-semibold mb-1 block">
                            Deskripsi <span class="text-red-500">*</span>
                        </label>

                        <textarea name="description"
                                required
                                rows="4"
                                class="textarea textarea-bordered w-full"></textarea>
                    </div>

                    <!-- KELAS -->
                    <div id="wrapper_kelas">
                    <label class="text-sm font-semibold mb-1 block">
                        Kelas <span class="text-red-500">*</span>
                    </label>

                    <select name="kelas_id" id="kelas_add" class="select select-bordered w-full">
                            <option value="">Pilih Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- MAPEL -->
                    <div>
                        <label class="text-sm font-semibold mb-1 block">
                            Mata Pelajaran <span class="text-red-500">*</span>
                        </label>

                        <select id="mapel_add" name="mapel_id" required class="select select-bordered w-full">
                            <option value="">Pilih Mapel</option>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->mata_pelajaran }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- BAB -->
                    <div id="wrapper_bab">
                        <label class="text-sm font-semibold mb-1 block">
                            Bab Materi <span class="text-red-500">*</span>
                        </label>

                        <select id="bab_add" name="bab_id" required class="select select-bordered w-full">
                            <option value="">Pilih Bab</option>
                            @foreach($babs as $bab)
                                <option value="{{ $bab->id }}" data-mapel="{{ $bab->mapel_id }}">
                                    {{ $bab->nama_bab }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- TIPE -->
                    <input
                    type="hidden"
                    id="tipe_library"
                    name="tipe">

                        <!-- TOPIK -->
                    <div class="md:col-span-2 hidden" id="topik_wrapper">
                        <label class="text-sm font-semibold mb-1 block">
                            Topik Materi
                        </label>

                        <select id="topik_add"
                                name="topik_materi_id"
                                class="select select-bordered w-full">

                                <option value="">pilih topik</option>
                            @foreach($topiks as $topik)
                            <option value="{{ $topik->id }}"
                                data-mapel="{{ $topik->mapel_id }}"
                                data-deskripsi="{{ $topik->deskripsi }}">
                                {{ $topik->nama_topik }}
                            </option>
                        @endforeach
                        </select>
                    </div>

                    <!-- DESKRIPSI TOPIK -->
                    <div class="md:col-span-2 hidden" id="topik_deskripsi_wrapper">

                        <label class="text-sm font-semibold mb-1 block">
                            Deskripsi Topik
                        </label>

                        <textarea
                            id="topik_deskripsi"
                            readonly
                            rows="3"
                            class="textarea textarea-bordered w-full bg-gray-100">
                        </textarea>

                    </div>

                    <!-- JUDUL OTOMATIS -->
                    <div class="md:col-span-2 hidden" id="title_auto_wrapper">

                        <label class="text-sm font-semibold mb-1 block">
                            Judul Materi
                        </label>

                        <input type="text" id="title_auto" readonly class="input input-bordered w-full bg-gray-100">

                    </div>

                    <!-- FILE -->
                    <div id="file_wrapper" class="md:col-span-2 hidden">
                        <label class="text-sm font-semibold mb-1 block">
                            Upload File
                        </label>

                        <input type="file"
                            name="file"
                            id="file_pdf"
                            required
                            class="file-input file-input-bordered w-full">

                        <small class="text-gray-500">
                            Format: PDF / PPT / DOC
                        </small>
                    </div>

                    <!-- VIDEO URL -->
                    <div id="video_wrapper" class="md:col-span-2 hidden">
                        <label class="text-sm font-semibold mb-1 block">
                            Link Video <span class="text-red-500">*</span>
                        </label>

                        <input type="url"
                            name="video_url"
                            id="video_url"
                            class="input input-bordered w-full"> 
                            
                        <small class="text-gray-500">
                            Format: link youtube / Gdrive
                        </small>
                    </div>

                    <!-- COVER PREVIEW -->
                    <div class="md:col-span-2 flex justify-center">
                        <div class="text-center">

                            <img id="cover_preview"
                                class="mt-2 w-40 h-56 object-cover rounded-xl hidden border shadow">

                            <p class="text-xs text-gray-500 mt-2">
                                Preview thumbnail otomatis
                            </p>

                        </div>
                    </div>

                </div>

                <!-- BUTTON -->
                <div class="flex justify-end gap-3 mt-7">

                    <button
                        type="button"
                        id="btnTambahTopik"
                        onclick="modal_add_topik.showModal()"
                        class="hidden bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">

                        ➕ Topik

                    </button>

                    <button
                            type="button"
                            onclick="resetModalTambah()"
                            class="px-5 py-2 rounded-lg border hover:bg-gray-100">
                            Reset
                        </button>

                    <button type="submit"
                            class="bg-blue-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg">

                        💾 Simpan

                    </button>

                </div>

            </form>

        </div>

        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>

    </dialog>

    <dialog id="modal_add_topik" class="modal">

        <div class="modal-box max-w-3xl">

            <form
                action="{{ route('library.topik.store') }}"
                method="POST">

                @csrf

                <h3 class="font-bold text-xl mb-5">
                    ➕ Tambah Topik Materi
                </h3>

                <div class="grid md:grid-cols-2 gap-4">

                    <div>
                        <label class="font-semibold block mb-1">
                            Mata Pelajaran
                        </label>

                        <select
                            id="topik_mapel"
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

                </div>

                <hr class="my-5">

                <div id="topikContainer">

                    <div class="grid grid-cols-12 gap-2 mb-3 topik-row">

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
                            class="input input-bordered col-span-6">

                        <button
                            type="button"
                            onclick="addTopikRow()"
                            class="btn btn-success col-span-1">

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

        <!-- ================= MODAL EDIT ================= -->
        <dialog id="modal_edit_book" class="modal">

            <div class="modal-box w-[95%] max-w-2xl p-0 overflow-hidden rounded-2xl">

                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-5 text-white">
                    <h3 class="text-2xl font-bold text-center">✏️ Edit Library</h3>
                    <p class="text-center text-sm opacity-90 mt-1">
                        Perbarui data materi pembelajaran
                    </p>
                </div>
                
                <form id="editForm" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tipe" id="edit_tipe">

                    <input type="hidden" name="auto_cover" id="auto_cover">

                    <div class="grid md:grid-cols-2 gap-4">

                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold mb-1 block">Judul *</label>
                            <input id="edit_title" name="title" required class="input input-bordered w-full">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold mb-1 block">Deskripsi *</label>
                        <textarea id="edit_description" name="description" rows="4" class="textarea textarea-bordered w-full"></textarea>
                        </div>

                        <div>
                            <label class="text-sm font-semibold mb-1 block">Kelas *</label>
                            <select id="edit_kelas" name="kelas_id" required class="select select-bordered w-full">
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->kelas }}</option>   
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold mb-1 block">Mapel *</label>
                            <select id="edit_mapel" name="mapel_id" required class="select select-bordered w-full">
                                @foreach($mapels as $mapel)
                                    <option value="{{ $mapel->id }}">{{ $mapel->mata_pelajaran }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold mb-1 block">Bab *</label>
                            <select id="edit_bab" name="bab_id"  class="select select-bordered w-full">
                                @foreach($babs as $bab)
                                    <option value="{{ $bab->id }}" data-mapel="{{ $bab->mapel_id }}">
                                        {{ $bab->nama_bab }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="file_wrapper_edit">
                            <label class="text-sm font-semibold mb-1 block">File Baru</label>
                            <input id="file_pdf_edit" type="file" name="file" class="file-input file-input-bordered w-full">
                        </div>

                        <div id="video_wrapper_edit" class="hidden">
                            <label class="text-sm font-semibold mb-1 block">Link Video</label>
                            <input id="video_url_edit" type="text" name="video_url" class="input input-bordered w-full">
                        </div>

                        <!-- TOPIK -->
                        <div class="md:col-span-2" id="edit_topik_wrapper">
                            <label class="text-sm font-semibold mb-1 block">
                                Topik Materi
                            </label>

                            <select id="edit_topik_add"
                                    name="topik_materi_id"
                                    class="select select-bordered w-full">  
                                @foreach($topiks as $t)
                                    <option value="{{ $t->id }}"
                                        data-deskripsi="{{ $t->deskripsi }}"
                                        data-kelas="{{ $t->kelas_id }}"
                                        data-mapel="{{ $t->mapel_id }}">
                                        {{ $t->nama_topik }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- DESKRIPSI TOPIK -->
                        <div class="md:col-span-2" id="edit_topik_deskripsi_wrapper">
                            <label class="text-sm font-semibold mb-1 block">
                                Deskripsi Topik
                            </label>

                            <input
                                type="text"
                                id="edit_topik_deskripsi"
                                readonly
                                class="input input-bordered w-full bg-gray-100"
                            />
                        </div>

                        <!-- JUDUL OTOMATIS -->
                        <div class="md:col-span-2" id="edit_title_auto_wrapper">
                            <label class="text-sm font-semibold mb-1 block">
                                Judul Materi
                            </label>

                            <input
                                readonly
                                id="edit_title_auto"
                                name="title"
                                class="input input-bordered w-full bg-gray-100">
                        </div>

                    </div>  

                    <div class="flex justify-end gap-3 mt-7">
                        <button type="button" onclick="modal_edit_book.close()" class="px-5 py-2 border rounded-lg">
                            Batal
                        </button>

                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            🚀 Update
                        </button>
                    </div>

                </form>

            </div>

            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>

        </dialog>

    <!-- ================= SCRIPT ================= -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>


    <script>

    // ================= TAB FUNCTION =================
    function showTab(tab) {

        const tabs = ['buku','ppt','lks','video'];

        tabs.forEach(t => {

            const table = document.getElementById('table_' + t);
            const btn = document.getElementById('tab_' + t);

            if (table) table.classList.add('hidden');

            if (btn) {
                btn.classList.remove('border-blue-500','text-blue-600','font-semibold');
                btn.classList.add('text-gray-500');
            }
        });

        const activeTable = document.getElementById('table_' + tab);
        const activeBtn = document.getElementById('tab_' + tab);

        if (activeTable) activeTable.classList.remove('hidden');

        if (activeBtn) {
            activeBtn.classList.remove('text-gray-500');
            activeBtn.classList.add('border-blue-500','text-blue-600','font-semibold');
        }
    }

    document.getElementById('edit_topik_add')
.addEventListener('change', function () {

    let opt = this.options[this.selectedIndex];

    let kelasId = opt.dataset.kelas;
    let mapelId = opt.dataset.mapel;
    let deskripsi = opt.dataset.deskripsi;

    // 🔥 AUTO SET KELAS
    if (kelasId) {
        document.getElementById('edit_kelas').value = kelasId;
    }

    // 🔥 AUTO SET MAPEL
    if (mapelId) {
        document.getElementById('edit_mapel').value = mapelId;
    }

    // 🔥 AUTO DESKRIPSI
    document.getElementById('edit_topik_deskripsi').value =
        opt.dataset.deskripsi || '';

        if (this.value) {

    let tipe = document.getElementById('tipe_library').value;

    fetch(`/administrator/library/get-series/${this.value}?tipe=${tipe}`)
    .then(res => res.json())
    .then(series => {

        let autoTitle =
            'Series Materi ' + series.next;

        document.getElementById(
            'edit_title_auto'
        ).value = autoTitle;
    });

} else {

    document.getElementById(
        'edit_title_auto'
    ).value = '';
}

});

document.getElementById('edit_kelas')
.addEventListener('change', function(){

    let kelasId = this.value;

    let mapelId =
        document.getElementById('edit_mapel').value;

    if(!kelasId || !mapelId) return;

    fetch(
        `/administrator/library/get-topik?kelas_id=${kelasId}&mapel_id=${mapelId}`
    )
    .then(res => res.json())
    .then(data => {

        let topik =
            document.getElementById('edit_topik_add');

        topik.innerHTML =
            '<option value="">Pilih Topik</option>';

        data.forEach(t => {

            let opt =
                document.createElement('option');

            opt.value = t.id;
            opt.textContent = t.nama_topik;

            opt.dataset.deskripsi =
                t.deskripsi || '';

            opt.dataset.kelas =
                t.kelas_id;

            opt.dataset.mapel =
                t.mapel_id;

            topik.appendChild(opt);
        });

        document.getElementById(
            'edit_topik_deskripsi'
        ).value = '';

        document.getElementById('edit_topik_deskripsi').value = '';
    });
});
document.getElementById('edit_mapel')?.addEventListener('change', function () {

    let mapelId = this.value;

    let kelasId =
    document.getElementById('edit_kelas').value;

fetch(
    `/administrator/library/get-topik?kelas_id=${kelasId}&mapel_id=${mapelId}`
)
        .then(res => res.json())
        .then(data => {

            let topik = document.getElementById('edit_topik_add');

            topik.innerHTML = '<option value="">Pilih Topik</option>';

            data.forEach(t => {

                let opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nama_topik;

                opt.dataset.deskripsi = t.deskripsi || '';
                opt.dataset.kelas = t.kelas_id;
                opt.dataset.mapel = t.mapel_id;

                topik.appendChild(opt);
            });
        });
});


    // ================= EDIT MODAL =================
function openEditModal(id, tipe, title, description, kelas, mapel, bab, topik_id = null){

    const modal = document.getElementById('modal_edit_book');
    const form = document.getElementById('editForm');

    form.reset();
    form.action = "/administrator/library/update/" + id;

    toggleEditType(tipe);

    document.getElementById('edit_title').value = title || '';
    document.getElementById('edit_description').value = description || '';
    document.getElementById('edit_kelas').value = kelas;
    document.getElementById('edit_mapel').value = mapel;
    document.getElementById('edit_bab').value = bab;
    document.getElementById('edit_tipe').value = tipe;

    // 🔥 SKIP TOPIK untuk LKS & VIDEO
    if (tipe === 'lks' || tipe === 'video') {

    const schoolId = $('#container').data('school-id');

    $.get(
        schoolId
            ? `/kelas/${kelas}/${schoolId}/mapel`
            : `/kelas/${kelas}/mapel`,
        function (mapels) {

            let mapelSelect = $('#edit_mapel');
            mapelSelect.html('<option value="">Pilih Mata Pelajaran</option>');

            mapels.forEach(function (m) {

                mapelSelect.append(`
                    <option value="${m.id}">
                        ${m.mata_pelajaran}
                    </option>
                `);

            });

            mapelSelect.val(mapel);

            $.get(`/mapel/${mapel}/bab`, function (babs) {

                let babSelect = $('#edit_bab');
                babSelect.html('<option value="">Pilih Bab</option>');

                babs.forEach(function (b) {

                    babSelect.append(`
                        <option value="${b.id}">
                            ${b.nama_bab}
                        </option>
                    `);

                });

                babSelect.val(bab);

                modal.showModal();

            });

        }
    );

    return;
}

    fetch(`/administrator/library/get-topik?kelas_id=${kelas}&mapel_id=${mapel}`)
        .then(res => res.json())
        .then(data => {

            let topik = document.getElementById('edit_topik_add');
            topik.innerHTML = '<option value="">Pilih Topik</option>';

            data.forEach(t => {
                let opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nama_topik;

                opt.dataset.deskripsi = t.deskripsi || '';
                opt.dataset.kelas = t.kelas_id;
                opt.dataset.mapel = t.mapel_id;

                topik.appendChild(opt);
            });

            if (topik_id) {
                return fetch(`/administrator/library/get-series/${topik_id}`);
            }
        })
        .then(res => res ? res.json() : null)
        .then(series => {

            if (series) {
                document.getElementById('edit_title_auto').value =
                    'Series Materi ' + series.next;
            }

            modal.showModal();
        });
}


    function syncEditDependencies() {

        const mapel = document.getElementById('edit_mapel');
        const bab = document.getElementById('edit_bab');

        if (mapel && bab) {

            bab.querySelectorAll('option').forEach(opt => {

                if (!opt.dataset.mapel) return;

                opt.style.display =
                    opt.dataset.mapel == mapel.value
                    ? 'block'
                    : 'none';
            });
        }
    }


    function fetchTopik() {

    let mapelId = document.getElementById('mapel_add')?.value;

    if (!mapelId) return;

    fetch(`/administrator/library/get-topik?mapel_id=${mapelId}`)
        .then(res => res.json())
        .then(data => {

            const select = document.getElementById('topik_add');
            select.innerHTML = '<option value="">Pilih Topik</option>';

            data.forEach(t => {
                let opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nama_topik;
                opt.dataset.deskripsi = t.deskripsi ?? '';
                select.appendChild(opt);
            });

            document.getElementById('topik_wrapper')
                .classList.remove('hidden');
        });
}

    function toggleEditType(tipe) {

        const fileWrapper = document.getElementById("file_wrapper_edit");
        const videoWrapper = document.getElementById("video_wrapper_edit");

        const titleWrapper =
            document.getElementById("edit_title").closest(".md\\:col-span-2");

        const descriptionWrapper =
            document.getElementById("edit_description").closest(".md\\:col-span-2");

        const kelasWrapper =
            document.getElementById("edit_kelas").parentElement;

        const mapelWrapper =
            document.getElementById("edit_mapel").parentElement;

        const babWrapper =
            document.getElementById("edit_bab").parentElement;

        const topikWrapper =
            document.getElementById("edit_topik_wrapper");

        const topikDeskripsiWrapper =
            document.getElementById("edit_topik_deskripsi_wrapper");

        const titleAutoWrapper =
            document.getElementById("edit_title_auto_wrapper");

        // reset semua
        fileWrapper.style.display = "none";
        videoWrapper.style.display = "none";

        titleWrapper.style.display = "none";
        descriptionWrapper.style.display = "none";

        kelasWrapper.style.display = "none";
        mapelWrapper.style.display = "none";
        babWrapper.style.display = "none";

        topikWrapper.style.display = "none";
        topikDeskripsiWrapper.style.display = "none";
        titleAutoWrapper.style.display = "none";


        // ================= BUKU / PPT =================
        if (["buku", "ppt"].includes(tipe)) {

            kelasWrapper.style.display = "none";
            toggleRequired(document.getElementById('edit_kelas'), false);

            mapelWrapper.style.display = "block";
            topikWrapper.style.display = "block";
            topikDeskripsiWrapper.style.display = "block";
            titleAutoWrapper.style.display = "block";
        }

        // ================= LKS =================
        else if (tipe === "lks") {

            titleWrapper.style.display = "block";

            kelasWrapper.style.display = "block";
            toggleRequired(document.getElementById('edit_kelas'), true);
            mapelWrapper.style.display = "block";
            babWrapper.style.display = "block";
        }

        // ================= VIDEO =================
        else if (tipe === "video") {

            titleWrapper.style.display = "block";

            kelasWrapper.style.display = "block";
            toggleRequired(document.getElementById('edit_kelas'), true);
            mapelWrapper.style.display = "block";
            babWrapper.style.display = "block";

            videoWrapper.style.display = "block";
        }
    }

    function toggleRequired(el, status) {
    if (!el) return;

    if (status) {
        el.setAttribute('required', 'required');
    } else {
        el.removeAttribute('required');
        el.value = ""; // optional: biar bersih juga
    }
}

document.getElementById('edit_topik_add')
.addEventListener('change', function () {

    let tipe = document.getElementById('edit_tipe').value;

    // 🔥 SKIP
    if (tipe === 'lks' || tipe === 'video') return;

    let opt = this.options[this.selectedIndex];

    document.getElementById('edit_topik_deskripsi').value =
        opt.dataset.deskripsi || '';
});
    //================load topik============
   function loadTopikMateri() {

    let mapelId = document.getElementById('mapel_add').value;

    if (!mapelId) return;

    fetch(`/administrator/library/get-topik?mapel_id=${mapelId}`)
        .then(res => res.json())
        .then(data => {

            const topik = document.getElementById('topik_add');

            topik.innerHTML = '<option value="">Pilih Topik</option>';

            data.forEach(item => {

                topik.innerHTML += `
                    <option
                        value="${item.id}"
                        data-deskripsi="${item.deskripsi ?? ''}">
                        ${item.nama_topik}
                    </option>
                `;
            });

        });
}

document.getElementById('mapel_add')
    .addEventListener('change', loadTopikMateri);

    function filterTopik(mapelId, topikSelect) {

    topikSelect.querySelectorAll('option').forEach(opt => {

        if (!opt.dataset.mapel) return;

        opt.style.display =
            opt.dataset.mapel == mapelId ? 'block' : 'none';
    });

    topikSelect.value = "";
}

function setRequired(el, status) {
    if (!el) return;

    if (status) {
        el.setAttribute('required', 'required');
    } else {
        el.removeAttribute('required');
    }
}

document.getElementById('mapel_add')
?.addEventListener('change', function () {

    filterTopik(this.value, document.getElementById('topik_add'));
});

    // ================= FILTER BAB =================
    function filterBab(mapelId,babSelect){

        babSelect.querySelectorAll('option').forEach(opt => {

            if(!opt.dataset.mapel) return;

            opt.style.display =
                opt.dataset.mapel == mapelId ? 'block' : 'none';
        });

        babSelect.value = "";
    }

    document.getElementById('mapel_add')
    ?.addEventListener('change', function () {

        filterBab(this.value, document.getElementById('bab_add'));
    });


    // ================= COVER GENERATOR =================
    const fileInput = document.getElementById("file_pdf");
    const autoCoverInput = document.getElementById("auto_cover");

    fileInput?.addEventListener("change", function(e){

        const file = e.target.files[0];
        if(!file) return;

        const ext = file.name.split('.').pop().toLowerCase();

        // ================= PDF =================
        if(ext === "pdf"){

            const reader = new FileReader();

            reader.onload = function(){

                const typedarray = new Uint8Array(this.result);

                pdfjsLib.getDocument(typedarray).promise.then(function(pdf){

                    pdf.getPage(1).then(function(page){

                        const viewport = page.getViewport({scale:1.5});

                        const canvas = document.createElement("canvas");
                        const context = canvas.getContext("2d");

                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        page.render({
                            canvasContext:context,
                            viewport:viewport
                        }).promise.then(function(){

                            autoCoverInput.value = canvas.toDataURL("image/jpeg");

                        });

                    });

                });

            };

            reader.readAsArrayBuffer(file);
        }

        // ================= PPT / DOC =================
        if(["ppt","pptx","doc","docx"].includes(ext)){

            const canvas = document.createElement("canvas");
            const ctx = canvas.getContext("2d");

            canvas.width = 600;
            canvas.height = 800;

            ctx.fillStyle = "#2563EB";
            ctx.fillRect(0,0,canvas.width,canvas.height);

            ctx.fillStyle = "#fff";
            ctx.font = "bold 40px Arial";
            ctx.textAlign="center";

            ctx.fillText(ext.toUpperCase()+" FILE",300,400);

            autoCoverInput.value = canvas.toDataURL("image/jpeg");
        }

    });


    // ================= VIDEO / FILE TOGGLE =================
    document.addEventListener("DOMContentLoaded", function () {

        const tipe =
            document.getElementById("tipe_library");

        const fileWrapper =
            document.getElementById("file_wrapper");

        const fileInput =
            document.getElementById("file_pdf");

        const videoUrl =
            document.getElementById("video_url");

        const videoWrapper =
            document.getElementById("video_wrapper");

        const autoCoverInput =
            document.getElementById("auto_cover");

        const coverPreview =
            document.getElementById("cover_preview");

        // ================= FORM LAMA =================

        const titleField =
            document.querySelector('input[name="title"]')
            ?.closest('.md\\:col-span-2');

        const descField =
            document.querySelector('textarea[name="description"]')
            ?.closest('.md\\:col-span-2');

        const babField =
            document.getElementById('bab_add')
            ?.closest('div');

        const kelasField =
        document.querySelector('select[name="kelas_id"]')
        ?.closest('div');

        const mapelField =
        document.getElementById('mapel_add')
        ?.closest('div');

        // ================= FORM BARU =================

        const topikWrapper =
            document.getElementById("topik_wrapper");

        const topikDescWrapper =
            document.getElementById("topik_deskripsi_wrapper");

        const titleAutoWrapper =
            document.getElementById("title_auto_wrapper");

        const descWrapper = document.getElementById('wrapper_description');

        const babWrapper = document.getElementById('wrapper_bab');


        // ================= TOGGLE FORM =================

        function toggleLibraryForm(tipe) {

    // ================= WRAPPERS =================
    const titleField = document.getElementById('title')?.closest('.md\\:col-span-2');
    const descWrapper = document.getElementById('wrapper_description');

    const kelasWrapper = document.getElementById('wrapper_kelas');
    const mapelWrapper = document.getElementById('mapel_add')?.closest('div');
    const babWrapper = document.getElementById('wrapper_bab');

    const topikWrapper = document.getElementById("topik_wrapper");
    const topikDescWrapper = document.getElementById("topik_deskripsi_wrapper");
    const titleAutoWrapper = document.getElementById("title_auto_wrapper");

    const fileWrapper = document.getElementById("file_wrapper");
    const videoWrapper = document.getElementById("video_wrapper");

    const desc = document.querySelector('textarea[name="description"]');
    const bab = document.getElementById('bab_add');
    // ================= RESET SEMUA =================
    [
    kelasWrapper,
    mapelWrapper,
    babWrapper,
    topikWrapper,
    topikDescWrapper,
    titleAutoWrapper,
    fileWrapper,
    videoWrapper
].forEach(el => el?.classList.add("hidden"));

    // default selalu tampil
    titleField?.classList.remove("hidden");
    descWrapper?.classList.remove("hidden");
    mapelWrapper?.classList.remove("hidden");

    // reset required state
    const fileInput = document.getElementById("file_pdf");
    const videoInput = document.getElementById("video_url");

    if (fileInput) fileInput.required = false;
    if (videoInput) videoInput.required = false;

    // ================= BUKU =================
    if (tipe === "buku") {

         // hide manual title
    titleField?.classList.add("hidden");

    // show auto title
    titleAutoWrapper?.classList.remove("hidden");

    descWrapper?.classList.add("hidden");

    kelasWrapper?.classList.add("hidden");
    babWrapper?.classList.add("hidden");

    topikWrapper?.classList.remove("hidden");
    topikDescWrapper?.classList.remove("hidden");

    fileWrapper?.classList.remove("hidden");

    setRequired(desc, false);
    setRequired(bab, false);

    if (fileInput) fileInput.required = true;
    }

    // ================= PPT =================
    else if (tipe === "ppt") {

        // hide manual title
    titleField?.classList.add("hidden");

    // show auto title
    titleAutoWrapper?.classList.remove("hidden");

    descWrapper?.classList.add("hidden");

    kelasWrapper?.classList.add("hidden");
    babWrapper?.classList.add("hidden");

    topikWrapper?.classList.remove("hidden");
    topikDescWrapper?.classList.remove("hidden");

    fileWrapper?.classList.remove("hidden");

    setRequired(desc, false);
    setRequired(bab, false);

    if (fileInput) fileInput.required = true;
    }

    // ================= LKS =================
    else if (tipe === "lks") {

        kelasWrapper?.classList.remove("hidden");
        babWrapper?.classList.remove("hidden");

        fileWrapper?.classList.remove("hidden");

        setRequired(desc, true);
        setRequired(bab, true);
        if (fileInput) fileInput.required = true;
    }

    // ================= VIDEO =================
    else if (tipe === "video") {

        kelasWrapper?.classList.remove("hidden");
        babWrapper?.classList.remove("hidden");

        videoWrapper?.classList.remove("hidden");

        setRequired(desc, true);
        setRequired(bab, true);
        if (videoInput) videoInput.required = true;
    }
}

        // ================= RESET =================

        function resetAll() {

            fileWrapper.classList.add("hidden");
            videoWrapper.classList.add("hidden");

            fileInput.required = false;
            videoUrl.required = false;

            fileInput.value = "";
            videoUrl.value = "";

            autoCoverInput.value = "";

            coverPreview.classList.add("hidden");
            coverPreview.src = "";
        }

        // ================= FILE =================

        function showFileInput() {

            fileWrapper.classList.remove("hidden");

            fileInput.required = true;
            videoUrl.required = false;
        }

        // ================= VIDEO =================

        function showVideoInput() {

            videoWrapper.classList.remove("hidden");

            fileWrapper.classList.add("hidden");

            fileInput.required = false;
            videoUrl.required = true;
        }

        // ================= INIT =================

        resetAll();

        toggleLibraryForm(tipe.value);

        if (
            tipe.value === "buku" ||
            tipe.value === "ppt" ||
            tipe.value === "lks"
        ) {
            showFileInput();
        }

        if (tipe.value === "video") {
            showVideoInput();
        }

        // ================= CHANGE =================

        tipe.addEventListener("change", function () {

            resetAll();

            const val = this.value;

            toggleLibraryForm(val);

            if (
                val === "buku" ||
                val === "ppt" ||
                val === "lks"
            ) {
                showFileInput();
            }

            if (val === "video") {
                showVideoInput();
            }

        });

        // ================= AUTO COVER VIDEO =================

        videoUrl.addEventListener("input", function () {

            let url = this.value.trim();

            if (!url) return;

            let thumbnail = "";

            if (
                url.includes("youtube.com") ||
                url.includes("youtu.be")
            ) {

                let videoId = "";

                if (url.includes("watch?v=")) {

                    videoId =
                        url.split("v=")[1]
                        .split("&")[0];

                } else if (
                    url.includes("youtu.be/")
                ) {

                    videoId =
                        url.split("youtu.be/")[1]
                        .split("?")[0];
                }

                if (videoId) {

                    thumbnail =
                        `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
                }

            } else if (
                url.includes("drive.google.com")
            ) {

                let match =
                    url.match(/\/d\/(.*?)\//);

                if (match) {

                    thumbnail =
                        `https://drive.google.com/thumbnail?id=${match[1]}&sz=w1000`;
                }
            }

            if (thumbnail) {

                autoCoverInput.value = thumbnail;

                coverPreview.src = thumbnail;

                coverPreview.classList.remove("hidden");
            }

            document.querySelector(
            '#modal_add_book select[name="kelas_id"]'
        )?.addEventListener('change', loadTopikMateri);

            document.getElementById('mapel_add')
        ?.addEventListener('change', loadTopikMateri);
        });
    });
    let topikIndex = 1;

    function addTopikRow() {

        const row = `
            <div class="grid grid-cols-12 gap-2 mb-3 topik-row">

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
                    class="btn btn-error col-span-2"
                    onclick="this.closest('.topik-row').remove()">

                    -

                </button>

            </div>
        `;

        document
            .getElementById('topikContainer')
            .insertAdjacentHTML('beforeend', row);

        topikIndex++;
    }

    function openTambahLibrary() {

        document
            .getElementById('modal_pilih_tipe')
            .showModal();
    }

    function pilihTipe(tipe) {

        const tipeSelect =
            document.getElementById("tipe_library");

        tipeSelect.value = tipe;

        tipeSelect.dispatchEvent(
            new Event("change")
        );

        if (
            tipe === "buku" ||
            tipe === "ppt"
        ) {

            document
                .getElementById("btnTambahTopik")
                ?.classList.remove("hidden");

        } else {

            document
                .getElementById("btnTambahTopik")
                ?.classList.add("hidden");
        }

        document
            .getElementById("modal_pilih_tipe")
            .close();

        document
            .getElementById("modal_add_book")
            .showModal();
    }


    document.getElementById('topik_add').addEventListener('change', function () {
        let option = this.options[this.selectedIndex];
        let deskripsi = option.dataset.deskripsi || '';

        document.getElementById('topik_deskripsi').value = deskripsi;

        if (this.value) {
            let tipe = document.getElementById('tipe_library').value;

                fetch(`/administrator/library/get-series/${this.value}?tipe=${tipe}`)
                .then(res => res.json())
                .then(data => {
                    let autoTitle = 'Series Materi ' + data.next;

                    // tampilkan preview
                    document.getElementById('title_auto').value = autoTitle;

                    // auto isi title utama
                    document.getElementById('title').value = autoTitle;
                });
        } else {
            document.getElementById('title_auto').value = '';
            document.getElementById('title').value = '';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {


        document.getElementById('mapel_add')
        ?.addEventListener('change', loadTopikMateri);

    });

$(document).ready(function () {

    let oldKelas = $('#kelas_add').data('old-kelas');
    let oldMapel = $('#mapel_add').data('old-mapel');
    let oldBab = $('#bab_add').data('old-bab');

    const schoolId = $('#container').data('school-id');

    function resetSelect($select, placeholder) {
        $select.html(`<option value="">${placeholder}</option>`);
    }


    // =====================================
    // TIPE LIBRARY BERUBAH
    // =====================================
$('#tipe_library').on('change', function () {

    const tipe = $(this).val();

    resetSelect($('#bab_add'), 'Pilih Bab');
    resetSelect($('#topik_add'), 'Pilih Topik');

    if(tipe !== 'buku' && tipe !== 'ppt'){
        resetSelect($('#mapel_add'), 'Pilih Mata Pelajaran');
    }

});

    // =====================================
    // KELAS -> MAPEL
    // =====================================
    $('#kelas_add').on('change', function () {

        const tipe = $('#tipe_library').val();

        if (tipe === 'buku' || tipe === 'ppt') {
            return;
        }

        resetSelect($('#mapel_add'), 'Pilih Mata Pelajaran');
        resetSelect($('#bab_add'), 'Pilih Bab');

        const kelasId = $(this).val();

        if (!kelasId) return;

        $.get(
            schoolId
                ? `/kelas/${kelasId}/${schoolId}/mapel`
                : `/kelas/${kelasId}/mapel`,
            function (data) {

                data.forEach(function (mapel) {

                    $('#mapel_add').append(
                        `<option value="${mapel.id}">
                            ${mapel.mata_pelajaran}
                        </option>`
                    );

                });

                if (oldMapel) {
                    $('#mapel_add').val(oldMapel).trigger('change');
                    oldMapel = null;
                }

            }
        );

    });

    // =====================================
    // MAPEL
    // =====================================
    $('#mapel_add').on('change', function () {

        const tipe = $('#tipe_library').val();
        const mapelId = $(this).val();

        resetSelect($('#bab_add'), 'Pilih Bab');
        resetSelect($('#topik_add'), 'Pilih Topik');

        if (!mapelId) return;

        // =====================
        // BUKU & PPT
        // =====================
        if (tipe === 'buku' || tipe === 'ppt') {

            $.get(`/mapel/${mapelId}/topik`, function (data) {

                data.forEach(function (topik) {

                    $('#topik_add').append(
                        `<option value="${topik.id}">
                            ${topik.nama_topik}
                        </option>`
                    );

                });

            });

            return;
        }

        // =====================
        // VIDEO & LKPD
        // =====================
        $.get(`/mapel/${mapelId}/bab`, function (data) {

            data.forEach(function (bab) {

                $('#bab_add').append(
                    `<option value="${bab.id}">
                        ${bab.nama_bab}
                    </option>`
                );

            });

            if (oldBab) {
                $('#bab_add').val(oldBab);
                oldBab = null;
            }

        });

    });

    // =====================================
    // EDIT MODE
    // =====================================
    const tipe = $('#tipe_library').val();

    if (tipe === 'buku' || tipe === 'ppt') {


        $('#kelas_add').val(oldKelas).trigger('change');

    }

});

function resetModalTambah() {

    const modal = document.getElementById('modal_add_book');
    const form = modal.querySelector('form');

    form.reset();

    $('#tipe_library').val('');

    resetSelect($('#mapel_add'), 'Pilih Mata Pelajaran');
    resetSelect($('#bab_add'), 'Pilih Bab');
    resetSelect($('#topik_add'), 'Pilih Topik');

    $('#auto_cover').val('');

    $('#cover_preview')
        .attr('src', '')
        .addClass('hidden');

    $('#topik_deskripsi').val('');
    $('#title_auto').val('');
    $('#title').val('');

    modal.close();
}

$('#edit_kelas').on('change', function () {

    const tipe = $('#edit_tipe').val();

    // buku & ppt tidak pakai logika ini
    if (tipe === 'buku' || tipe === 'ppt') {
        return;
    }

    $('#edit_mapel').html('<option value="">Pilih Mata Pelajaran</option>');
    $('#edit_bab').html('<option value="">Pilih Bab</option>');

    const kelasId = $(this).val();
    if (!kelasId) return;

    const schoolId = $('#container').data('school-id');

    $.get(
        schoolId
            ? `/kelas/${kelasId}/${schoolId}/mapel`
            : `/kelas/${kelasId}/mapel`,
        function (data) {

            data.forEach(function (mapel) {

                $('#edit_mapel').append(`
                    <option value="${mapel.id}">
                        ${mapel.mata_pelajaran}
                    </option>
                `);

            });

        }
    );

});

$('#edit_mapel').on('change', function () {

    const tipe = $('#edit_tipe').val();

    // Buku & PPT tetap pakai Topik
    if (tipe === 'buku' || tipe === 'ppt') {
        return;
    }

    const mapelId = $(this).val();

    $('#edit_bab').html('<option value="">Pilih Bab</option>');

    if (!mapelId) return;

    $.get(`/mapel/${mapelId}/bab`, function (data) {

        data.forEach(function (bab) {

            $('#edit_bab').append(`
                <option value="${bab.id}">
                    ${bab.nama_bab}
                </option>
            `);

        });

    });

});
    </script>

