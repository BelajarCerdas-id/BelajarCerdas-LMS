let countdown = null;
let examFinished = false;

let timerDuration = 0;

function setTimerDuration(duration) {
    timerDuration = Number(duration) || 0;
}

// WAKTU HABIS
function emptyTime() {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Maaf, waktu latihan kamu sudah habis.',
    });
}

// START TIMER
function startTimer() {

    if (!attemptId) return;

    if (!timerDuration || timerDuration <= 0) return;

    stopTimer();

    examFinished = false;
    const { START_KEY, EXPIRE_KEY } = getTimerKeys();
    const timerExam = document.getElementById('timer-tka-tryout-test');
    let expireTime = parseInt(localStorage.getItem(EXPIRE_KEY));

    if (!expireTime) {
        const startTime = Date.now();
        expireTime = startTime + (timerDuration * 1000);
        localStorage.setItem(START_KEY, startTime);
        localStorage.setItem(EXPIRE_KEY, expireTime);
    }

    let remaining = Math.floor(
        (expireTime - Date.now()) / 1000
    );

    if (remaining <= 0) {
        remaining = 0;

        updateTimerDisplay(remaining);
        stopTimer();
        examFinished = true;
        emptyTime();
        autoSubmitUnSavedQuestions();

        localStorage.removeItem(START_KEY);
        localStorage.removeItem(EXPIRE_KEY);

        return;
    }

    updateTimerDisplay(remaining);

    countdown = setInterval(() => {

        remaining = Math.max(0, remaining - 1);

        updateTimerDisplay(remaining);

        if (remaining <= 0 && !examFinished) {

            examFinished = true;

            clearInterval(countdown);

            countdown = null;

            timerExam.textContent = 'Waktu Habis';

            emptyTime();

            autoSubmitUnSavedQuestions();

            localStorage.removeItem(START_KEY);
            localStorage.removeItem(EXPIRE_KEY);
        }

    }, 1000);

}

// STOP TIMER
function stopTimer() {
    if (countdown !== null) {
        clearInterval(countdown);
        countdown = null;
    }
}

// TOTAL DURASI YANG DIGUNAKAN
function getTotalExamDuration() {
    if (!attemptId) return 0;

    const { START_KEY } = getTimerKeys();

    const startTime = parseInt(localStorage.getItem(START_KEY));

    if (!startTime) return 0;

    return Math.floor((Date.now() - startTime) / 1000);
}

// UPDATE TIMER
function updateTimerDisplay(seconds) {

    seconds = Math.max(0, seconds);

    const timerExam = document.getElementById('timer-tka-tryout-test');

    if (!timerExam) return;

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    timerExam.textContent = `${String(hours).padStart(2, '0')}:` + `${String(minutes).padStart(2, '0')}:` + `${String(secs).padStart(2, '0')}`;
}

// TAMPILKAN WAKTU AWAL
function initTimerDisplay() {

    if (!timerDuration || timerDuration <= 0) {
        updateTimerDisplay(0);
        return;
    }

    if (!attemptId) {
        updateTimerDisplay(timerDuration);
        return;
    }

    const { EXPIRE_KEY } = getTimerKeys();

    const expireTime = parseInt(
        localStorage.getItem(EXPIRE_KEY)
    );

    if (expireTime) {
        const remaining = Math.max(0, Math.floor((expireTime - Date.now()) / 1000));
        updateTimerDisplay(remaining);

    } else {
        updateTimerDisplay(timerDuration);
    }
}

// RESET TIMER
function resetTimer() {

    stopTimer();
    examFinished = false;
    if (!attemptId) return;

    const { START_KEY, EXPIRE_KEY } = getTimerKeys();

    localStorage.removeItem(START_KEY);
    localStorage.removeItem(EXPIRE_KEY);
}