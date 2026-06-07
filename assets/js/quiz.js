/* WBDLS — quiz.js */
(function () {
    'use strict';

    // TIMER
    const timerEl = document.getElementById('quizTimer');
    const form = document.getElementById('quizForm');
    if (timerEl && typeof timeLimit !== 'undefined' && timeLimit > 0) {
        let secondsLeft = timeLimit * 60;
        const display = () => {
            const m = Math.floor(secondsLeft / 60);
            const s = secondsLeft % 60;
            timerEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            if (secondsLeft <= 60) timerEl.classList.add('danger');
        };
        display();
        const interval = setInterval(() => {
            secondsLeft--;
            display();
            if (secondsLeft <= 0) {
                clearInterval(interval);
                if (form) form.submit();
            }
        }, 1000);
    }

    // QUESTION NAVIGATION
    const slides = document.querySelectorAll('.question-slide');
    const counter = document.getElementById('questionCounter');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitQuizBtn');
    let current = 0;

    function showSlide(i) {
        slides.forEach((s, idx) => s.classList.toggle('active', idx === i));
        if (counter) counter.textContent = 'Question ' + (i + 1) + ' of ' + slides.length;
        if (prevBtn) prevBtn.disabled = (i === 0);
        const isLast = (i === slides.length - 1);
        if (nextBtn) nextBtn.style.display = isLast ? 'none' : '';
        if (submitBtn) submitBtn.style.display = isLast ? '' : 'none';
    }
    if (slides.length) showSlide(0);
    if (nextBtn) nextBtn.addEventListener('click', () => { if (current < slides.length - 1) { current++; showSlide(current); } });
    if (prevBtn) prevBtn.addEventListener('click', () => { if (current > 0) { current--; showSlide(current); } });
})();
