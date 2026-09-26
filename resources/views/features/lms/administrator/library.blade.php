    @include('components/sidebar-beranda', ['headerSideNav' => 'LMS Library'])
    

    @php
    use Illuminate\Support\Str;
    @endphp

    @if (Auth::user()->role === 'Administrator')

    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] min-h-screen bg-white transition-all duration-500 ease-in-out z-20">
    <div class="mt-4 sm:mt-6 mb-10 mx-4 sm:mx-7.5">
        

    <main>
    @include('features.lms.components.library.upload-manager')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- ================= HEADER ================= -->

    <!-- ================= HEADER & CONTROLS ================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('library.administrator') }}" class="relative w-full sm:w-80">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari materi perpustakaan..."
                    class="w-full h-11 bg-white border border-gray-300 rounded-xl pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 placeholder:text-gray-400 shadow-sm"
                    oninput="this.form.submit()"
                >
            </div>
        </form>

        <button
            onclick="modal_pilih_tipe.showModal()"
            class="bg-[#0071BC] hover:bg-blue-600 text-white font-semibold h-11 px-5 rounded-xl shadow-sm hover:shadow transition-all text-sm flex items-center justify-center gap-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Library</span>
        </button>
    </div>

    <!-- ================= TABS ================= -->
    <div class="flex items-center gap-2 mb-6 border-b border-gray-200 overflow-x-auto pb-px">
        <button onclick="showTab('buku')" id="tab_buku"
            class="px-5 py-2.5 border-b-2 border-[#0071BC] text-[#0071BC] font-semibold text-sm transition-all flex items-center gap-2 shrink-0 cursor-pointer">
            <i class="fa-solid fa-book"></i>
            <span>Buku</span>
            <span class="bg-blue-50 text-[#0071BC] text-xs px-2 py-0.5 rounded-full font-bold ml-1">{{ $books->where('tipe','buku')->count() }}</span>
        </button>

        <button onclick="showTab('ppt')" id="tab_ppt"
            class="px-5 py-2.5 text-gray-500 hover:text-gray-700 text-sm font-medium transition-all flex items-center gap-2 shrink-0 cursor-pointer border-b-2 border-transparent">
            <i class="fa-solid fa-file-powerpoint"></i>
            <span>PPT</span>
            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-bold ml-1">{{ $books->where('tipe','ppt')->count() }}</span>
        </button>

        <button onclick="showTab('lks')" id="tab_lks" 
            class="px-5 py-2.5 text-gray-500 hover:text-gray-700 text-sm font-medium transition-all flex items-center gap-2 shrink-0 cursor-pointer border-b-2 border-transparent">
            <i class="fa-solid fa-file-lines"></i>
            <span>LKPD</span>
            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-bold ml-1">{{ $books->where('tipe','lks')->count() }}</span>
        </button>

        <button onclick="showTab('video')" id="tab_video" 
            class="px-5 py-2.5 text-gray-500 hover:text-gray-700 text-sm font-medium transition-all flex items-center gap-2 shrink-0 cursor-pointer border-b-2 border-transparent">
            <i class="fa-solid fa-video"></i>
            <span>Video</span>
            <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-bold ml-1">{{ $books->where('tipe','video')->count() }}</span>
        </button> 
    </div>

    <!-- ================= TABLE BUKU ================= -->
    <div id="table_buku">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50/80 text-gray-700 text-xs font-semibold uppercase tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-12">No</th>
                            <th class="px-4 py-3.5 text-left">Cover</th>
                            <th class="px-4 py-3.5 text-left">Judul</th>
                            <th class="px-4 py-3.5 text-left">Mapel</th>
                            <th class="px-4 py-3.5 text-left">Topik Materi</th>
                            <th class="px-4 py-3.5 text-left">Deskripsi Topik</th>
                            <th class="px-4 py-3.5 text-center">File</th>
                            <th class="px-4 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($books->where('tipe','buku') as $book)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">
                                @if($book->cover)
                                    <img src="{{ asset('library/sampul/'.$book->cover) }}" class="w-14 h-18 object-cover rounded-xl shadow-xs border border-gray-200">
                                @else
                                    <div class="w-14 h-18 bg-gray-100 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 text-xs">No Cover</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800 max-w-[200px] truncate">{{ $book->title }}</td>
                            <td class="px-4 py-3 text-gray-600 font-medium">{{ $book->mapel->mata_pelajaran ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $book->topik->nama_topik ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">{{ Str::limit($book->topik->deskripsi ?? '-', 50) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($book->file)
                                    <a href="{{ asset('library/file/'.$book->file) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#0071BC] bg-blue-50 hover:bg-blue-100 border border-blue-200/60 transition-colors">
                                        <i class="fa-solid fa-eye text-[11px]"></i>
                                        <span>Lihat</span>
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="openEditModal('{{ $book->id }}', 'buku', @js($book->title), @js($book->description), '{{ $book->kelas_id }}', '{{ $book->mapel_id }}', '{{ $book->bab_id ?? '' }}', '{{ $book->topik_materi_id ?? '' }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 transition-colors cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                        <span>Edit</span>
                                    </button>
                                    <form action="{{ route('library.delete',$book->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus buku ini?')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200/80 transition-colors cursor-pointer">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-400">
                                <i class="fa-solid fa-book-open text-4xl mb-2 text-gray-300 block"></i>
                                Tidak ada data buku
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TABLE PPT ================= -->
    <div id="table_ppt" class="hidden">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50/80 text-gray-700 text-xs font-semibold uppercase tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-12">No</th>
                            <th class="px-4 py-3.5 text-left">Cover</th>
                            <th class="px-4 py-3.5 text-left">Judul</th>
                            <th class="px-4 py-3.5 text-left">Mapel</th>
                            <th class="px-4 py-3.5 text-left">Topik Materi</th>
                            <th class="px-4 py-3.5 text-left">Deskripsi Topik</th>
                            <th class="px-4 py-3.5 text-center">File</th>
                            <th class="px-4 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($books->where('tipe','ppt') as $book)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">
                                @if($book->cover)
                                    <img src="{{ asset('library/sampul/'.$book->cover) }}" class="w-14 h-18 object-cover rounded-xl shadow-xs border border-gray-200">
                                @else
                                    <div class="w-14 h-18 bg-gray-100 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 text-xs">No Cover</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800 max-w-[200px] truncate">{{ $book->title }}</td>
                            <td class="px-4 py-3 text-gray-600 font-medium">{{ $book->mapel->mata_pelajaran ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $book->topik->nama_topik ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">{{ Str::limit($book->topik->deskripsi ?? '-', 50) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($book->file)
                                    <a href="{{ asset('library/file/'.$book->file) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#0071BC] bg-blue-50 hover:bg-blue-100 border border-blue-200/60 transition-colors">
                                        <i class="fa-solid fa-eye text-[11px]"></i>
                                        <span>Lihat</span>
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="openEditModal('{{ $book->id }}', 'ppt', @js($book->title), @js($book->description), '{{ $book->kelas_id }}', '{{ $book->mapel_id }}', '{{ $book->bab_id ?? '' }}', '{{ $book->topik_materi_id ?? '' }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 transition-colors cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                        <span>Edit</span>
                                    </button>
                                    <form action="{{ route('library.delete',$book->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus PPT ini?')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200/80 transition-colors cursor-pointer">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-400">
                                <i class="fa-solid fa-file-powerpoint text-4xl mb-2 text-gray-300 block"></i>
                                Tidak ada data PPT
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TABLE LKS ================= -->
    <div id="table_lks" class="hidden">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50/80 text-gray-700 text-xs font-semibold uppercase tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-12">No</th>
                            <th class="px-4 py-3.5 text-left">Cover</th>
                            <th class="px-4 py-3.5 text-left">Judul LKS</th>
                            <th class="px-4 py-3.5 text-left">Kelas</th>
                            <th class="px-4 py-3.5 text-left">Mapel</th>
                            <th class="px-4 py-3.5 text-left">Bab</th>
                            <th class="px-4 py-3.5 text-center">File LKPD</th>
                            <th class="px-4 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($books->where('tipe','lks') as $book)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">
                                @if($book->cover)
                                    <img src="{{ asset('library/sampul/'.$book->cover) }}" class="w-14 h-18 object-cover rounded-xl shadow-xs border border-gray-200">
                                @else
                                    <div class="w-14 h-18 bg-gray-100 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 text-xs">No Cover</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800 max-w-[200px] truncate">{{ $book->title }}</td>
                            <td class="px-4 py-3 text-gray-600 font-medium">{{ $book->kelas->kelas ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 font-medium">{{ $book->mapel->mata_pelajaran ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $book->bab->nama_bab ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($book->file)
                                    <a href="{{ asset('library/file/'.$book->file) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#0071BC] bg-blue-50 hover:bg-blue-100 border border-blue-200/60 transition-colors">
                                        <i class="fa-solid fa-file-pdf text-[11px]"></i>
                                        <span>Lihat LKPD</span>
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">Tidak ada file</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="openEditModal('{{ $book->id }}', 'lks', '{{ $book->title }}', '{{ $book->description }}', '{{ $book->kelas_id }}', '{{ $book->mapel_id }}', '{{ $book->bab_id ?? '' }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 transition-colors cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                        <span>Edit</span>
                                    </button>
                                    <form action="{{ route('library.delete',$book->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus LKS ini?')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200/80 transition-colors cursor-pointer">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-400">
                                <i class="fa-solid fa-file-lines text-4xl mb-2 text-gray-300 block"></i>
                                Tidak ada data LKPD
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TABLE VIDEO ================= -->
    <div id="table_video" class="hidden">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50/80 text-gray-700 text-xs font-semibold uppercase tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-12">No</th>
                            <th class="px-4 py-3.5 text-left">Cover</th>
                            <th class="px-4 py-3.5 text-left">Judul</th>
                            <th class="px-4 py-3.5 text-left">Deskripsi</th>
                            <th class="px-4 py-3.5 text-left">Kelas</th>
                            <th class="px-4 py-3.5 text-left">Mapel</th>
                            <th class="px-4 py-3.5 text-left">Bab</th>
                            <th class="px-4 py-3.5 text-center">Video</th>
                            <th class="px-4 py-3.5 text-center">Upload</th>
                            <th class="px-4 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="table_video_body" class="divide-y divide-gray-100">
                    {{-- VIDEO YANG MASIH PROSES UPLOAD --}}
                    @foreach($uploadingVideos as $upload)
                        <tr id="upload-row-{{ $upload->upload_id }}" class="bg-amber-50/50">
                            <td class="px-4 py-3 text-center">-</td>
                            <td class="px-4 py-3">
                                <div class="w-14 h-18 bg-gray-200 rounded-xl flex items-center justify-center text-xs text-gray-500 font-medium">
                                    Upload...
                                </div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $upload->file_name }}</td>
                            <td class="px-4 py-3 max-w-xs text-gray-400 text-xs">-</td>
                            <td class="px-4 py-3 text-gray-400">-</td>
                            <td class="px-4 py-3 text-gray-400">-</td>
                            <td class="px-4 py-3 text-gray-400">-</td>
                            <td class="px-4 py-3 text-center text-xs text-blue-600 font-medium">Sedang diproses</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $progress = $upload->total_chunks
                                        ? round(($upload->uploaded_chunks / $upload->total_chunks) * 100)
                                        : 0;
                                @endphp
                                <div class="w-36 bg-gray-200 rounded-full h-2.5 overflow-hidden mx-auto">
                                    <div id="progress-bar-{{ $upload->upload_id }}"
                                        class="bg-[#0071BC] h-full rounded-full text-white text-[9px] flex items-center justify-center font-bold"
                                        style="width: {{ $progress }}%;">
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500 mt-1 block">{{ $progress }}%</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span id="status-{{ $upload->upload_id }}" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    Uploading...
                                </span>
                            </td>
                        </tr>
                    @endforeach

                    @forelse($books->where('tipe','video') as $book)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 text-center text-gray-500 font-medium">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $cover = $book->cover;
                                @endphp
                                @if($cover)
                                    @if(Str::startsWith($cover, 'http'))
                                        <img src="{{ $cover }}" class="w-14 h-18 object-cover rounded-xl shadow-xs border border-gray-200">
                                    @else
                                        <img src="{{ asset('library/sampul/'.$cover) }}" class="w-14 h-18 object-cover rounded-xl shadow-xs border border-gray-200">
                                    @endif
                                @else
                                    <div class="w-14 h-18 bg-gray-100 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 text-xs">No Cover</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800 max-w-[200px] truncate">{{ $book->title }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">{{ Str::limit($book->description ?? '-', 80) }}</td>
                            <td class="px-4 py-3 text-gray-600 font-medium">{{ $book->kelas->kelas ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 font-medium">{{ $book->mapel->mata_pelajaran ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $book->bab->nama_bab ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ $book->file }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#0071BC] bg-blue-50 hover:bg-blue-100 border border-blue-200/60 transition-colors">
                                    <i class="fa-solid fa-play text-[11px]"></i>
                                    <span>Lihat Video</span>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div id="upload_waiting_{{ $book->id }}" class="hidden">
                                    <div class="text-xs mb-1">
                                        <span id="status_{{ $book->id }}" class="font-medium text-gray-600">Menunggu...</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                        <div id="progress_{{ $book->id }}" class="bg-[#0071BC] h-2 rounded-full transition-all duration-300" style="width:0%"></div>
                                    </div>
                                    <div class="text-[11px] mt-2 text-gray-500 space-y-0.5">
                                        <div>Upload : <span id="uploaded_{{ $book->id }}">0 MB</span></div>
                                        <div>Total : <span id="total_{{ $book->id }}">0 MB</span></div>
                                        <div>Speed : <span id="speed_{{ $book->id }}">0 MB/s</span></div>
                                        <div>ETA : <span id="eta_{{ $book->id }}">--</span></div>
                                    </div>
                                </div>
                                @if($book->file)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                        <i class="fa-solid fa-check text-[10px]"></i> Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="openEditModal('{{ $book->id }}', 'video', '{{ $book->title }}', '{{ $book->description }}', '{{ $book->kelas_id }}', '{{ $book->mapel_id }}', '{{ $book->bab_id ?? '' }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 transition-colors cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                        <span>Edit</span>
                                    </button>
                                    <form action="{{ route('library.delete',$book->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Hapus video ini?')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200/80 transition-colors cursor-pointer">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-12 text-gray-400">
                                <i class="fa-solid fa-film text-4xl mb-2 text-gray-300 block"></i>
                                Tidak ada data Video
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
</main>
</div>
</div>

<dialog id="modal_pilih_tipe" class="modal">
    <div class="modal-box bg-white rounded-2xl max-w-md p-6 border border-gray-100 shadow-xl">
        <h3 class="font-bold text-lg text-gray-800 text-center mb-1">Pilih Tipe Library</h3>
        <p class="text-xs text-gray-500 text-center mb-5">Pilih jenis materi pembelajaran yang ingin Anda tambahkan</p>
        <div class="grid grid-cols-2 gap-3.5">
            <button type="button" onclick="pilihTipe('buku')" 
                class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 hover:border-[#0071BC] hover:bg-blue-50/50 transition-all text-gray-700 hover:text-[#0071BC] group cursor-pointer shadow-xs">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0071BC] flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-book"></i>
                </div>
                <span class="font-semibold text-sm">Buku</span>
            </button>

            <button type="button" onclick="pilihTipe('ppt')" 
                class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 hover:border-amber-500 hover:bg-amber-50/50 transition-all text-gray-700 hover:text-amber-600 group cursor-pointer shadow-xs">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-file-powerpoint"></i>
                </div>
                <span class="font-semibold text-sm">PPT</span>
            </button>

            <button type="button" onclick="pilihTipe('lks')" 
                class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all text-gray-700 hover:text-emerald-600 group cursor-pointer shadow-xs">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <span class="font-semibold text-sm">LKPD</span>
            </button>

            <button type="button" onclick="pilihTipe('video')" 
                class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 hover:border-rose-500 hover:bg-rose-50/50 transition-all text-gray-700 hover:text-rose-600 group cursor-pointer shadow-xs">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-video"></i>
                </div>
                <span class="font-semibold text-sm">Video</span>
            </button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- ================= MODAL TAMBAH ================= -->
<dialog id="modal_add_book" class="modal">
    <div class="modal-box w-[95%] max-w-2xl max-h-[88vh] overflow-y-auto p-0 rounded-2xl border border-gray-100 shadow-2xl">
        <!-- HEADER -->
        <div class="bg-gradient-to-r from-[#0071BC] to-[#005B94] px-6 py-5 text-white">
            <h3 class="text-xl font-bold text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-book-bookmark"></i>
                <span>Tambah Library</span>
            </h3>
            <p class="text-center text-xs text-blue-100 mt-1"> Tambahkan materi pembelajaran dengan lengkap </p>
        </div>
        <form
            id="libraryForm"
            action="{{ route('library.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="p-6">
            @csrf
            <!-- AUTO COVER -->
            <input type="hidden" name="auto_cover" id="auto_cover">
            
            <div class="grid md:grid-cols-2 gap-4">
                <!-- TITLE -->
                <div id="title_wrapper" class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Judul Materi <span class="text-red-500">*</span> </label>
                    <input type="text" id="title" name="title" required class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20">
                </div>
                
                <!-- DESC -->
                <div id="wrapper_description" class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Deskripsi <span class="text-red-500">*</span> </label>
                    <textarea name="description" required rows="3" class="w-full bg-white border border-gray-300 rounded-xl p-3 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20"></textarea>
                </div>
                
                <!-- KELAS -->
                <div id="wrapper_kelas">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Kelas <span class="text-red-500">*</span> </label>
                    <select name="kelas_id" id="kelas_add" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                        <option value="">Pilih Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- MAPEL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Mata Pelajaran <span class="text-red-500">*</span> </label>
                    <select id="mapel_add" name="mapel_id" required class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                        <option value="">Pilih Mapel</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}">{{ $mapel->mata_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- BAB -->
                <div id="wrapper_bab">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Bab Materi <span class="text-red-500">*</span> </label>
                    <select id="bab_add" name="bab_id" required class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                        <option value="">Pilih Bab</option>
                        @foreach($babs as $bab)
                            <option value="{{ $bab->id }}" data-mapel="{{ $bab->mapel_id }}">
                                {{ $bab->nama_bab }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- TIPE -->
                <input type="hidden" id="tipe_library" name="tipe">
                
                <!-- TOPIK -->
                <div class="md:col-span-2 hidden" id="topik_wrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Topik Materi </label>
                    <select id="topik_add" name="topik_materi_id" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                        <option value="">Pilih Topik</option>
                        @foreach($topiks as $topik)
                            <option value="{{ $topik->id }}" data-mapel="{{ $topik->mapel_id }}" data-deskripsi="{{ $topik->deskripsi }}">
                                {{ $topik->nama_topik }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- DESKRIPSI TOPIK -->
                <div class="md:col-span-2 hidden" id="topik_deskripsi_wrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Deskripsi Topik </label>
                    <textarea id="topik_deskripsi" readonly rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm text-gray-600 outline-none"></textarea>
                </div>
                
                <!-- JUDUL OTOMATIS -->
                <div class="md:col-span-2 hidden" id="title_auto_wrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Judul Materi </label>
                    <input type="text" id="title_auto" readonly class="w-full h-11 bg-gray-50 border border-gray-200 rounded-xl px-4 text-sm font-medium text-gray-600 outline-none">
                </div>
                
                <!-- FILE -->
                <div id="file_wrapper" class="md:col-span-2 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Upload File </label>
                    <input type="file" name="file" id="file_pdf" required class="file-input file-input-bordered file-input-primary w-full rounded-xl">
                    <small class="text-gray-500 text-xs mt-1 block"> Format: PDF / PPT / DOC </small>
                </div>
                
                <!-- ================= VIDEO INPUT (LINK + FILE + PROGRESS) ================= -->
                <div id="video_wrapper" class="md:col-span-2 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2"> Input Video </label>
                    <!-- SWITCH MODE -->
                    <div class="flex gap-2 mb-3">
                        <button type="button" class="px-3.5 py-1.5 text-xs font-semibold bg-[#0071BC] text-white rounded-lg transition-all" onclick="toggleVideoInputMode('url')"> 🔗 Link </button>
                        <button type="button" class="px-3.5 py-1.5 text-xs font-semibold bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-all" onclick="toggleVideoInputMode('file')"> 📁 File </button>
                    </div>
                    
                    <!-- ================= LINK INPUT ================= -->
                    <div id="video_url_box">
                        <input type="url" name="video_url" id="video_url" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20" placeholder="https://youtube.com / drive link">
                        <small class="text-gray-500 text-xs mt-1 block"> Gunakan link YouTube atau Google Drive </small>
                    </div>
                    
                    <!-- ================= FILE INPUT ================= -->
                    <div id="video_file_box" class="hidden">
                        <input type="file" name="video_file" id="video_file" accept="video/*" class="file-input file-input-bordered file-input-primary w-full rounded-xl">
                        <small class="text-gray-500 text-xs mt-1 block"> Upload file video (mp4, mov, dll) </small>
                        
                        <!-- ================= COVER TIMESTAMP PICKER ================= -->
                        <div id="cover_time_box" class="mt-4 hidden bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label class="text-xs font-medium text-gray-700 block mb-1"> Pilih detik cover (thumbnail) </label>
                            <input type="range" id="cover_time" min="0" value="1" step="1" class="w-full accent-[#0071BC]">
                            <div class="text-xs text-gray-500 mt-1"> Detik: <span id="cover_time_label" class="font-bold text-[#0071BC]">1</span> </div>
                            <button type="button" onclick="captureVideoCover()" class="mt-2 text-xs bg-[#0071BC] hover:bg-[#005B94] text-white px-3.5 py-1.5 rounded-lg font-medium transition-all"> 🎬 Ambil Cover </button>
                        </div>
                    </div>
                </div>
                
                <!-- COVER PREVIEW -->
                <div class="md:col-span-2 flex justify-center">
                    <div class="text-center">
                        <img id="cover_preview" class="mt-2 w-40 h-56 object-cover rounded-xl hidden border shadow">
                        <p class="text-xs text-gray-500 mt-2"> Preview thumbnail otomatis </p>
                    </div>
                </div>
            </div>
            
            <!-- BUTTON -->
            <div class="flex justify-end gap-3 mt-7 pt-4 border-t border-gray-100">
                <button type="button" id="btnTambahTopik" onclick="modal_add_topik.showModal()" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2.5 rounded-xl transition-all shadow-xs text-sm"> ➕ Topik </button>
                <button type="button" onclick="modal_add_book.close()" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium transition-all text-sm cursor-pointer"> Batal </button>
                <button
                    id="btnSaveLibrary"
                    type="submit"
                    class="bg-[#0071BC] hover:bg-[#005B94] text-white font-medium px-6 py-2.5 rounded-xl shadow-xs hover:shadow transition-all text-sm cursor-pointer">
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
    <div class="modal-box max-w-3xl rounded-2xl border border-gray-100 shadow-2xl p-6">
        <form action="{{ route('library.topik.store') }}" method="POST">
            @csrf
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-gray-100">
                <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-folder-plus text-[#0071BC]"></i>
                    <span>Tambah Topik Materi</span>
                </h3>
                <button type="button" onclick="modal_add_topik.close()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Mata Pelajaran <span class="text-red-500">*</span> </label>
                    <select id="topik_mapel" name="mapel_id" required class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                        <option value="">Pilih Mapel</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}"> {{ $mapel->mata_pelajaran }} </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="my-5 border-gray-200">
            <div id="topikContainer">
                <div class="grid grid-cols-12 gap-2 mb-3 topik-row items-center">
                    <input type="text" name="topik[0][nama_topik]" placeholder="Nama Topik" required class="w-full h-10 border border-gray-300 rounded-xl px-3 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] col-span-5">
                    <input type="text" name="topik[0][deskripsi]" placeholder="Deskripsi Topik" class="w-full h-10 border border-gray-300 rounded-xl px-3 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] col-span-6">
                    <button type="button" onclick="addTopikRow()" class="h-10 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl col-span-1 flex items-center justify-center font-bold text-lg cursor-pointer"> + </button>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="button" onclick="modal_add_topik.close()" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium transition-all text-sm cursor-pointer"> Batal </button>
                <button type="submit" class="bg-[#0071BC] hover:bg-[#005B94] text-white font-medium px-5 py-2.5 rounded-xl shadow-xs hover:shadow transition-all text-sm cursor-pointer"> Simpan Topik </button>
            </div>
        </form>
    </div>
</dialog>

<!-- ================= MODAL EDIT ================= -->
<dialog id="modal_edit_book" class="modal">
    <div class="modal-box w-[95%] max-w-2xl max-h-[88vh] overflow-y-auto p-0 rounded-2xl border border-gray-100 shadow-2xl">
        <div class="bg-gradient-to-r from-[#0071BC] to-[#005B94] px-6 py-5 text-white">
            <h3 class="text-xl font-bold text-center flex items-center justify-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Library</span>
            </h3>
            <p class="text-center text-xs text-blue-100 mt-1"> Perbarui data materi pembelajaran </p>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="tipe" id="edit_tipe">
            <input type="hidden" name="auto_cover" id="auto_cover">
            
            <div class="grid md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Judul *</label>
                    <input id="edit_title" name="title" required class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi *</label>
                    <textarea id="edit_description" name="description" rows="3" class="w-full bg-white border border-gray-300 rounded-xl p-3 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Kelas *</label>
                    <select id="edit_kelas" name="kelas_id" required class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mapel *</label>
                    <select id="edit_mapel" name="mapel_id" required class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}">{{ $mapel->mata_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Bab *</label>
                    <select id="edit_bab" name="bab_id" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                        @foreach($babs as $bab)
                            <option value="{{ $bab->id }}" data-mapel="{{ $bab->mapel_id }}">
                                {{ $bab->nama_bab }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="file_wrapper_edit">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">File Baru</label>
                    <input id="file_pdf_edit" type="file" name="file" class="file-input file-input-bordered file-input-primary w-full rounded-xl">
                </div>
                <div id="video_wrapper_edit" class="hidden md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Link Video</label>
                    <input id="video_url_edit" type="text" name="video_url" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20">
                </div>
                <!-- TOPIK -->
                <div class="md:col-span-2" id="edit_topik_wrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Topik Materi </label>
                    <select id="edit_topik_add" name="topik_materi_id" class="w-full h-11 bg-white border border-gray-300 rounded-xl px-4 pr-10 text-sm font-medium text-gray-700 outline-none transition-all duration-200 hover:border-gray-400 focus:border-[#0071BC] focus:ring-2 focus:ring-[#0071BC]/20 cursor-pointer">
                        @foreach($topiks as $t)
                            <option value="{{ $t->id }}" data-deskripsi="{{ $t->deskripsi }}" data-kelas="{{ $t->kelas_id }}" data-mapel="{{ $t->mapel_id }}">
                                {{ $t->nama_topik }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- DESKRIPSI TOPIK -->
                <div class="md:col-span-2" id="edit_topik_deskripsi_wrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Deskripsi Topik </label>
                    <input type="text" id="edit_topik_deskripsi" readonly class="w-full h-11 bg-gray-50 border border-gray-200 rounded-xl px-4 text-sm text-gray-600 outline-none" />
                </div>
                <!-- JUDUL OTOMATIS -->
                <div class="md:col-span-2" id="edit_title_auto_wrapper">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5"> Judul Materi </label>
                    <input readonly id="edit_title_auto" name="title" class="w-full h-11 bg-gray-50 border border-gray-200 rounded-xl px-4 text-sm font-medium text-gray-600 outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-7 pt-4 border-t border-gray-100">
                <button type="button" onclick="modal_edit_book.close()" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium transition-all text-sm cursor-pointer"> Batal </button>
                <button type="submit" class="bg-[#0071BC] hover:bg-[#005B94] text-white font-medium px-6 py-2.5 rounded-xl shadow-xs hover:shadow transition-all text-sm cursor-pointer"> 🚀 Update </button>
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
                btn.classList.remove('border-[#0071BC]', 'text-[#0071BC]', 'border-blue-500', 'text-blue-600', 'font-semibold');
                btn.classList.add('border-transparent', 'text-gray-500');
                const badge = btn.querySelector('span:last-child');
                if (badge) {
                    badge.className = 'bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-bold ml-1';
                }
            }
        });

        const activeTable = document.getElementById('table_' + tab);
        const activeBtn = document.getElementById('tab_' + tab);
        if (activeTable) activeTable.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent', 'text-gray-500');
            activeBtn.classList.add('border-[#0071BC]', 'text-[#0071BC]', 'font-semibold');
            const badge = activeBtn.querySelector('span:last-child');
            if (badge) {
                badge.className = 'bg-blue-50 text-[#0071BC] text-xs px-2 py-0.5 rounded-full font-bold ml-1';
            }
        }
    }





    document.getElementById('edit_topik_add').addEventListener('change', function () {

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

        document.getElementById('edit_topik_deskripsi').value = opt.dataset.deskripsi || '';

        

        if (this.value) {

            let tipe = document.getElementById('tipe_library').value;

            fetch(`/administrator/library/get-series/${this.value}?tipe=${tipe}`)

                .then(res => res.json())

                .then(series => {

                    let autoTitle = 'Series Materi ' + series.next;

                    document.getElementById('edit_title_auto').value = autoTitle;

                });

        } else {

            document.getElementById('edit_title_auto').value = '';

        }

    });



    



    document.getElementById('edit_kelas').addEventListener('change', function(){

        let kelasId = this.value;

        let mapelId = document.getElementById('edit_mapel').value;

        if(!kelasId || !mapelId) return;

        

        fetch(`/administrator/library/get-topik?kelas_id=${kelasId}&mapel_id=${mapelId}`)

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

                document.getElementById('edit_topik_deskripsi').value = '';

            });

    });



    document.getElementById('edit_mapel')?.addEventListener('change', function () {

        let mapelId = this.value;

        let kelasId = document.getElementById('edit_kelas').value;

        fetch(`/administrator/library/get-topik?kelas_id=${kelasId}&mapel_id=${mapelId}`)

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
document.getElementById('edit_tipe').value = tipe;

// ================= LKS & VIDEO =================
if (tipe === "lks" || tipe === "video") {

    loadMapel(kelas, "edit_mapel", mapel);

    setTimeout(() => {

        document.getElementById("edit_mapel")
            .dispatchEvent(new Event("change"));

        setTimeout(() => {
            document.getElementById("edit_bab").value = bab;
        }, 200);

    }, 300);

}
// ================= BUKU & PPT =================
else {

    document.getElementById("edit_mapel").value = mapel;
}

document.getElementById("edit_bab").value = bab;

        

        // 🔥 SKIP TOPIK untuk LKS & VIDEO

        if (tipe === 'lks' || tipe === 'video') {

            modal.showModal();

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

                    document.getElementById('edit_title_auto').value = 'Series Materi ' + series.next;

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

                opt.style.display = opt.dataset.mapel == mapel.value ? 'block' : 'none';

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

                document.getElementById('topik_wrapper').classList.remove('hidden');

            });

    }



function toggleEditType(tipe) {

    const fileWrapper = document.getElementById("file_wrapper_edit");
    const videoWrapper = document.getElementById("video_wrapper_edit");

    const titleInput = document.getElementById("edit_title");
    const titleAuto  = document.getElementById("edit_title_auto");

    const titleWrapper = titleInput.closest(".md\\:col-span-2");
    const descriptionWrapper = document.getElementById("edit_description").closest(".md\\:col-span-2");

    const kelasWrapper = document.getElementById("edit_kelas").parentElement;
    const mapelWrapper = document.getElementById("edit_mapel").parentElement;
    const babWrapper = document.getElementById("edit_bab").parentElement;

    const topikWrapper = document.getElementById("edit_topik_wrapper");
    const topikDeskripsiWrapper = document.getElementById("edit_topik_deskripsi_wrapper");
    const titleAutoWrapper = document.getElementById("edit_title_auto_wrapper");

    // reset tampilan
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

    // reset name title
    titleInput.removeAttribute("name");
    titleAuto.removeAttribute("name");

    // ================= BUKU / PPT =================
    if (["buku", "ppt"].includes(tipe)) {

        titleAuto.setAttribute("name", "title");

        toggleRequired(document.getElementById('edit_kelas'), false);

        mapelWrapper.style.display = "block";
        topikWrapper.style.display = "block";
        topikDeskripsiWrapper.style.display = "block";
        titleAutoWrapper.style.display = "block";
    }

    // ================= LKS =================
    else if (tipe === "lks") {

        titleInput.setAttribute("name", "title");

        titleWrapper.style.display = "block";
        kelasWrapper.style.display = "block";
        mapelWrapper.style.display = "block";
        babWrapper.style.display = "block";
        fileWrapper.style.display = "block";

        toggleRequired(document.getElementById('edit_kelas'), true);
    }

    // ================= VIDEO =================
    else if (tipe === "video") {

        titleInput.setAttribute("name", "title");

        titleWrapper.style.display = "block";
        kelasWrapper.style.display = "block";
        mapelWrapper.style.display = "block";
        babWrapper.style.display = "block";
        videoWrapper.style.display = "block";

        toggleRequired(document.getElementById('edit_kelas'), true);
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



    document.getElementById('edit_topik_add').addEventListener('change', function () {

        let tipe = document.getElementById('edit_tipe').value;

        // 🔥 SKIP

        if (tipe === 'lks' || tipe === 'video') return;

        let opt = this.options[this.selectedIndex];

        document.getElementById('edit_topik_deskripsi').value = opt.dataset.deskripsi || '';

    });



    //================load topik============

    function loadTopikMateri() {

        let kelasId = document.querySelector('#modal_add_book select[name="kelas_id"]')?.value;

        let mapelId = document.getElementById('mapel_add')?.value;

        if (!kelasId || !mapelId) return;

        

        fetch(`/administrator/library/get-topik?kelas_id=${kelasId}&mapel_id=${mapelId}`)

            .then(response => response.json())

            .then(data => {

                const topikSelect = document.getElementById('topik_add');

                topikSelect.innerHTML = '<option value="">Pilih Topik</option>';

                data.forEach(topik => {

                    const option = document.createElement('option');

                    option.value = topik.id;

                    option.textContent = topik.nama_topik;

                    // ✅ penting: simpan mapel untuk filter ulang kalau perlu

                    option.dataset.mapel = topik.mapel_id;

                    option.dataset.deskripsi = topik.deskripsi ?? '';

                    topikSelect.appendChild(option);

                });

                

                // ✅ FILTER DI SINI (langsung setelah load)

                topikSelect.querySelectorAll('option').forEach(opt => {

                    if (!opt.dataset.mapel) return;

                    opt.style.display = opt.dataset.mapel == mapelId ? 'block' : 'none';

                });

                // reset value

                topikSelect.value = "";

            })

            .catch(err => {

                console.log(err);

            });

    }



    function filterTopik(mapelId, topikSelect) {

        topikSelect.querySelectorAll('option').forEach(opt => {

            if (!opt.dataset.mapel) return;

            opt.style.display = opt.dataset.mapel == mapelId ? 'block' : 'none';

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



    document.getElementById('mapel_add')?.addEventListener('change', function () {

        filterTopik(this.value, document.getElementById('topik_add'));

    });



    // ================= FILTER BAB =================

    function filterBab(mapelId, babSelect){

        babSelect.querySelectorAll('option').forEach(opt => {

            if(!opt.dataset.mapel) return;

            opt.style.display = opt.dataset.mapel == mapelId ? 'block' : 'none';

        });

        babSelect.value = "";

    }



    document.getElementById('mapel_add')?.addEventListener('change', function () {

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

                            canvasContext: context,

                            viewport: viewport

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

function resetSelect(select, placeholder) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
}

function loadMapel(kelasId, target = "mapel_add", selected = null) {

    const select = document.getElementById(target);

    resetSelect(select, "Pilih Mata Pelajaran");

    if (!kelasId) return;

    fetch(`/kelas/${kelasId}/mapel`)
        .then(r => r.json())
        .then(data => {

            data.forEach(item => {

                let opt = document.createElement("option");

                opt.value = item.id;
                opt.textContent = item.mata_pelajaran;

                if (selected == item.id) {
                    opt.selected = true;
                }

                select.appendChild(opt);

            });

            select.dispatchEvent(new Event("change"));

        });

}

document.getElementById("kelas_add")?.addEventListener("change", function () {

    const tipe = document.getElementById("tipe_library").value;

    // Buku & PPT tetap menggunakan mekanisme lama
    if (tipe === "buku" || tipe === "ppt") {
        return;
    }

    loadMapel(this.value);

});

document.getElementById("mapel_add")?.addEventListener("change", function () {

    const tipe = document.getElementById("tipe_library").value;

    // Jangan ganggu Topik
    if (tipe === "buku" || tipe === "ppt") {
        return;
    }

    fetch(`/mapel/${this.value}/bab`)
        .then(res => res.json())
        .then(data => {

            const bab = document.getElementById("bab_add");

            bab.innerHTML = '<option value="">Pilih Bab</option>';

            data.forEach(item => {

                bab.innerHTML += `
                    <option value="${item.id}">
                        ${item.nama_bab}
                    </option>
                `;

            });

        });

});

document.getElementById("edit_kelas")?.addEventListener("change", function () {

    const tipe = document.getElementById("edit_tipe").value;

    if (tipe == "buku" || tipe == "ppt") {
        return;
    }

    loadMapel(this.value, "edit_mapel");

});

document.getElementById("edit_mapel")?.addEventListener("change", function () {

    const tipe = document.getElementById("edit_tipe").value;

    if (tipe == "buku" || tipe == "ppt") {
        return;
    }

    fetch(`/mapel/${this.value}/bab`)
        .then(r => r.json())
        .then(data => {

            const bab = document.getElementById("edit_bab");

            bab.innerHTML = '<option value="">Pilih Bab</option>';

            data.forEach(item => {

                bab.innerHTML += `
                    <option value="${item.id}">
                        ${item.nama_bab}
                    </option>
                `;

            });

        });

});

    // ================= VIDEO / FILE TOGGLE =================

    document.addEventListener("DOMContentLoaded", function () {

        const tipe = document.getElementById("tipe_library");

        const fileWrapper = document.getElementById("file_wrapper");

        const fileInput = document.getElementById("file_pdf");

        const videoUrl = document.getElementById("video_url");

        const videoWrapper = document.getElementById("video_wrapper");

        const autoCoverInput = document.getElementById("auto_cover");

        const coverPreview = document.getElementById("cover_preview");

        

        // ================= FORM LAMA =================

        const titleField = document.querySelector('input[name="title"]')?.closest('.md\\:col-span-2');

        const descField = document.querySelector('textarea[name="description"]')?.closest('.md\\:col-span-2');

        const babField = document.getElementById('bab_add')?.closest('div');

        const kelasField = document.querySelector('select[name="kelas_id"]')?.closest('div');

        const mapelField = document.getElementById('mapel_add')?.closest('div');

        

        // ================= FORM BARU =================

        const topikWrapper = document.getElementById("topik_wrapper");

        const topikDescWrapper = document.getElementById("topik_deskripsi_wrapper");

        const titleAutoWrapper = document.getElementById("title_auto_wrapper");

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

            [ kelasWrapper, mapelWrapper, babWrapper, topikWrapper, topikDescWrapper, titleAutoWrapper, fileWrapper, videoWrapper ].forEach(el => el?.classList.add("hidden"));

            

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

                titleField?.classList.add("hidden");

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

                titleField?.classList.add("hidden");

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

        if (tipe.value === "buku" || tipe.value === "ppt" || tipe.value === "lks") {

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

            if (val === "buku" || val === "ppt" || val === "lks") {

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

            if (url.includes("youtube.com") || url.includes("youtu.be")) {

                let videoId = "";

                if (url.includes("watch?v=")) {

                    videoId = url.split("v=")[1].split("&")[0];

                } else if (url.includes("youtu.be/")) {

                    videoId = url.split("youtu.be/")[1].split("?")[0];

                }

                if (videoId) {

                    thumbnail = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;

                }

            } else if (url.includes("drive.google.com")) {

                let match = url.match(/\/d\/(.*?)\//);

                if (match) {

                    thumbnail = `https://drive.google.com/thumbnail?id=${match[1]}&sz=w1000`;

                }

            }

            if (thumbnail) {

                autoCoverInput.value = thumbnail;

                coverPreview.src = thumbnail;

                coverPreview.classList.remove("hidden");

            }

            document.querySelector('#modal_add_book select[name="kelas_id"]')?.addEventListener('change', loadTopikMateri);

            document.getElementById('mapel_add')?.addEventListener('change', loadTopikMateri);

        });

    });



    let topikIndex = 1;

    function addTopikRow() {

        const row = `<div class="grid grid-cols-12 gap-2 mb-3 topik-row items-center">

            <input type="text" name="topik[${topikIndex}][nama_topik]" placeholder="Nama Topik" class="w-full h-10 border border-gray-300 rounded-xl px-3 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] col-span-5">

            <input type="text" name="topik[${topikIndex}][deskripsi]" placeholder="Deskripsi Topik" class="w-full h-10 border border-gray-300 rounded-xl px-3 text-sm font-medium text-gray-700 outline-none focus:border-[#0071BC] col-span-6">

            <button type="button" class="h-10 bg-red-500 hover:bg-red-600 text-white rounded-xl col-span-1 flex items-center justify-center font-bold text-lg cursor-pointer" onclick="this.closest('.topik-row').remove()"> - </button>

        </div>`;

        document.getElementById('topikContainer').insertAdjacentHTML('beforeend', row);

        topikIndex++;

    }



    function openTambahLibrary() {

        document.getElementById('modal_pilih_tipe').showModal();

    }



    function pilihTipe(tipe) {

        const tipeSelect = document.getElementById("tipe_library");

        tipeSelect.value = tipe;

        tipeSelect.dispatchEvent(new Event("change"));

        

        if (tipe === "buku" || tipe === "ppt") {

            document.getElementById("btnTambahTopik")?.classList.remove("hidden");

        } else {

            document.getElementById("btnTambahTopik")?.classList.add("hidden");

        }

        document.getElementById("modal_pilih_tipe").close();

        document.getElementById("modal_add_book").showModal();

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



    document.getElementById("libraryForm").addEventListener("submit", function () {

    console.log(document.getElementById("tipe_library").value);

    });



    document.addEventListener('DOMContentLoaded', function() {

        document.getElementById('mapel_add')?.addEventListener('change', loadTopikMateri);

    });



    function toggleVideoInputMode(mode) {

        const urlBox = document.getElementById('video_url_box');

        const fileBox = document.getElementById('video_file_box');

        const urlInput = document.getElementById('video_url');

        const fileInput = document.getElementById('video_file');

        

        if (mode === 'url') {

            urlBox.classList.remove('hidden');

            fileBox.classList.add('hidden');

            urlInput.required = true;

            fileInput.required = false;

        } else {

            urlBox.classList.add('hidden');

            fileBox.classList.remove('hidden');

            urlInput.required = false;

            fileInput.required = true;

            resetVideoProgress();

        }

    }



    function resetVideoProgress() {

        document.getElementById('video_progress_box').classList.add('hidden');

        document.getElementById('video_progress_bar').style.width = '0%';

        document.getElementById('video_percent').innerText = '0%';

        document.getElementById('video_success').classList.add('hidden');

        document.getElementById('video_error').classList.add('hidden');

        document.getElementById('video_retry_btn').classList.add('hidden');

    }



    function retryVideoUpload() {

        document.getElementById('video_file').click();

    }



    function cancelVideoUpload(){



    if(currentUploadXHR){



        currentUploadXHR.abort();



    }



}



    let videoElement = document.createElement("video");

    let videoURLObject = null;



    // ketika user pilih file video

    document.getElementById("video_file")?.addEventListener("change", function (e) {

        const file = e.target.files[0];

        if (!file) return;

        videoURLObject = URL.createObjectURL(file);

        videoElement.src = videoURLObject;

        videoElement.preload = "metadata";

        videoElement.onloadedmetadata = function () {

            const duration = Math.floor(videoElement.duration);

            const slider = document.getElementById("cover_time");

            const box = document.getElementById("cover_time_box");

            slider.max = duration;

            slider.value = 1;

            document.getElementById("cover_time_label").innerText = "1";

            box.classList.remove("hidden");

        };

    });



    // update label slider

    document.getElementById("cover_time")?.addEventListener("input", function () {

        document.getElementById("cover_time_label").innerText = this.value;

    });



    // ambil frame video jadi cover

    function captureVideoCover() {

        const time = parseFloat(document.getElementById("cover_time").value);

        videoElement.currentTime = time;

        videoElement.onseeked = function () {

            const canvas = document.createElement("canvas");

            canvas.width = videoElement.videoWidth;

            canvas.height = videoElement.videoHeight;

            const ctx = canvas.getContext("2d");

            ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

            const base64 = canvas.toDataURL("image/jpeg");

            document.getElementById("auto_cover").value = base64;

            // preview

            const preview = document.getElementById("cover_preview");

            preview.src = base64;

            preview.classList.remove("hidden");

        };

    }



   /*=============================================

=            AJAX Upload Video                =

=============================================*/



let currentUploadXHR = null;

let uploadStartTime = 0;

let lastForm = null;




function formatBytes(bytes) {

    return (bytes / 1024 / 1024).toFixed(2) + " MB";

}



function formatTime(sec) {

    sec = Math.max(0, Math.floor(sec));

    let m = Math.floor(sec / 60);

    let s = sec % 60;

    return m + "m " + s + "s";

}



function uploadLibraryVideo(form) {



    const xhr = new XMLHttpRequest();



    const formData = new FormData(form);



    const uploadId = Date.now();



    createTemporaryVideoRow(uploadId, formData.get("title"));



    const btn = document.getElementById("btnSaveLibrary");



    btn.innerHTML = "Uploading...";



    showTab("video");



    let uploadStartTime = 0;



    xhr.upload.addEventListener("progress", function (e) {



        if (!e.lengthComputable) return;



        if (uploadStartTime === 0) {

            uploadStartTime = Date.now();

        }



        const progress = Math.round((e.loaded / e.total) * 100);



        const elapsed = (Date.now() - uploadStartTime) / 1000;



        const speed = elapsed > 0 ? e.loaded / elapsed : 0;



        const eta = speed > 0

            ? (e.total - e.loaded) / speed

            : 0;



        updateTableProgress(

            uploadId,

            progress,

            "Uploading...",

            formatBytes(e.loaded),

            formatBytes(e.total),

            (speed / 1024 / 1024).toFixed(2) + " MB/s",

            formatTime(eta)

        );



    });



    xhr.onload = function () {



        console.log(xhr.getResponseHeader("content-type"));

console.log(xhr.responseURL);

console.log(xhr.responseText);



   if (xhr.status === 200) {

    const data = JSON.parse(xhr.responseText);

    updateTableProgress(
        uploadId,
        100,
        "Upload selesai"
    );

    const tempRow = document.getElementById("upload-row-" + uploadId);

    if (tempRow && data.row) {
        tempRow.outerHTML = data.row;
    }

    form.reset();

    document.getElementById("modal_add_book").close();

    btn.innerHTML = "💾 Simpan";



        // ambil row asli dari server



    } else {



        updateTableProgress(

            uploadId,

            0,

            "Upload gagal"

        );



        btn.innerHTML = "💾 Simpan";

    }



    };



    xhr.onerror = function () {



        btn.innerHTML = "💾 Simpan";



        updateTableProgress(

            uploadId,

            0,

            "Upload gagal"

        );



    };



    xhr.onabort = function () {



        btn.innerHTML = "💾 Simpan";



        updateTableProgress(

            uploadId,

            0,

            "Upload dibatalkan"

        );



    };



    xhr.open("POST", form.action, true);



xhr.setRequestHeader(

    "X-CSRF-TOKEN",

    document.querySelector('meta[name="csrf-token"]').content

);



xhr.setRequestHeader(

    "Accept",

    "application/json"

);



xhr.send(formData);



// reset form agar bisa upload lagi

form.reset();



document.getElementById("video_file").value = "";

document.getElementById("video_url").value = "";

document.getElementById("auto_cover").value = "";



document.getElementById("cover_preview").src = "";

document.getElementById("cover_preview").classList.add("hidden");



document.getElementById("cover_time_box").classList.add("hidden");



// buka lagi tombol simpan

btn.innerHTML = "💾 Simpan";

btn.disabled = false;   



// tutup modal

document.getElementById("modal_add_book").close();

}



function createTemporaryVideoRow(id, title){



    const tbody = document.querySelector("#table_video tbody");



    const tr = document.createElement("tr");



    tr.id = "upload-row-" + id;



    tr.innerHTML = `

        <td>-</td>



        <td>

            <div class="w-16 h-20 bg-gray-200 rounded"></div>

        </td>



        <td>${title}</td>



        <td>-</td>



        <td>-</td>



        <td>-</td>



        <td>Sedang upload...</td>



        <td>



            <div id="upload_waiting_${id}">



                <div class="text-xs mb-1">



                    <span id="status_${id}">Menunggu...</span>



                </div>



                <div class="w-full bg-gray-200 rounded h-2">



                    <div

                        id="progress_${id}"

                        class="bg-blue-500 h-2"

                        style="width:0%">

                    </div>



                </div>



                <div class="text-[11px] mt-2">



                    Upload :

                    <span id="uploaded_${id}">0 MB</span><br>



                    Total :

                    <span id="total_${id}">0 MB</span><br>



                    Speed :

                    <span id="speed_${id}">0 MB/s</span><br>



                    ETA :

                    <span id="eta_${id}">--</span>



                </div>



            </div>



        </td>



        <td>-</td>

    `;



    tbody.prepend(tr);



}



/*=============================================

=            Retry Upload                     =

=============================================*/



function retryVideoUpload() {



    if (lastForm) {

        uploadLibraryVideo(lastForm);

    }



}



/*=============================================

=            Cancel Upload                    =

=============================================*/



function cancelVideoUpload() {



    if (currentUploadXHR) {

        currentUploadXHR.abort();

    }



}



/*=============================================

=            Update Progress Table            =

=============================================*/



function updateTableProgress(id, progress, status, loaded="", total="", speed="", eta="") {



    const waiting = document.getElementById("upload_waiting_" + id);



    if(waiting){

        waiting.classList.remove("hidden");

    }



    const bar = document.getElementById("progress_" + id);



    if(bar){

        bar.style.width = progress + "%";

    }



    const statusEl = document.getElementById("status_" + id);



    if(statusEl){

        statusEl.innerHTML = status;

    }



    const uploaded = document.getElementById("uploaded_" + id);



    if(uploaded){

        uploaded.innerHTML = loaded;

    }



    const totalEl = document.getElementById("total_" + id);



    if(totalEl){

        totalEl.innerHTML = total;

    }



    const speedEl = document.getElementById("speed_" + id);



    if(speedEl){

        speedEl.innerHTML = speed;

    }



    const etaEl = document.getElementById("eta_" + id);



    if(etaEl){

        etaEl.innerHTML = eta;

    }



}

/*=============================================

=            Submit Form                      =

=============================================*/



document

.getElementById("libraryForm")

.addEventListener("submit", function (e) {



    const tipe = document.getElementById("tipe_library").value;



    if (tipe !== "video") {

        return;

    }



    e.preventDefault();



    uploadLibraryVideo(this);



});

const searchInput = document.getElementById("searchLibrary");

searchInput.addEventListener("input", function () {

    const keyword = this.value.toLowerCase().trim();

    document.querySelectorAll("tbody tr").forEach(row => {

        const text = row.innerText.toLowerCase();

        row.style.display = text.includes(keyword) ? "" : "none";

    });

});


</script>


<script src="{{ asset('assets/js/library/upload-manager.js') }}"></script>



