    <div class="flex flex-col items-center justify-center min-h-screen bg-slate-50 px-4">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center text-center max-w-sm">
            <i class="fas fa-triangle-exclamation text-6xl text-amber-400 mb-4"></i>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Akses Ditolak</h1>
            <p class="text-slate-500 mb-8">Maaf, Anda tidak memiliki akses ke dashboard ini.</p>
            
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center py-3 bg-red-50 text-red-600 font-bold rounded-xl gap-2 cursor-pointer transition-all duration-300 hover:bg-red-500 hover:text-white border border-red-100">
                    <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                    Kembali & Keluar
                </button>
            </form>
        </div>
    </div>
