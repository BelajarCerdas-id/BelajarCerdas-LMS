document.addEventListener('DOMContentLoaded', function () {

    window.Echo.channel('dailyReflectionLivePreview')
        .listen('.daily.reflection.live-preview', (event) => {

            // siswa menjawab refleksi
            if (event.tipe_model === 'SchReflAnswer') {
                if (event.data.reflection_id && event.data.total_responden !== undefined) {
                    updateHistoryDetailHeaderCount(event.data.reflection_id, event.data.total_responden);
                    updateHistoryDetailSummaryCount(event.data);
                    updateReflectionChart(event.data.bar_chart);
                    updateEmotionChart(event.data.emotions);
                    prependStudentAnswer(event.data.new_answer);
                }
            }
        });
});