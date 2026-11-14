let selectedQuestions = new Map(); // Simpan soal yang dipilih dan bobotnya

// panggil function ketika terjadi onChange pada target
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('school-assessment-checkbox')) {
        updateSchoolAssessmentSelected(); // Update status checkbox rombel
    }
});

// FUNCTION UPDATE SCHOOL ASSESSMENT SELECTED
function updateSchoolAssessmentSelected() {
    const checkedItems = document.querySelectorAll('.school-assessment-checkbox:checked'); // Ambil yang dicentang
    const total = checkedItems.length; // Hitung total

    if (total > 0) {
        $('#error-school_assessment_id').text(''); // Hapus error jika ada
    }
}

// EVENT: INDIVIDUAL CHECKBOX
$(document).off('change', '.question-checkbox').on('change', '.question-checkbox', function () {
    const questionId = String($(this).data('question-id')); // Ambil id soal
    const weightInput = $('.question-weight-input[data-question-id="' + questionId + '"]'); // Ambil input bobot

    if ($(this).is(':checked')) {
        selectedQuestions.set(questionId, 1); // Set default bobot 1
        weightInput.prop('disabled', false); // Aktifkan input
        weightInput.val(1); // Isi default 1
    } else {
        selectedQuestions.delete(questionId); // Hapus dari map
        weightInput.prop('disabled', true).val(''); // Nonaktifkan input
    }

    syncHiddenInputs(); // Sinkron hidden input
    updateSelectedCount(); // Update jumlah terpilih
    calculateTotal(); // Hitung total bobot
    clearWeightError(); // Hapus error border
    questionVisibilityInfo(); // Update info jumlah di halaman
});

// EVENT: MASTER CHECKBOX (SELECT ALL VISIBLE)
$(document).on('click', '.question-all-checkbox', function () {
    const checkboxes = document.querySelectorAll('.question-checkbox'); // Semua checkbox soal
    const allSelected = checkboxes.length === document.querySelectorAll('.question-checkbox:checked').length; // Cek semua sudah dicentang

    checkboxes.forEach(cb => {
        const id = String(cb.getAttribute('data-question-id')); // Ambil id soal
        const weightInput = $('.question-weight-input[data-question-id="' + id + '"]'); // Ambil input bobot
        cb.checked = !allSelected; // Toggle centang semua

        if (!allSelected) {
            selectedQuestions.set(id, 1); // Set default bobot 1
            weightInput.prop('disabled', false); // Aktifkan input
            weightInput.val(1); // Isi default 1
        } else {
            selectedQuestions.delete(id); // Hapus dari map
            weightInput.prop('disabled', true).val(''); // Nonaktifkan input
        }
    });

    syncHiddenInputs(); // Sinkron hidden input
    updateSelectedCount(); // Update jumlah terpilih
    calculateTotal(); // Hitung total bobot
    clearWeightError(); // Hapus error
    questionVisibilityInfo(); // Update info jumlah
});

// SYNC INPUT HIDDEN
function syncHiddenInputs() {
    const container = $('#container-form-teacher-question-bank-for-release'); // Container form
    container.find('input[data-hidden="true"]').remove(); // Hapus hidden lama

    selectedQuestions.forEach((weight, id) => { // Loop map
        container.append(`
            <input type="hidden" name="question_id[]" value="${id}" data-hidden="true"> <!-- id soal -->
            <input type="hidden" name="question_weight[${id}]" value="${weight}" data-hidden="true"> <!-- bobot soal -->
        `);
    });
}

// RESTORE CHECKED STATE (SETELAH AJAX / SEARCH)
function restoreCheckedState() {
    $('.question-checkbox').each(function () {
        const id = String($(this).data('question-id')); // Ambil id
        if (selectedQuestions.has(id)) { // Jika sudah dipilih
            $(this).prop('checked', true); // Centang checkbox
            const weight = selectedQuestions.get(id); // Ambil bobot
            const input = $('.question-weight-input[data-question-id="' + id + '"]'); // Ambil input
            input.prop('disabled', false); // Aktifkan input
            input.val(weight); // Set bobot
        }
    });
}

// function clear weight error
function clearWeightError() {
    $('.question-weight-input').each(function () {
        const questionId = $(this).data('question-id'); // Ambil id soal
        $(this).removeClass('border-red-400'); // Hapus border merah
        const errorSpan = $('#error-question_weight_' + questionId); // Ambil span error
        if (errorSpan.length) {
            errorSpan.addClass('hidden'); // Sembunyikan error
            errorSpan.text(''); // Kosongkan text
        }
    });
}