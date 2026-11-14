window.addEventListener('resize', () => {
    document.querySelectorAll('.matching-container').forEach(container => {
        const pairs = JSON.parse(container.dataset.pairs || '[]');
        drawMatchingLines(container, pairs);
    });
});

function drawMatchingLines(container, pairs) {
    const svg = container.querySelector('.matching-lines');
    const centerLine = container.querySelector('.matching-center-line');
    if (!svg || !centerLine) return;

    svg.innerHTML = '';

    const cRect = container.getBoundingClientRect();
    const centerX = centerLine.getBoundingClientRect().left - cRect.left;

    pairs.forEach(pair => {
        const leftEl = container.querySelector(`[data-key="${pair.left}"]`);
        const rightEl = container.querySelector(`[data-key="${pair.right}"]`);
        if (!leftEl || !rightEl) return;

        const l = leftEl.getBoundingClientRect();
        const r = rightEl.getBoundingClientRect();

        const y1 = l.top + l.height / 2 - cRect.top;
        const y2 = r.top + r.height / 2 - cRect.top;

        const xLeft = l.right - cRect.left;
        const xRight = r.left - cRect.left;

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

        path.setAttribute(
            'd',
            `
                M ${xLeft} ${y1}
                L ${xRight} ${y2}
            `
        );

        path.setAttribute('stroke', '#0071BC');
        path.setAttribute('stroke-width', '2.5');
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke-linecap', 'round');

        svg.appendChild(path);
    });
}