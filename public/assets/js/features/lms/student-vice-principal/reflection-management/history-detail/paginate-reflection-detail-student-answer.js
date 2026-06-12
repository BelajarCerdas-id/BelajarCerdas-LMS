function paginateReflectionStudentAnswer(page = 1) {

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const reflectionQuestionId = container.dataset.reflectionQuestionId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!reflectionQuestionId) return;

    $('#reflection-student-answer-skeleton').removeClass('hidden');

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/reflection-management/history-detail/${reflectionQuestionId}/student-answer/paginate`,
        method: 'GET',
        success: function (response) {
            $('#tbody-reflection-student-answer').empty();
            $('.pagination-container-reflection-student-answer').empty();

            if (response.data.length > 0) {
                $.each(response.data, function (index, item) {
                    $('#tbody-reflection-student-answer').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.student_profile?.nama_lengkap ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.school_class?.class_name ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.answer ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${item.emotion_color}-100 text-${item.emotion_color}-700">
                                        ${item.emotion_status ?? '-'}
                                    </span>
                                </td>
                            </tr>
                        `);
                });

                $('.pagination-container-reflection-student-answer').html(response.links);
                bindPaginationLinks();
                $('#reflection-student-answer-skeleton').addClass('hidden');
                $('#empty-message-reflection-student-answer').hide();
                $('.thead-table-reflection-student-answer').show();
            } else {
                $('#reflection-student-answer-skeleton').addClass('hidden');
                $('#empty-message-reflection-student-answer').show();
                $('.thead-table-reflection-student-answer').hide();
            }
        },
        error: function (xhr, status, error) {
            console.error('Terjadi kesalahan:', status, error);
        }
    })
}

$(document).ready(function () {
    paginateReflectionStudentAnswer();
});

function bindPaginationLinks() {
    $('.pagination-container-reflection-student-answer').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateReflectionStudentAnswer(page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

function prependStudentAnswer(answerData) {

    const totalRow =
        $('#tbody-reflection-student-answer tr').length;

    const row = `
        <tr class="text-xs animate-fade-in">
            <td class="border border-gray-300 px-3 py-2 text-center">
                1
            </td>

            <td class="border border-gray-300 px-3 py-2 text-center">
                ${answerData.nama_lengkap}
            </td>

            <td class="border border-gray-300 px-3 py-2 text-center">
                ${answerData.class_name}
            </td>

            <td class="border border-gray-300 px-3 py-2 text-center">
                ${answerData.answer}
            </td>

            <td class="border border-gray-300 px-3 py-2 text-center">
                <span class="
                    inline-flex
                    items-center
                    px-2 py-1
                    rounded-full
                    text-xs
                    font-medium
                    bg-${answerData.emotion_color}-100
                    text-${answerData.emotion_color}-700
                ">
                    ${answerData.emotion_status}
                </span>
            </td>
        </tr>
    `;

    $('#tbody-reflection-student-answer')
        .prepend(row);

    // maksimal 10 data sesuai pagination
    $('#tbody-reflection-student-answer tr')
        .slice(10)
        .remove();
}