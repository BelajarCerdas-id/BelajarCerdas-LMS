let isProcessing = false;

$(document).ready(function () {

    $('#submit-library').on('click', function (e) {

        e.preventDefault();

        if (isProcessing) return;

        isProcessing = true;

        const form = $('#library-form')[0];

        const formData = new FormData(form);

        const btn = $(this);

        btn.prop('disabled', true);

        $.ajax({

            url: '/library/store',

            method: 'POST',

            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            data: formData,

            processData: false,

            contentType: false,

            success: function (response) {

                const modal = document.getElementById('modal-library');

                modal.close();

                $('#library-form')[0].reset();

                paginateLibrary();

                alert(response.message);

                isProcessing = false;

                btn.prop('disabled', false);
            },

            error: function (xhr) {

                alert('Terjadi kesalahan.');

                isProcessing = false;

                btn.prop('disabled', false);
            }
        });
    });

});

$(document).on('click', '.btn-delete-book', function () {

    let id = $(this).data('id');

    if (!confirm('Hapus buku ini?')) return;

    $.ajax({

        url: `/library/${id}/delete`,

        method: 'DELETE',

        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        success: function () {

            paginateLibrary();

        }

    });

});