$(document).ready(function () {
    var oldRombelKelas = $('#school_class_id').attr('data-old-rombel'); // Ambil rombel kelas yang dipilih jika ada
    const container = document.getElementById('container');
    const schoolId = container.dataset.schoolId;

    function resetSelect($select, placeholder) {
        $select.prop('disabled', true).removeClass('cursor-pointer opacity-100').addClass('cursor-default opacity-50').empty().append(`<option value="" class="hidden">${placeholder}</option>`);
    }

    function enableSelect($select) {
        $select.prop('disabled', false).removeClass('cursor-default opacity-50').addClass('cursor-pointer opacity-100');
    }

    $('#id_kelas').on('change', function () {

        resetSelect($('#school_class_id'), 'Pilih Rombel Kelas');

        let kelasId = $(this).val();
        if (!kelasId) return;

        // LOAD ROMBEL
        $.get(`/kelas/${kelasId}/rombel-kelas/${schoolId}`, function (data) {
            enableSelect($('#school_class_id'));

            data.forEach(schoolClass => {
                $('#school_class_id').append(
                    `<option value="${schoolClass.id}">${schoolClass.class_name} - ${schoolClass.tahun_ajaran}</option>`
                );
            });

            if (oldRombelKelas) {
                $('#school_class_id').val(oldRombelKelas).trigger('change');
                oldRombelKelas = null;
            }
        });
    });
});
