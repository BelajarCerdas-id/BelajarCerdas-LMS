$(document).ready(function () {
    var oldKurikulum = $('#id_kurikulum').attr('data-old-kurikulum');
    var oldKelas = $('#id_kelas').attr('data-old-kelas'); // Ambil kelas yang dipilih jika ada
    var oldMapel = $('#id_mapel').attr('data-old-mapel'); // Ambil mapel yang dipilih jika ada
    var oldBab = $('#id_bab').attr('data-old-bab'); // Ambil bab yang dipilih jika ada
    var oldSubBab = $('#id_sub_bab').attr('data-old-sub-bab'); // Ambil sub bab yang dipilih jika ada

    const container = document.getElementById('container');
    const schoolId = container.dataset.schoolId;

    function resetSelect($select, placeholder) {
        $select.prop('disabled', true).removeClass('cursor-pointer opacity-100').addClass('cursor-default opacity-50').empty().append(`<option value="" class="hidden">${placeholder}</option>`);
    }

    function enableSelect($select) {
        $select.prop('disabled', false).removeClass('cursor-default opacity-50').addClass('cursor-pointer opacity-100');
    }

    $('#id_kurikulum').on('change', function () {

        resetSelect($('#id_kelas'), 'Pilih Kelas');
        resetSelect($('#id_mapel'), 'Pilih Mata Pelajaran');
        resetSelect($('#id_bab'), 'Pilih Bab');
        resetSelect($('#id_sub_bab'), 'Pilih Sub Bab');

        let curriculumId = $(this).val();
        if (!curriculumId) return;

        // LOAD KELAS
        $.get(schoolId
            ? `/kurikulum/${curriculumId}/${schoolId}/kelas`
            : `/kurikulum/${curriculumId}/kelas`, function (data) {
            enableSelect($('#id_kelas'));

            data.forEach(kelas => {
                $('#id_kelas').append(
                    `<option value="${kelas.id}">${kelas.kelas}</option>`
                );
            });

            if (oldKelas) {
                $('#id_kelas').val(oldKelas).trigger('change');
                oldKelas = null; // PENTING
            }
        });
    });

    // Ketika id_kelas berubah
    $('#id_kelas').on('change', function () {

        resetSelect($('#id_mapel'), 'Pilih Mata Pelajaran');
        resetSelect($('#id_bab'), 'Pilih Bab');
        resetSelect($('#id_sub_bab'), 'Pilih Sub Bab');

        let kelasId = $(this).val();
        if (!kelasId) return;

        $.get(schoolId
            ? `/kelas/${kelasId}/${schoolId}/mapel`
            : `/kelas/${kelasId}/mapel`, function (data) {
                enableSelect($('#id_mapel'));

                data.forEach(mapel => {
                    $('#id_mapel').append(
                        `<option value="${schoolId ? mapel.mapel?.id : mapel.id}">${schoolId ? mapel.mapel?.mata_pelajaran : mapel.mata_pelajaran}</option>`
                    );
                });

                if (oldMapel) {
                    $('#id_mapel').val(oldMapel).trigger('change');
                    oldMapel = null; // reset setelah dipakai
                }
            });
    });

    // Ketika id_mapel berubah
    $('#id_mapel').on('change', function () {

        resetSelect($('#id_bab'), 'Pilih Bab');
        resetSelect($('#id_sub_bab'), 'Pilih Sub Bab');

        let mapelId = $(this).val();
        if (!mapelId) return;

        $.get(`/mapel/${mapelId}/bab`, function (data) {
            enableSelect($('#id_bab'));

            data.forEach(bab => {
                $('#id_bab').append(
                    `<option value="${bab.id}">${bab.nama_bab}</option>`
                );
            });

            // EDIT MODE (sekali saja)
            if (oldBab) {
                $('#id_bab').val(oldBab).trigger('change');
                oldBab = null;
            }
        });
    });

    // Ketika id_bab berubah
    $('#id_bab').on('change', function () {

        resetSelect($('#id_sub_bab'), 'Pilih Sub Bab');

        let babId = $(this).val();
        if (!babId) return;

        $.get(`/bab/${babId}/sub-bab`, function (data) {
            enableSelect($('#id_sub_bab'));

            data.forEach(sub => {
                $('#id_sub_bab').append(
                    `<option value="${sub.id}">${sub.sub_bab}</option>`
                );
            });

            // EDIT MODE (sekali saja)
            if (oldSubBab) {
                $('#id_sub_bab').val(oldSubBab).trigger('change');
                oldSubBab = null;
            }
        });
    });

    if (oldKurikulum) {
        $('#id_kurikulum').val(oldKurikulum).trigger('change');
    }
});