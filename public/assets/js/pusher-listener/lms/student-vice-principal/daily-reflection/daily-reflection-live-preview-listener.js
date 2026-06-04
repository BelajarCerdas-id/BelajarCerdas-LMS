document.addEventListener('DOMContentLoaded', function () {

    window.Echo.channel('dailyReflectionLivePreview')
        .listen('.daily.reflection.live-preview', (event) => {

            // wakil kesiswaan membuat refleksi baru
            if (event.tipe_model === 'SchReflQuestion') {

                paginateDailyReflectionLivePreview(); // load live preview
            }

            // siswa menjawab refleksi
            if (event.tipe_model === 'SchReflAnswer') {

                updateEmotionRealtime(event.data.emotions);

                if (event.data.reflection_id && event.data.total_responden !== undefined) {
                    updateHistoryRecentCount(event.data.reflection_id, event.data.total_responden);
                }
            }
        });
});