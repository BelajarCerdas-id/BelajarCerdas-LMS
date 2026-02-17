$(document).ready(function () {
    var oldService = $('#id_service').attr('data-old-service'); // Ambil service yang dipilih jika ada

    function resetSelect($select, placeholder) {
        $select.prop('disabled', true).removeClass('cursor-pointer opacity-100').addClass('cursor-default opacity-50').empty().append(`<option value="" class="hidden">${placeholder}</option>`);
    }

    function enableSelect($select) {
        $select.prop('disabled', false).removeClass('cursor-default opacity-50').addClass('cursor-pointer opacity-100');
    }

    $('#id_kurikulum').on('change', function () {

        resetSelect($('#id_service'), 'Pilih Service');

        let curriculumId = $(this).val();
        if (!curriculumId) return;

        // LOAD SERVICE
        $.get(`/kurikulum/${curriculumId}/service`, function (data) {
            enableSelect($('#id_service'));

            data.forEach(service => {
                $('#id_service').append(
                    `<option value="${service.id}">${service.name}</option>`
                );
            });

            if (oldService) {
                $('#id_service').val(oldService).trigger('change');
                oldService = null;
            }
        });
    });
});
