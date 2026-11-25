// ============================================================
//  EXAM TIMER ONLY — anticheat detection lives in
//  anticheat/anticheat-core.js + platform files.
//  Shared state (examFinished, finalExamDuration, ...) and the
//  anticheat API (reportCheating, shouldSkipAntiCheat,
//  stopHeartbeat, enterFullscreen, ...) are provided by core.
// ============================================================

let countdown = null;
let lastTickTimestamp = 0;
let teacherWait = null; // { poll, socket } while the teacher has not admitted this student

// Function to display modal when exam time expires
function emptyTime() {
    isWarningModalOpen = true;
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Maaf, waktu ujian kamu sudah habis.',
        allowOutsideClick: false,
        allowEscapeKey: false
    });
}

function startTimer() {
    if (!containerFormAssessment.length) return;
    if (countdown !== null) return;
    if (examFinished) return;

    const timerExam = document.getElementById('timer-assessment-test');
    // scoped per user: a shared lab machine must not inherit another
    // student's timer state (see anticheat-core examStateKey)
    const START_KEY = examStartKey();
    const EXPIRE_KEY = examExpireKey();

    // Selalu verifikasi dengan server: firstOrCreate attempt server adalah
    // satu-satunya sumber kebenaran. Kalau localStorage punya expire yang
    // masih berjalan tapi attempt server sudah tidak ada (reset/manual),
    // timer lokal tetap jalan padahal simpan jawaban akan 422 not_started.
    startNewCountdown();

    function startNewCountdown() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/start-timer`,
            method: 'GET',
            success: function (response) {
                if (response.status === 'paused') return handleExamPaused();
                if (response.status === 'waiting_teacher') return waitForTeacher();
                stopWaitingForTeacher();
                if (examPaused) clearPausedState();

                const startTime = response.start_time;
                const expireTime = response.expire_time;
                localStorage.setItem(START_KEY, startTime);
                localStorage.setItem(EXPIRE_KEY, expireTime);

                const remaining = Math.ceil((expireTime - Date.now()) / 1000);
                if (remaining > 0) {
                    connectUnlockSocket();
                    runCountdown(remaining);
                } else {
                    clearInterval(countdown);
                    countdown = null;

                    if (timerExam) timerExam.textContent = 'Waktu Habis';

                    finalExamDuration = getTotalExamDuration();
                    saveQuestionDuration();
                    emptyTime();
                    autoSubmitUnSavedQuestions();

                    localStorage.removeItem(EXPIRE_KEY);
                    localStorage.removeItem(START_KEY);
                }
            },
            error: function (xhr) {
                if (xhr.status === 403 && xhr.responseJSON?.status === 'denied') return deniedByTeacher();
                if (xhr.status === 410) return handleExamStopped();

                if (xhr.status === 422) {
                    const response = xhr.responseJSON;
                    if (response?.status === 'not_started') {
                        isWarningModalOpen = true;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Assessment Belum Dimulai',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            isWarningModalOpen = false;
                        });
                        return;
                    }

                    if (response?.status === 'expired') {
                        isWarningModalOpen = true;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Assessment Telah Berakhir',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            isWarningModalOpen = false;
                        });
                        return;
                    }
                }

                // network / server error (not a business 422): retry, otherwise the timer never starts
                if (xhr.status !== 422) setTimeout(startNewCountdown, 3000);
            }
        });
    }

    function waitForTeacher() {
        if (examPaused) clearPausedState();
        if (teacherWait) return;
        isWarningModalOpen = true;
        Swal.fire({
            icon: 'info',
            title: 'Menunggu guru',
            text: 'Ujian akan dimulai setelah guru mengizinkan kamu masuk. Halaman ini akan terbuka otomatis.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
        // fallback when the socket is unavailable: retry the start every 10s
        teacherWait = { poll: setInterval(startTimer, 10000), socket: null };
        connectEntrySocket();
    }

    function connectEntrySocket() {
        if (!teacherWait || teacherWait.socket) return;

        $.ajax({
            url: examTokenUrl(),
            method: 'GET',
            success: function (res) {
                if (!teacherWait || teacherWait.socket) return;
                var scheme = window.location.protocol === 'https:' ? 'wss' : 'ws';
                var socket = new WebSocket(scheme + '://' + window.location.host + res.url + '?token=' + encodeURIComponent(res.token));
                teacherWait.socket = socket;

                socket.onmessage = function (msg) {
                    var event;
                    try { event = JSON.parse(msg.data); } catch (e) { return; }
                    if (event.type === 'exam_stopped') return handleExamStopped();
                    if (event.type !== 'entry_resolved') return;
                    if (event.status === 'approved') {
                        stopWaitingForTeacher();
                        startTimer();
                    } else {
                        deniedByTeacher();
                    }
                };
                socket.onclose = function () {
                    if (!teacherWait) return;
                    teacherWait.socket = null;
                    setTimeout(connectEntrySocket, 3000);
                };
            },
            error: function () {
                if (teacherWait) setTimeout(connectEntrySocket, 3000);
            }
        });
    }

    function stopWaitingForTeacher() {
        if (!teacherWait) return;
        clearInterval(teacherWait.poll);
        if (teacherWait.socket) {
            teacherWait.socket.onclose = null;
            try { teacherWait.socket.close(); } catch (e) {}
        }
        teacherWait = null;
        isWarningModalOpen = false;
        if (typeof Swal.close === 'function') Swal.close();
    }

    function deniedByTeacher() {
        stopWaitingForTeacher();
        isWarningModalOpen = true;
        Swal.fire({
            icon: 'error',
            title: 'Tidak diizinkan',
            text: 'Guru tidak mengizinkan kamu mengikuti ujian ini.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
        });
    }

    // Devtools with a breakpoint or the debugger panel pauses on the statement below;
    // a long gap there means the student is inspecting the page.
    function startDevtoolsWatch() {
        if (devtoolsTimer) return;
        devtoolsTimer = setInterval(function () {
            var before = performance.now();
            debugger;
            if (performance.now() - before > 150) reportCheating('devtools_open');
        }, 1000);
    }

    function handleExamPaused() {
        if (examFinished || examStopped || examPaused) return;
        stopWaitingForTeacher();
        examPaused = true;
        clearInterval(countdown);
        countdown = null;
        stopHeartbeat();

        isWarningModalOpen = true;
        // full-page blockage: no input and no exam content while the proctor pauses this student
        showPausedBlockage();
        connectUnlockSocket();
    }

    function handleExamResumed() {
        if (!examPaused) return;
        // the server answers success once running; that response clears the pause state
        startTimer();
    }

    function clearPausedState() {
        examPaused = false;
        isWarningModalOpen = false;
        hidePausedBlockage();
        startHeartbeat();
    }

    function handleExamStopped() {
        if (examStopped) return;
        examStopped = true;
        examPaused = false;
        hidePausedBlockage();
        stopWaitingForTeacher();
        stopHeartbeat();
        clearInterval(countdown);
        countdown = null;
        closeUnlockSocket();

        examFinished = true;
        finalExamDuration = getTotalExamDuration();
        saveQuestionDuration();

        isWarningModalOpen = true;
        Swal.fire({
            icon: 'info',
            title: 'Ujian dihentikan',
            text: 'Guru menghentikan ujian. Jawabanmu dikirim otomatis.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
        autoSubmitUnSavedQuestions(null, 'submitted');
    }

    function runCountdown(seconds) {
        startDevtoolsWatch();
        updateTimerDisplay(seconds);

        // Initialize tick timestamp for freeze detection
        lastTickTimestamp = Date.now();

        countdown = setInterval(() => {
            const now = Date.now();

            // Freeze detection: if the gap between ticks is > 10 seconds (a 3s gap
            // is normal main-thread jank under CKEditor/MathJax),
            // the browser was likely backgrounded (especially iOS Safari).
            // JS execution is suspended when the app goes to background on mobile.
            if (lastTickTimestamp && (now - lastTickTimestamp) > 10000) {
                if (!shouldSkipAntiCheat()) {
                    reportCheating('js_freeze_detected');
                }
            }
            lastTickTimestamp = now;

            seconds--;
            updateTimerDisplay(seconds);

            if (seconds <= 0 && !examFinished) {
                examFinished = true;

                clearInterval(countdown);
                countdown = null;

                if (timerExam) timerExam.textContent = 'Waktu Habis';

                saveQuestionDuration();
                finalExamDuration = getTotalExamDuration();
                emptyTime();
                autoSubmitUnSavedQuestions();

                localStorage.removeItem(START_KEY);
                localStorage.removeItem(EXPIRE_KEY);
            }
        }, 1000);
    }

    function updateTimerDisplay(seconds) {
        if (!timerExam) return;
        const safeSeconds = Math.max(0, seconds);
        const hours = Math.floor(safeSeconds / 3600);
        const minutes = Math.floor((safeSeconds % 3600) / 60);
        const secs = safeSeconds % 60;
        timerExam.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
}

function stopTimer() {
    clearInterval(countdown);
    countdown = null;
}

function getTotalExamDuration() {
    const startTime = parseInt(localStorage.getItem(examStartKey()));
    const expireTime = parseInt(localStorage.getItem(examExpireKey()));
    if (!startTime || !expireTime) return 0;

    const now = Date.now();
    const totalDuration = Math.floor((expireTime - startTime) / 1000);
    const remaining = Math.max(0, Math.floor((expireTime - now) / 1000));
    return totalDuration - remaining;
}
