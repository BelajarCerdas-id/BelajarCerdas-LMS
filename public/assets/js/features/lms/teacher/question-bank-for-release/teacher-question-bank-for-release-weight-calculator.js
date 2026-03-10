function calculateTotal() {
    const totalWeightEl = document.getElementById('total-weight'); // Elemen tampilan total
    const totalWeightInput = document.getElementById('total-weight-input'); // Input hidden total
    const errorWeight = document.getElementById('error-total_weight'); // Elemen error total
    const progressBar = document.getElementById('weight-progress'); // Bar progress

    let total = 0; // Inisialisasi total

    selectedQuestions.forEach(val => {
        const weight = parseInt(val) || 0;
        total += weight;
    });

    totalWeightEl.textContent = total >= 1000 ? "999+" : total; // Update text total
    totalWeightInput.value = total; // Update input hidden total

    const percentage = Math.min(total, 100); // Batasi visual max 100%
    progressBar.style.width = percentage + '%'; // Set lebar progress

    // Reset warna
    progressBar.classList.remove('bg-green-600', 'bg-yellow-400', 'bg-red-500');

    let colorClass = 'bg-red-500'; // Default merah

    // Set warna berdasarkan total
    if (total > 100) {
        colorClass = 'bg-red-500';
        errorWeight.classList.remove('hidden');
    }
    else if (total === 100) {
        colorClass = 'bg-green-600';
        errorWeight.classList.add('hidden');
    }
    else if (total >= 60) {
        colorClass = 'bg-yellow-400';
        errorWeight.classList.add('hidden');
    }
    else if (total >= 30) {
        colorClass = 'bg-red-500';
        errorWeight.classList.add('hidden');
    }
    else {
        errorWeight.classList.add('hidden');
    }

    progressBar.className = progressBar.className.replace(/bg-\w+-\d+/g, ''); // Hapus class warna lama
    progressBar.classList.add(colorClass); // Tambahkan class warna baru
}

// Hitung ulang saat user input
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('question-weight-input')) {
        calculateTotal(); // Panggil calculateTotal()
    }
});

// Event input untuk setiap bobot soal
$(document).on('input', '.question-weight-input', function () {
    const questionId = String($(this).data('question-id')); // Ambil id soal
    let value = parseInt($(this).val()); // Ambil value

    // Jika value tidak valid, set ke 1
    if (isNaN(value) || value < 1) {
        value = 1;
        $(this).val(1);
    }

    selectedQuestions.set(questionId, value); // Update Map

    $(this).removeClass('border-red-400'); // Hapus border error

    const errorSpan = $('#error-question_weight_' + questionId); // Ambil span error
    if (errorSpan.length) {
        errorSpan.addClass('hidden').text(''); // Sembunyikan error
    }

    syncHiddenInputs(); // Sinkron hidden input
    calculateTotal(); // Hitung ulang total
});

// Hitung awal saat load halaman
$(document).ready(function () {
    calculateTotal();
});