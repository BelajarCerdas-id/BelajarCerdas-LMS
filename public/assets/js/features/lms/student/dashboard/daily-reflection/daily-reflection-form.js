function studentDailyReflectionForm(page = 1) {
    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    const dailyReflectionForm = $('#daily-reflection-form');

    dailyReflectionForm.html(dailyReflectionLoading());

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/student/daily-reflection/paginate`,
        method: 'GET',
        data: {
            page: page
        },
        headers: {
            'X-Timezone': Intl.DateTimeFormat().resolvedOptions().timeZone
        },
        success: function (response) {
            dailyReflectionForm.empty();

            if (response.data.length > 0) {
                const emotions = response.emotion_status;

                $.each(response.data, function (index, item) {

                    const hasAnswered = item.sch_refl_answer !== null;

                    const answerData = item.sch_refl_answer;

                    let statusReflection = '';

                    const formatDate = (dateString) => {
                        const months = [
                            'Januari', 'Februari', 'Maret', 'April',
                            'Mei', 'Juni', 'Juli', 'Agustus',
                            'September', 'Oktober', 'November', 'Desember'
                        ];

                        const date = new Date(dateString);
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${day} ${monthName} ${year}`;
                    };

                    const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                    });

                    if (hasAnswered) {
                        statusReflection = `
                            <div class="px-2.5 py-1 rounded-lg bg-green-50 border border-green-200 text-[10px] font-bold text-green-600 uppercase
                                tracking-wider shadow-sm shrink-0">
                                Telah Dijawab
                            </div>
                        `;
                    } else {
                        statusReflection = `
                            <div class="px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-[10px] font-bold text-amber-600 uppercase
                                tracking-wider shadow-sm shrink-0">
                                Belum Dijawab
                            </div>
                        `;
                    }

                    $('#status-daily-reflection').html(statusReflection);

                    let emotionHTML = '';

                    $.each(emotions, function (index, emotion) {

                        const isChecked = hasAnswered && answerData.emotion_status === emotion.value;

                        emotionHTML += `
                            <label class="${hasAnswered ? '' : 'cursor-pointer'} group">

                                <input type="radio" name="emotion_status" value="${emotion.value}" class="peer hidden" ${isChecked ? 'checked' : ''} ${hasAnswered ? 'disabled' : ''}>

                                <div class="relative flex flex-col items-center justify-center py-4 rounded-2xl border border-slate-200 bg-white 
                                    transition-all duration-200 shadow-sm

                                    ${!hasAnswered ? emotion.classes.hover : ''}
                                    ${emotion.classes.checked}
                                    ${emotion.classes.ring}

                                    ${hasAnswered && !isChecked ? 'opacity-60' : ''}">

                                    <!-- ICON -->
                                    <div class="w-11 h-11 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center border border-slate-100 mb-3
                                        transition-all duration-200

                                        ${!hasAnswered ? emotion.classes.icon_hover : ''}
                                        ${emotion.classes.icon_checked}">

                                        <i class="fas ${emotion.icon} text-lg"></i>
                                    </div>

                                    <!-- LABEL -->
                                    <span class="text-[11px] font-semibold text-slate-600 text-center leading-tight px-1
                                        transition-all duration-200

                                        ${!hasAnswered ? emotion.classes.text_hover : ''}
                                        ${emotion.classes.text_checked}">

                                        ${emotion.label}

                                    </span>

                                    <!-- CHECK -->
                                    <div class="absolute top-2 right-2 w-5 h-5 rounded-full
                                        ${isChecked ? 'flex' : 'hidden'}
                                        items-center justify-center shadow-sm
                                        ${emotion.classes.check}">

                                        <i class="fas fa-check text-[10px]"></i>

                                    </div>
                                </div>
                            </label>
                        `;
                    });

                    const form = `
                        <!-- Title -->
                        <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm">

                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                Judul Refleksi
                            </p>

                            <h4 class="text-sm md:text-base font-bold text-slate-800 leading-relaxed">
                                ${item.title ?? 'Judul tidak tersedia'}
                            </h4>

                        </div>

                        <!-- Question -->
                        <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm mt-5">

                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                                Pertanyaan Refleksi
                            </p>

                            <p class="text-sm text-slate-700 leading-relaxed">
                                ${item.question ?? 'Pertanyaan tidak tersedia'}
                            </p>

                        </div>

                        <form id="answer-daily-reflection-form">

                            <input type="text" name="sch_refl_id" value="${item.id}" class="hidden">

                            <!-- EMOTION -->
                            <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm mt-5">

                                <div class="flex items-center justify-between mb-3">

                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Kondisi Emosional Hari Ini
                                        <sup class="text-red-500">&#42;</sup>
                                    </p>

                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 gap-3">
                                    ${emotionHTML}
                                </div>

                                ${!hasAnswered
                                    ? `<span id="error-emotion_status" class="text-red-500 text-xs mt-1 font-bold"></span>` : ''
                                }

                            </div>

                            <!-- Answer -->
                            <div class="mt-5">

                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-3">
                                    Jawaban Refleksi
                                    <sup class="text-red-500">&#42;</sup>
                                </label>

                                ${hasAnswered
                                    ? `
                                        <div class="relative">

                                            <textarea
                                                rows="4"
                                                readonly
                                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none resize-none">${answerData.answer}</textarea>

                                            <div class="flex items-center justify-between mt-2">

                                                <div class="flex items-center gap-2 text-[10px] text-slate-500">

                                                    <i class="fas fa-clock text-slate-400"></i>

                                                    <span>
                                                        Dikirim pada ${formatDate(answerData.created_at)}
                                                    </span>

                                                </div>

                                            </div>

                                        </div>
                                    `
                                    : `
                                        <textarea rows="4" name="answer" placeholder="Tuliskan pengalaman, kendala, atau masukan terkait kegiatan belajar hari ini..."
                                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none resize-none
                                            focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all"></textarea>

                                        <span id="error-answer" class="text-red-500 text-xs mt-1 font-bold"></span>
                                    `
                                }

                            </div>

                            ${!hasAnswered
                                ? `
                                    <!-- Footer -->
                                    <div class="flex items-start justify-between gap-4 pt-2">
        
                                        <div class="flex items-start gap-2 text-[10px] text-slate-500 leading-relaxed">
        
                                            <i class="fas fa-shield-halved text-indigo-400 mt-0.5"></i>
        
                                            <p>
                                                Jawaban refleksi hanya dapat diakses oleh pihak sekolah terkait.
                                            </p>
        
                                        </div>
        
                                        <button type="button"
                                            id="submit-button-create-daily-reflection"
                                            class="shrink-0 px-5 py-2.5 bg-linear-to-r from-indigo-500 to-indigo-600
                                            hover:from-indigo-600 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-200 transition-all
                                            cursor-pointer disabled:cursor-default">
        
                                            Kirim Jawaban
                                            <i class="fas fa-paper-plane ml-1"></i>
        
                                        </button>
        
                                    </div>
                                `
                            : ''
                        }

                        </form>
                    `;

                    dailyReflectionForm.append(form);
                });

                $('.pagination-container-daily-reflection').html(response.links);
                bindPaginationLinks();
                $('#empty-message-daily-reflection').addClass('hidden');

            } else {
                $('#empty-message-daily-reflection').removeClass('hidden');
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function () {
    studentDailyReflectionForm();
});

