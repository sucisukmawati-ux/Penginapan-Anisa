document.querySelectorAll('[data-duration-select]').forEach((durationSelect) => {
    const form = durationSelect.closest('form');
    const nightsField = form?.querySelector('[data-nights-field]');
    const nightsInput = nightsField?.querySelector('input[name="nights"]');

    if (!nightsField || !nightsInput) {
        return;
    }

    const updateNightsField = () => {
        const isOvernight = durationSelect.value === 'overnight';
        nightsField.hidden = !isOvernight;
        nightsInput.disabled = !isOvernight;
    };

    durationSelect.addEventListener('change', updateNightsField);
    updateNightsField();
});

document.querySelectorAll('[data-testimonial-carousel]').forEach((carousel) => {
    const scroller = carousel.querySelector('[data-testimonial-scroller]');
    const track = carousel.querySelector('[data-testimonial-track]');
    const toggle = carousel.querySelector('[data-testimonial-toggle]');

    if (!scroller || !track || !toggle) {
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let pausedByUser = false;
    let isVisible = false;
    let animationFrame;
    let lastFrameTime;
    let loopDistance = 0;
    let clonedItems = [];

    const setupTrack = () => {
        clonedItems.forEach((item) => item.remove());
        clonedItems = [];
        scroller.scrollLeft = 0;
        toggle.hidden = track.scrollWidth <= scroller.clientWidth;

        if (toggle.hidden) {
            loopDistance = 0;
            return;
        }

        [...track.children].forEach((item) => {
            const clone = item.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            clone.inert = true;
            track.append(clone);
            clonedItems.push(clone);
        });

        loopDistance = clonedItems[0].offsetLeft - track.children[0].offsetLeft;
        scroller.scrollLeft = loopDistance;
    };

    const stopAutoplay = () => {
        window.cancelAnimationFrame(animationFrame);
        animationFrame = undefined;
        lastFrameTime = undefined;
    };

    const updateAutoplay = () => {
        stopAutoplay();

        if (toggle.hidden || !isVisible || pausedByUser || reducedMotion.matches || carousel.matches(':hover') ||
            carousel.contains(document.activeElement) || document.hidden || loopDistance === 0) {
            return;
        }

        const animate = (time) => {
            if (lastFrameTime !== undefined) {
                scroller.scrollLeft -= Math.min(time - lastFrameTime, 50) * 0.025;
            }
            lastFrameTime = time;

            if (scroller.scrollLeft <= 0) {
                scroller.scrollLeft += loopDistance;
            }

            animationFrame = window.requestAnimationFrame(animate);
        };

        animationFrame = window.requestAnimationFrame(animate);
    };

    setupTrack();
    toggle.hidden = !loopDistance;
    toggle.addEventListener('click', () => {
        pausedByUser = !pausedByUser;
        toggle.setAttribute('aria-pressed', String(pausedByUser));
        toggle.textContent = pausedByUser ? 'Lanjutkan geser otomatis' : 'Jeda geser otomatis';
        updateAutoplay();
    });

    carousel.addEventListener('mouseenter', updateAutoplay);
    carousel.addEventListener('mouseleave', updateAutoplay);
    carousel.addEventListener('focusin', updateAutoplay);
    carousel.addEventListener('focusout', updateAutoplay);
    document.addEventListener('visibilitychange', updateAutoplay);
    window.addEventListener('resize', () => {
        setupTrack();
        updateAutoplay();
    });
    reducedMotion.addEventListener('change', updateAutoplay);

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(([entry]) => {
            isVisible = entry.isIntersecting;
            updateAutoplay();
        }, { threshold: 0.1 });
        observer.observe(carousel);
    } else {
        isVisible = true;
    }

    updateAutoplay();
});