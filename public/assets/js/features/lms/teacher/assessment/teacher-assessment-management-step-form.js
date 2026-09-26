let currentStep = 1;
const totalSteps = 3;

const nextBtn = document.getElementById("next-btn");
const backBtn = document.getElementById("backBtn");
const draftBtn = document.getElementById("submit-button-draft-create-assessment");
const publishBtn = document.getElementById("submit-button-publish-create-assessment");
const progressLine = document.getElementById("progress-line");
const now = new Date();

const clientNow = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0') + ' ' + String(now.getHours()).padStart(2, '0') + ':' +
    String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');

function updateUI() {

    document.querySelectorAll(".step").forEach((el, index) => {
        el.classList.toggle("hidden", index + 1 !== currentStep);
    });

    for (let i = 1; i <= totalSteps; i++) {
        const circle = document.getElementById("circle-" + i);

        // RESET
        circle.className = "mx-auto w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm font-bold transition-all duration-300";

        if (i < currentStep) {
            circle.innerHTML = `<i class="fa-solid fa-check"></i>`;
            circle.classList.add("border-green-600", "bg-green-600", "text-white");
        }
        else if (i === currentStep) {
            circle.innerHTML = i;
            circle.classList.add("border-blue-600", "text-blue-600", "bg-white");
        }
        else {
            circle.innerHTML = i;
            circle.classList.add("border-gray-300", "text-gray-400", "bg-white");
        }
    }

    const progressPercent = ((currentStep - 1) / (totalSteps - 1)) * 100;
    progressLine.style.width = progressPercent + "%";

    backBtn.classList.toggle("hidden", currentStep === 1);

    if (currentStep === totalSteps) {
        nextBtn.classList.add("hidden");
        draftBtn.classList.remove("hidden");
        publishBtn.classList.remove("hidden");
    } else {
        nextBtn.classList.remove("hidden");
        draftBtn.classList.add("hidden");
        publishBtn.classList.add("hidden");
    }
}

backBtn.addEventListener("click", () => {
    if (currentStep > 1) {
        currentStep--;
        updateUI();
    }
});

nextBtn.addEventListener("click", function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!role || !schoolName || !schoolId) return;

    // VALIDASI STEP 1: Pastikan semua informasi akademik sudah dipilih sebelum lanjut ke langkah berikutnya
    if (currentStep === 1) {
        let hasAcademicError = false;

        const tahunAjaran = $('#dropdown-tahun-ajaran').val();
        const kelas = $('#dropdown-filter-class').val();
        const mapel = $('#dropdown-filter-mapel').val();
        const semester = $('#semester').val();

        // Reset error messages dan highlight untuk academic info
        $('#error-dropdown-tahun-ajaran, #error-dropdown-filter-class, #error-dropdown-filter-mapel, #error-semester').text('');
        $('#dropdown-tahun-ajaran, #dropdown-filter-class, #dropdown-filter-mapel, #semester').removeClass('border-red-400 border');

        if (!tahunAjaran) {
            $('#error-dropdown-tahun-ajaran').text('Harap pilih tahun ajaran.');
            $('#dropdown-tahun-ajaran').addClass('border-red-400 border');
            hasAcademicError = true;
        }

        if (!kelas) {
            $('#error-dropdown-filter-class').text('Harap pilih kelas.');
            $('#dropdown-filter-class').addClass('border-red-400 border');
            hasAcademicError = true;
        }

        if (!mapel) {
            $('#error-dropdown-filter-mapel').text('Harap pilih mata pelajaran.');
            $('#dropdown-filter-mapel').addClass('border-red-400 border');
            hasAcademicError = true;
        }

        if (!semester) {
            $('#error-semester').text('Harap pilih semester.');
            $('#semester').addClass('border-red-400 border');
            hasAcademicError = true;
        }

        if (hasAcademicError) {
            const academicSection = document.getElementById('academic-info-section') || container;
            academicSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        // Cek juga Target Rombel jika belum ada yang dipilih
        const selectedRombelCount = $('.rombel-checkbox:checked').length;
        if (selectedRombelCount === 0) {
            $('#error-school_class_id').removeClass('hidden').text('Harap pilih setidaknya satu rombel kelas.');
            const rombelSection = document.getElementById('target-rombel-section');
            if (rombelSection) {
                rombelSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            return;
        } else {
            $('#error-school_class_id').addClass('hidden').text('');
        }
    }

    const form = $('#create-assessment-form')[0];
    const formData = new FormData(form);
    formData.append('step', currentStep);
    formData.append('client_now', clientNow);

    // Kirim AJAX ke route validasi
    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-assessment-management/validate-step-form/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // validasi berhasil -> next step
            currentStep++;
            updateUI();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                // tampilkan error di field
                const errors = xhr.responseJSON.errors;

                // reset error sebelumnya
                $('.text-error').text('');
                $('.border-red-400').removeClass('border-red-400 border');

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-assessment-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-assessment-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });

                if (errors.school_class_id) {
                    $('#error-school_class_id').removeClass('hidden').text(errors.school_class_id[0]);
                }
            } else {
                alert('Terjadi kesalahan saat validasi.');
            }
        }
    });
});

updateUI();