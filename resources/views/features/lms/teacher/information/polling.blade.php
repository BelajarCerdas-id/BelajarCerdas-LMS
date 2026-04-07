@include('components/sidebar-beranda', [
    'headerSideNav' => 'Polling'
])

@if (Auth::user()->role === 'Guru')
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-slate-50 min-h-screen pb-12">
        <div class="pt-8 mx-6 lg:mx-10">

            <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-transparent rounded-full -translate-y-1/2 translate-x-1/3 opacity-70 pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="w-12 h-12 bg-blue-50 text-[#0071BC] rounded-2xl flex items-center justify-center text-2xl shadow-inner border border-blue-100 shrink-0">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Manajemen Polling</h1>
                    </div>
                    <p class="text-slate-500 text-sm font-medium md:ml-16">Buat jajak pendapat interaktif untuk mengetahui opini dan tingkat pemahaman siswa.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <div class="xl:col-span-2 flex flex-col gap-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-[#0071BC]"></div>

                        <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-5">
                            <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[#0071BC]">
                                    <i class="fas fa-pen-to-square"></i>
                                </div>
                                Buat Polling Baru
                            </h2>
                        </div>

                        <form id="form-polling" class="space-y-7">
                            
                            <div class="group">
                                <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                    Target Kelas <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#0071BC] transition-colors">
                                        <i class="fas fa-users text-lg"></i>
                                    </div>
                                    <select id="poll-class" required class="w-full border-2 border-slate-200 rounded-2xl pl-12 pr-10 py-3.5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-slate-50 focus:bg-white appearance-none cursor-pointer">
                                        <option value="" disabled selected>-- Pilih Kelas Tujuan --</option>
                                        @foreach($classes ?? [] as $cls)
                                            <option value="{{ $cls->class_id }}" data-name="{{ $cls->class_name }}">Kelas {{ $cls->class_name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="group">
                                <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                    Pertanyaan Polling <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute top-4 left-4 text-slate-400 pointer-events-none group-focus-within:text-[#0071BC] transition-colors">
                                        <i class="fas fa-circle-question text-lg"></i>
                                    </div>
                                    <textarea id="poll-question" required rows="3" placeholder="Contoh: Bagaimana tingkat pemahaman kalian tentang materi hari ini?" class="w-full border-2 border-slate-200 rounded-2xl pl-12 pr-4 py-3.5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all resize-none bg-slate-50 focus:bg-white placeholder:font-normal"></textarea>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center justify-between">
                                    <span>Pilihan Jawaban <span class="text-red-500">*</span></span>
                                    <span class="text-xs font-medium text-slate-400 bg-slate-100 px-2 py-1 rounded-md">Min. 2 Pilihan</span>
                                </label>
                                
                                <div id="options-container" class="space-y-3">
                                    <div class="flex items-center gap-3 group/option">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                                <span class="text-xs font-bold w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">A</span>
                                            </div>
                                            <input type="text" required placeholder="Sangat Paham" class="poll-option w-full border-2 border-slate-200 rounded-xl pl-12 pr-4 py-3 text-sm font-medium focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-white hover:border-slate-300">
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3 group/option">
                                        <div class="relative flex-1">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                                <span class="text-xs font-bold w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">B</span>
                                            </div>
                                            <input type="text" required placeholder="Sedikit Bingung" class="poll-option w-full border-2 border-slate-200 rounded-xl pl-12 pr-4 py-3 text-sm font-medium focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-white hover:border-slate-300">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="button" onclick="addOption()" class="mt-4 w-full py-3.5 border-2 border-dashed border-slate-300 rounded-xl text-sm font-bold text-slate-500 hover:text-[#0071BC] hover:border-[#0071BC] hover:bg-blue-50/50 flex items-center justify-center gap-2 transition-all">
                                    <i class="fas fa-plus"></i> Tambah Pilihan Lain
                                </button>
                            </div>

                            <div class="pt-6 border-t border-slate-100 flex justify-end">
                                <button type="submit" id="btn-publish-poll" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-[#0071BC] to-[#005B94] hover:shadow-lg hover:shadow-blue-500/30 font-bold text-white rounded-xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                    <i class="fas fa-paper-plane"></i> Publish ke Siswa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="xl:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 flex flex-col h-[600px] sticky top-24">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 shrink-0">
                            <h3 class="font-extrabold text-slate-800 text-lg flex items-center gap-2">
                                <i class="fas fa-history text-[#0071BC]"></i> Riwayat Polling
                            </h3>
                            <span class="bg-blue-50 text-[#0071BC] text-xs font-bold px-3 py-1 rounded-lg">{{ count($polls ?? []) }} Total</span>
                        </div>
                        
                        <div class="flex flex-col gap-4 overflow-y-auto custom-scrollbar flex-1 pr-2">
                            @forelse($polls ?? [] as $poll)
                                <div class="p-5 border-2 border-slate-100 rounded-2xl bg-white hover:border-[#0071BC]/30 hover:shadow-md hover:-translate-y-0.5 transition-all cursor-default group relative overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-400"></div>
                                    <div class="pl-2">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex gap-2">
                                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-md uppercase tracking-widest flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                                </span>
                                                <span class="text-[10px] font-bold text-[#0071BC] bg-blue-50 px-2.5 py-1 rounded-md flex items-center gap-1.5">
                                                    <i class="fas fa-chalkboard"></i> {{ $poll->class_name ?? 'Semua Kelas' }}
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-md">
                                                {{ $poll->created_at->format('d M') }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700 line-clamp-2 leading-snug group-hover:text-[#0071BC] transition-colors">{{ $poll->question }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="flex-1 flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50 mt-2">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4 text-slate-300 text-3xl">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-700 mb-1">Belum Ada Polling</h4>
                                    <p class="text-xs font-medium text-slate-400">Polling yang Anda buat akan muncul di sini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endif

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    function addOption() {
        const container = document.getElementById('options-container');
        const optionCount = container.children.length; 
        const letter = alphabet[optionCount] || '?'; 
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 animate-fade-in group/option';
        div.innerHTML = `
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <span class="text-xs font-bold w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-slate-600">${letter}</span>
                </div>
                <input type="text" required placeholder="Pilihan Tambahan" class="poll-option w-full border-2 border-slate-200 rounded-xl pl-12 pr-4 py-3 text-sm font-medium focus:ring-4 focus:ring-[#0071BC]/10 focus:border-[#0071BC] outline-none transition-all bg-white hover:border-slate-300">
            </div>
            <button type="button" onclick="removeOption(this)" class="w-12 h-[46px] shrink-0 border-2 border-red-100 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white hover:border-red-500 transition-all cursor-pointer shadow-sm">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
        container.appendChild(div);
        updateLetters();
    }

    function removeOption(btn) {
        btn.parentElement.remove();
        updateLetters(); 
    }

    function updateLetters() {
        const options = document.querySelectorAll('#options-container .group\\/option');
        options.forEach((opt, index) => {
            const span = opt.querySelector('span');
            if(span) span.textContent = alphabet[index] || '?';
        });
    }

    // Submit Form via AJAX dengan SweetAlert2
    document.getElementById('form-polling').onsubmit = async function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-publish-poll');
        const originalText = btn.innerHTML;

        // Ambil Data Kelas dan Pertanyaan
        const classSelect = document.getElementById('poll-class');
        const classId = classSelect.value;
        const className = classSelect.options[classSelect.selectedIndex]?.getAttribute('data-name');
        const question = document.getElementById('poll-question').value;
        
        // Ambil Data Opsi
        const optionsInputs = document.querySelectorAll('.poll-option');
        let options = [];
        optionsInputs.forEach(input => {
            if(input.value.trim() !== '') options.push(input.value.trim());
        });

        if(options.length < 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilihan Kurang',
                text: 'Minimal harus ada 2 pilihan jawaban yang diisi!',
                confirmButtonColor: '#0071BC'
            });
            return;
        }

        btn.innerHTML = `<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...`;
        btn.disabled = true;

        try {
            const url = `{{ route('lms.teacherPolling.save', ['role' => $role ?? 'Guru', 'schoolName' => $schoolName ?? 'sekolah', 'schoolId' => $schoolId ?? '1']) }}`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    class_id: classId,
                    class_name: className,
                    question: question, 
                    options: options 
                })
            });

            const result = await response.json();

            if (!response.ok) throw new Error(result.message || "Gagal menyimpan ke database");

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message || 'Polling berhasil dipublikasikan ke siswa.',
                confirmButtonColor: '#0071BC',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.reload(); 
            });
            
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: error.message || 'Terjadi kesalahan saat mempublikasikan polling.',
                confirmButtonColor: '#0071BC'
            });
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    };
</script>