/**
 * ==================================================================================
 * PROJECT MAIN SCRIPT (MODERN ES6+)
 * * SECTIONS INCLUDED:
 * 1. NAVIGATION: Handles the mobile hamburger menu open/close states.
 * 2. BACK TO TOP: Controls the visibility and smooth scrolling of the return button.
 * 3. SHOPPING UTILITIES: Manages the quantity increment/decrement for product inputs.
 * 4. INFINITE CAROUSEL: A touch-responsive, auto-playing slider for featured content.
 * 5. STATISTICS COUNTER: Animates numerical data when they enter the viewport.
 * 6. 24-HOUR COUNTDOWN: A specialized timer that resets daily at Midnight.
 * ==================================================================================
 */

document.addEventListener('DOMContentLoaded', () => {

    const hamburgerBtn = document.querySelector(".hamburger");
    const navList = document.querySelector(".nav-list");
    const closeBtn = document.querySelector(".close");

    if (hamburgerBtn && navList) {
        hamburgerBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            navList.classList.add("active");
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            navList.classList.remove("active");
        });
    }

    document.addEventListener("click", (e) => {
        if (navList.classList.contains("active")) {
            if (!navList.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                navList.classList.remove("active");
            }
        }
    });

    const fileInput = document.getElementById('picture');
    const fileChosen = document.getElementById('file-chosen');

    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            fileChosen.textContent = this.files[0].name;
        } else {
            fileChosen.textContent = "No file selected";
        }
    });

    const backBtn = document.querySelector(".back-to-top");

    if (backBtn) {
        window.addEventListener("scroll", () => {
            window.scrollY > 100 ? backBtn.classList.add("show") : backBtn.classList.remove("show");
        });

        backBtn.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    const inputGroups = document.querySelectorAll('.input-group');

    inputGroups.forEach(group => {
        group.addEventListener('click', (e) => {
            const buttonPlus = e.target.closest('.button-plus');
            const buttonMinus = e.target.closest('.button-minus');

            if (!buttonPlus && !buttonMinus) return;

            const input = group.querySelector('input');
            let currentVal = parseInt(input.value, 10) || 0;

            if (buttonPlus) {
                input.value = currentVal + 1;
            } else if (buttonMinus && currentVal > 0) {
                input.value = currentVal - 1;
            }
        });
    });

   

    const counters = document.querySelectorAll(".count");

    const animateCounter = (el) => {
        const target = +el.innerText.replace(/,/g, '');
        const duration = 1200;
        const startTime = performance.now();

        const updateCount = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const currentNum = Math.floor(progress * target);

            el.innerText = currentNum.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(updateCount);
            } else {
                el.innerText = target.toLocaleString();
            }
        };
        requestAnimationFrame(updateCount);
    };

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 1.0 });

    counters.forEach(counter => counterObserver.observe(counter));


    const hourEl = document.getElementById("hour");
    const minuteEl = document.getElementById("minute");
    const secondEl = document.getElementById("second");

    const updateTimer = () => {
        const now = new Date();
        const midnight = new Date();
        midnight.setHours(24, 0, 0, 0);

        const diff = midnight - now;

        const h = Math.floor(diff / (1000 * 60 * 60));
        const m = Math.floor((diff / (1000 * 60)) % 60);
        const s = Math.floor((diff / 1000) % 60);

        if (hourEl) hourEl.innerText = h.toString().padStart(2, '0');
        if (minuteEl) minuteEl.innerText = m.toString().padStart(2, '0');
        if (secondEl) secondEl.innerText = s.toString().padStart(2, '0');
    };

    setInterval(updateTimer, 1000);
    updateTimer();
});

document.addEventListener("DOMContentLoaded", function () {

  const endDateText = document.getElementById("dealEnd").innerText.trim();

  // Add end-of-day time so the deal lasts the whole date
  const endTime = new Date(endDateText + "T23:59:59").getTime();

  function updateTimer() {
    const now = new Date().getTime();
    const diff = endTime - now;

    if (isNaN(endTime)) {
      console.error("Invalid date:", endDateText);
      return;
    }

    if (diff <= 0) {
      hour.innerText = "00";
      minute.innerText = "00";
      second.innerText = "00";
      return;
    }

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    hour.innerText = String(hours).padStart(2, "0");
    minute.innerText = String(minutes).padStart(2, "0");
    second.innerText = String(seconds).padStart(2, "0");
  }

  updateTimer();
  setInterval(updateTimer, 1000);
});

// GLOBAL AJAX FORM INTERCEPTOR for User Side
document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll('form.ajax-form').forEach(form => {
      form.addEventListener('submit', function(e) {
          e.preventDefault();
          const submitBtn = this.querySelector('button[type="submit"]') || this.querySelector('button');
          if (submitBtn) submitBtn.disabled = true;

          // Clear previous errors
          this.querySelectorAll('.error-text').forEach(el => el.textContent = '');

          const formData = new FormData(this);

          fetch(this.action, {
              method: this.method || 'POST',
              body: formData,
              headers: {
                  'Accept': 'application/json'
              }
          })
          .then(res => res.json().catch(() => ({ status: 'error', message: 'Invalid server response' })))
          .then(data => {
              if (data.status === 'success') {
                  if (data.redirect) {
                      window.location.href = data.redirect;
                  } else {
                      window.location.reload();
                  }
              } else {
                  if (data.field) {
                      const errorEl = this.querySelector(`#${data.field}-error`) || this.querySelector(`.error-${data.field}`);
                      if (errorEl) {
                          errorEl.textContent = data.message;
                      } else {
                          alert(data.message);
                      }
                  } else {
                      alert(data.message || 'An error occurred.');
                  }
              }
          })
          .catch(err => {
              console.error(err);
              alert("A network error occurred. Please try again.");
          })
          .finally(() => {
              if (submitBtn) submitBtn.disabled = false;
          });
      });
  });
});