function bindPaginationLinks() {
    $('.pagination-container-daily-reflection').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        studentDailyReflectionForm(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

// Hilangkan error textarea saat user mengetik
$(document).on('input', 'textarea[name="answer"]', function () {

    $(this)
        .removeClass('!border-red-400 focus:!border-red-400 focus:!ring-red-100')
        .addClass('border-slate-200 focus:border-indigo-400 focus:ring-indigo-100');

    $('#error-answer').text('');
});

// Hilangkan error emotion status saat user memilih emosi
$(document).on('change', 'input[name="emotion_status"]', function () {

    $('#error-emotion_status').text('');
});

function dailyReflectionLoading() {
    return `
        <div class="space-y-5 animate-pulse">

            <!-- TITLE -->
            <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm">

                <div class="h-3 w-28 bg-slate-200 rounded mb-3"></div>
                <div class="h-5 w-72 bg-slate-200 rounded"></div>

            </div>

            <!-- QUESTION -->
            <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm">

                <div class="h-3 w-36 bg-slate-200 rounded mb-4"></div>

                <div class="space-y-2">
                    <div class="h-3 bg-slate-200 rounded"></div>
                    <div class="h-3 bg-slate-200 rounded w-11/12"></div>
                    <div class="h-3 bg-slate-200 rounded w-9/12"></div>
                </div>

            </div>

            <!-- EMOTION -->
            <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm">

                <div class="h-3 w-40 bg-slate-200 rounded mb-4"></div>

                <div class="grid grid-cols-5 gap-3">

                    ${Array(5).fill(`
                        <div class="rounded-2xl border border-slate-100 p-4 flex flex-col items-center">

                            <div class="w-11 h-11 rounded-xl bg-slate-200 mb-3"></div>

                            <div class="h-3 w-14 bg-slate-200 rounded"></div>

                        </div>
                    `).join('')}

                </div>

            </div>

            <!-- TEXTAREA -->
            <div>

                <div class="h-3 w-32 bg-slate-200 rounded mb-3"></div>

                <div class="h-32 rounded-2xl bg-slate-200"></div>

            </div>

            <!-- FOOTER -->
            <div class="flex items-center justify-between">

                <div class="h-3 w-64 bg-slate-200 rounded"></div>

                <div class="h-10 w-36 rounded-xl bg-slate-200"></div>

            </div>

        </div>
    `;
}

let isProcessing = false;

// Form Action create subject passing grade criteria
$(document).on('click', '#submit-button-create-daily-reflection', function (e) {
    e.preventDefault();

    const form = $('#answer-daily-reflection-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/student/daily-reflection/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-answer-daily-reflection').html(
                `
                <div class=" w-full flex justify-center">
                    <div class="fixed z-9999">
                        <div id="alertSuccess"
                            class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-green-600 text-sm">${response.message}</span>
                            <i class="fas fa-times cursor-pointer text-green-600" id="btnClose"></i>
                        </div>
                    </div>
                </div>
                `
            );

            setTimeout(function () {
                document.getElementById('alertSuccess').remove();
            }, 3000);

            document.getElementById('btnClose').addEventListener('click', function () {
                document.getElementById('alertSuccess').remove();
            });

            // reset form
            $('#answer-daily-reflection-form')[0].reset();

            const page = new URL(window.location.href).searchParams.get('page');

            // Memanggil fungsi untuk memuat ulang data
            studentDailyReflectionForm(page);

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            isProcessing = false;
            btn.prop('disabled', false);
            
            if (xhr.status === 409) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Refleksi Sudah Diisi',
                    text: xhr.responseJSON.message,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#4f46e5',
                });

                return;
            }
            
            if (xhr.status === 422) {
                const res = xhr.responseJSON;

                const errors = res.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#answer-daily-reflection-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#answer-daily-reflection-form').find(`[name="${field}"]`)
                        .removeClass('border-slate-200 focus:border-indigo-400 focus:ring-indigo-100')
                        .addClass('!border-red-400 focus:!border-red-400 focus:!ring-red-100');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }
        }
    });
});