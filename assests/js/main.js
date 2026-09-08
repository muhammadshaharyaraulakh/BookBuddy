document.addEventListener('DOMContentLoaded', () => {
    const hamburgerBtn = document.querySelector('.nav-hamburger');
    const mobileDrawer = document.querySelector('.mobile-drawer');
    const mobileCloseBtn = document.querySelector('.mobile-close-btn');

    if (hamburgerBtn && mobileDrawer) {
        hamburgerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            mobileDrawer.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (mobileCloseBtn && mobileDrawer) {
        mobileCloseBtn.addEventListener('click', () => {
            mobileDrawer.classList.remove('open');
            document.body.style.overflow = '';
        });
    }

    if (mobileDrawer) {
        mobileDrawer.addEventListener('click', (e) => {
            if (e.target === mobileDrawer) {
                mobileDrawer.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    }

    const catDropdownBtn = document.querySelector('.nav-dropdown-btn');
    const catDropdownMenu = document.querySelector('.nav-dropdown-menu');
    const catSearchInput = document.querySelector('.nav-search-box input');
    const catItems = document.querySelectorAll('.category-item');

    if (catDropdownBtn && catDropdownMenu) {
        catDropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            catDropdownMenu.classList.toggle('show');
            if (catDropdownMenu.classList.contains('show') && catSearchInput) {
                catSearchInput.value = '';
                catItems.forEach(item => item.style.display = 'flex');
                setTimeout(() => catSearchInput.focus(), 100);
            }
        });
    }

    if (catSearchInput && catItems.length > 0) {
        catSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            catItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    const profileToggle = document.querySelector('.user-profile-toggle');
    const profileMenu = document.querySelector('.user-profile-menu');

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('show');
        });
    }

    document.addEventListener('click', (e) => {
        if (catDropdownMenu && !catDropdownMenu.contains(e.target) && (!catDropdownBtn || !catDropdownBtn.contains(e.target))) {
            catDropdownMenu.classList.remove('show');
        }
        if (profileMenu && !profileMenu.contains(e.target) && (!profileToggle || !profileToggle.contains(e.target))) {
            profileMenu.classList.remove('show');
        }
    });

    const hoursEl = document.getElementById('deal-hours');
    const minsEl = document.getElementById('deal-mins');
    const secsEl = document.getElementById('deal-secs');

    if (hoursEl && minsEl && secsEl) {
        let totalSeconds = (14 * 3600) + (35 * 60) + 12;

        const updateTimer = () => {
            if (totalSeconds <= 0) {
                hoursEl.textContent = '00h';
                minsEl.textContent = '00m';
                secsEl.textContent = '00s';
                return;
            }

            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;

            hoursEl.textContent = String(h).padStart(2, '0') + 'h';
            minsEl.textContent = String(m).padStart(2, '0') + 'm';
            secsEl.textContent = String(s).padStart(2, '0') + 's';

            totalSeconds--;
        };

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    const sliderOuter = document.querySelector('.testimonials-slider-outer');
    const track = document.querySelector('.testimonials-track');
    const cards = document.querySelectorAll('.testimonial-card');
    const prevBtn = document.getElementById('slider-prev-btn');
    const nextBtn = document.getElementById('slider-next-btn');

    if (sliderOuter && track && cards.length > 0) {
        let currentIndex = 0;
        let autoSlideTimer = null;

        const getVisibleCount = () => {
            const width = window.innerWidth;
            if (width <= 680) return 1;
            if (width <= 1080) return 2;
            return 3;
        };

        const getMaxIndex = () => {
            return Math.max(0, cards.length - getVisibleCount());
        };

        const getStep = () => {
            const firstCard = cards[0];
            const gap = 24;
            return firstCard.offsetWidth + gap;
        };

        const applySlide = () => {
            const step = getStep();
            const maxIdx = getMaxIndex();
            if (currentIndex > maxIdx) {
                currentIndex = 0;
            }
            if (currentIndex < 0) {
                currentIndex = maxIdx;
            }
            track.style.transform = `translateX(-${currentIndex * step}px)`;
        };

        const slideNext = () => {
            const maxIdx = getMaxIndex();
            if (currentIndex >= maxIdx) {
                currentIndex = 0;
            } else {
                currentIndex++;
            }
            applySlide();
        };

        const slidePrev = () => {
            const maxIdx = getMaxIndex();
            if (currentIndex <= 0) {
                currentIndex = maxIdx;
            } else {
                currentIndex--;
            }
            applySlide();
        };

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                slideNext();
                resetAutoSlide();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                slidePrev();
                resetAutoSlide();
            });
        }

        const startAutoSlide = () => {
            if (!autoSlideTimer) {
                autoSlideTimer = setInterval(slideNext, 3500);
            }
        };

        const stopAutoSlide = () => {
            if (autoSlideTimer) {
                clearInterval(autoSlideTimer);
                autoSlideTimer = null;
            }
        };

        const resetAutoSlide = () => {
            stopAutoSlide();
            startAutoSlide();
        };

        sliderOuter.addEventListener('mouseenter', stopAutoSlide);
        sliderOuter.addEventListener('mouseleave', startAutoSlide);

        window.addEventListener('resize', () => {
            applySlide();
        });

        startAutoSlide();
    }

    const faqButtons = document.querySelectorAll('.faq-question-btn');
    faqButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-item');
            if (!item) return;

            const wasActive = item.classList.contains('active');

            document.querySelectorAll('.faq-item').forEach(other => {
                other.classList.remove('active');
            });

            if (!wasActive) {
                item.classList.add('active');
            }
        });
    });
});