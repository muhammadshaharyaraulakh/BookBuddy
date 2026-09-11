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

    const resetCategoryFilter = () => {
        catItems.forEach((item, idx) => {
            item.style.display = idx < 4 ? 'flex' : 'none';
        });
    };

    if (catDropdownBtn && catDropdownMenu) {
        catDropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            catDropdownMenu.classList.toggle('show');
            if (catDropdownMenu.classList.contains('show') && catSearchInput) {
                catSearchInput.value = '';
                resetCategoryFilter();
                setTimeout(() => catSearchInput.focus(), 100);
            }
        });
    }

    if (catSearchInput && catItems.length > 0) {
        catSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            if (!query) {
                resetCategoryFilter();
            } else {
                catItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(query)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }
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

    const countdownBox = document.querySelector('.deal-countdown-box');
    const hoursEl = document.getElementById('deal-hours');
    const minsEl = document.getElementById('deal-mins');
    const secsEl = document.getElementById('deal-secs');

    if (hoursEl && minsEl && secsEl) {
        let totalSeconds = 0;
        const endTimeStr = countdownBox ? countdownBox.getAttribute('data-end-time') : null;
        if (endTimeStr) {
            const endMs = new Date(endTimeStr.replace(/-/g, '/')).getTime();
            totalSeconds = Math.max(0, Math.floor((endMs - Date.now()) / 1000));
        } else {
            totalSeconds = (14 * 3600) + (35 * 60) + 12;
        }

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

    // Section 1: Neo-Brutalist Toast Popup System
    window.showNeoToast = function(message, type = 'error', duration = 4000) {
        let container = document.getElementById('neoToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'neoToastContainer';
            container.className = 'neo-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `neo-toast neo-toast-${type}`;

        let iconClass = 'ph-bold ph-warning-circle';
        if (type === 'success') iconClass = 'ph-bold ph-check-circle';
        if (type === 'warning') iconClass = 'ph-bold ph-warning';

        toast.innerHTML = `
            <div class="neo-toast-content">
                <i class="${iconClass} neo-toast-icon"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="neo-toast-close" aria-label="Close notification">&times;</button>
        `;

        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('active'));

        const removeToast = () => {
            toast.classList.remove('active');
            setTimeout(() => toast.remove(), 260);
        };

        toast.querySelector('.neo-toast-close').addEventListener('click', removeToast);
        setTimeout(removeToast, duration);
    };

    // Section 2: Book Details Page Quantity Stepper with Limit Validation
    const bookQtyInput = document.getElementById('book-qty');
    const bookQtyDecBtn = document.getElementById('qty-decrease');
    const bookQtyIncBtn = document.getElementById('qty-increase');

    if (bookQtyInput && bookQtyDecBtn && bookQtyIncBtn) {
        bookQtyDecBtn.addEventListener('click', () => {
            let current = parseInt(bookQtyInput.value, 10) || 1;
            if (current <= 1) {
                window.showNeoToast("Quantity cannot be less than 1.", "warning");
                return;
            }
            bookQtyInput.value = current - 1;
        });

        bookQtyIncBtn.addEventListener('click', () => {
            let current = parseInt(bookQtyInput.value, 10) || 1;
            const maxAllowed = parseInt(bookQtyInput.getAttribute('data-max'), 10) || 10;
            const liveStock = parseInt(bookQtyInput.getAttribute('data-stock'), 10) || 0;

            if (current >= maxAllowed) {
                if (maxAllowed >= 10) {
                    window.showNeoToast("Maximum limit is 10 copies per book.", "warning");
                } else {
                    window.showNeoToast(`Only ${liveStock} ${liveStock === 1 ? 'copy' : 'copies'} available in stock.`, "warning");
                }
                return;
            }
            bookQtyInput.value = current + 1;
        });
    }

    // Section 3: Book Details Page Add to Cart AJAX Execution
    const addToCartBtn = document.getElementById('btn-add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', async () => {
            const bookId = addToCartBtn.getAttribute('data-book-id');
            const bookTitle = addToCartBtn.getAttribute('data-book-title') || 'Book';
            const qtyInput = document.getElementById('book-qty');
            const quantity = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;

            if (!bookId) {
                window.showNeoToast("Invalid book selected.", "error");
                return;
            }

            const originalHtml = addToCartBtn.innerHTML;
            addToCartBtn.disabled = true;
            addToCartBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Adding...';

            try {
                const formData = new FormData();
                formData.append('bookId', bookId);
                formData.append('quantity', quantity);

                const res = await fetch('/handlers/cart.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    window.showNeoToast(data.message || `Added to cart!`, "success");
                    addToCartBtn.innerHTML = '<i class="ph-bold ph-check"></i> Added!';
                    setTimeout(() => {
                        addToCartBtn.disabled = false;
                        addToCartBtn.innerHTML = originalHtml;
                    }, 2000);
                } else {
                    window.showNeoToast(data.message || "Failed to add to cart.", "error");
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = originalHtml;
                }
            } catch (err) {
                window.showNeoToast("Network error. Please try again.", "error");
                addToCartBtn.disabled = false;
                addToCartBtn.innerHTML = originalHtml;
            }
        });
    }

    // Helper: Dynamic Checkout State & Stock Issues Toggle
    function updateCheckoutState(hasStockIssue) {
        const warningBox = document.getElementById('checkout-stock-warning');
        const disabledBtn = document.getElementById('btn-checkout-disabled');
        const activeBtn = document.getElementById('btn-checkout-active');
        const headerBadge = document.getElementById('cart-header-stock-badge');

        if (hasStockIssue) {
            if (warningBox) warningBox.style.display = 'block';
            if (disabledBtn) disabledBtn.style.display = 'block';
            if (activeBtn) activeBtn.style.display = 'none';
            if (headerBadge) headerBadge.style.display = 'inline-flex';
        } else {
            if (warningBox) warningBox.style.display = 'none';
            if (disabledBtn) disabledBtn.style.display = 'none';
            if (activeBtn) activeBtn.style.display = 'flex';
            if (headerBadge) headerBadge.style.display = 'none';
        }
    }

    // Section 4: Cart Page Interactive Stepper (+ / -) AJAX Sync
    document.querySelectorAll('.btn-cart-inc, .btn-cart-dec').forEach(btn => {
        btn.addEventListener('click', async () => {
            const cartId = btn.getAttribute('data-cart-id');
            const action = btn.classList.contains('btn-cart-inc') ? 'inc' : 'dec';
            const row = document.getElementById(`cart-row-${cartId}`);
            if (!row || !cartId) return;

            if (row.getAttribute('data-daily-deal') === '1') {
                window.showNeoToast("This book is in an active Daily Deal and cannot be modified. Please remove it from your cart.", "warning");
                return;
            }

            const input = row.querySelector('.row-qty-input');
            const currentVal = parseInt(input.value, 10) || 1;
            const maxVal = parseInt(input.getAttribute('data-max'), 10) || 10;
            const stockVal = parseInt(input.getAttribute('data-stock'), 10) || 0;

            if (action === 'inc' && currentVal >= maxVal) {
                if (maxVal >= 10) {
                    window.showNeoToast("Maximum purchase limit is 10 copies per book.", "warning");
                } else {
                    window.showNeoToast(`Only ${stockVal} ${stockVal === 1 ? 'copy' : 'copies'} available in stock.`, "warning");
                }
                return;
            }

            if (action === 'dec' && currentVal <= 1) {
                window.showNeoToast("Quantity cannot be less than 1. Click the trash icon to remove.", "warning");
                return;
            }

            btn.disabled = true;
            try {
                const formData = new FormData();
                formData.append('cart_id', cartId);
                formData.append('action', action);

                const res = await fetch('/handlers/cartUpdate.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    input.value = data.new_quantity;
                    const liveStock = parseInt(data.live_stock, 10) || stockVal;
                    input.setAttribute('data-stock', liveStock);
                    input.setAttribute('data-max', Math.min(10, liveStock));
                    row.setAttribute('data-stock', liveStock);

                    const subtotalEl = document.getElementById(`row-subtotal-${cartId}`);
                    if (subtotalEl && data.item_subtotal) {
                        subtotalEl.textContent = `$${data.item_subtotal}`;
                    }
                    const summarySubtotal = document.getElementById('cart-summary-subtotal');
                    const summaryTotal = document.getElementById('cart-summary-total');
                    if (summarySubtotal && data.cart_subtotal) {
                        summarySubtotal.textContent = `$${data.cart_subtotal}`;
                    }
                    if (summaryTotal && data.cart_total) {
                        summaryTotal.textContent = `$${data.cart_total}`;
                    }

                    // Update badges if quantity is now aligned with stock
                    const badgeContainer = document.getElementById(`item-badges-${cartId}`);
                    if (badgeContainer && liveStock > 0) {
                        const existingStockBadge = badgeContainer.querySelector('.badge-warning');
                        if (existingStockBadge) {
                            if (liveStock < 5) {
                                existingStockBadge.innerHTML = `<i class="ph-bold ph-warning"></i> Only ${liveStock} left in stock`;
                            } else {
                                existingStockBadge.remove();
                            }
                        }
                    }

                    // Dynamically toggle checkout button & warning state
                    if (data.has_stock_issue !== undefined) {
                        updateCheckoutState(data.has_stock_issue);
                    }
                } else {
                    window.showNeoToast(data.message || "Could not update quantity.", "error");
                }
            } catch (err) {
                window.showNeoToast("Connection error while updating cart.", "error");
            } finally {
                btn.disabled = false;
            }
        });
    });

    // Section 5: Cart Page Remove Item AJAX Execution
    document.querySelectorAll('.btn-cart-remove').forEach(btn => {
        btn.addEventListener('click', async () => {
            const cartId = btn.getAttribute('data-cart-id');
            const bookTitle = btn.getAttribute('data-book-title') || 'this book';
            const row = document.getElementById(`cart-row-${cartId}`);
            if (!row || !cartId) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i>';

            try {
                const formData = new FormData();
                formData.append('cart_id', cartId);

                const res = await fetch('/handlers/cartRemove.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    window.showNeoToast(data.message || `Removed "${bookTitle}" from cart.`, "success");
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        row.remove();

                        const countEl = document.getElementById('cart-header-count');
                        if (countEl && data.items_count !== undefined) {
                            countEl.textContent = data.items_count;
                        }

                        const summarySubtotal = document.getElementById('cart-summary-subtotal');
                        const summaryTotal = document.getElementById('cart-summary-total');
                        if (summarySubtotal && data.cart_subtotal) {
                            summarySubtotal.textContent = `$${data.cart_subtotal}`;
                        }
                        if (summaryTotal && data.cart_total) {
                            summaryTotal.textContent = `$${data.cart_total}`;
                        }

                        // Update checkout state if removing item resolved stock issues
                        if (data.has_stock_issue !== undefined) {
                            updateCheckoutState(data.has_stock_issue);
                        }

                        if (data.items_count === 0) {
                            window.location.reload();
                        }
                    }, 300);
                } else {
                    window.showNeoToast(data.message || "Failed to remove item.", "error");
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph-bold ph-trash"></i>';
                }
            } catch (err) {
                window.showNeoToast("Error communicating with server.", "error");
                btn.disabled = false;
                btn.innerHTML = '<i class="ph-bold ph-trash"></i>';
            }
        });
    });

    const genreToggle = document.querySelector('.genre-collapse-toggle');
    const genreWidget = document.querySelector('.sidebar-genre-widget');

    if (sessionStorage.getItem('genreCollapsed') === 'true' && genreWidget) {
        genreWidget.classList.add('collapsed');
    }

    if (genreToggle && genreWidget) {
        genreToggle.addEventListener('click', () => {
            genreWidget.classList.toggle('collapsed');
            if (genreWidget.classList.contains('collapsed')) {
                sessionStorage.setItem('genreCollapsed', 'true');
            } else {
                sessionStorage.removeItem('genreCollapsed');
            }
        });
    }

    const genreLinks = document.querySelectorAll('.genre-collapsible-content a');
    genreLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (genreWidget) {
                genreWidget.classList.add('collapsed');
                sessionStorage.setItem('genreCollapsed', 'true');
            }
        });
    });

    // ==========================================================================
    // Section 6: Neo-Brutalist Address Verification & Order Placement Flow
    // ==========================================================================
    const checkoutBtnActive = document.getElementById('btn-checkout-active');
    const selectAddrModal = document.getElementById('selectAddressModal');
    const addAddrModal = document.getElementById('addAddressModal');
    const addressListContainer = document.getElementById('addressListContainer');
    const addAddressForm = document.getElementById('addAddressForm');
    const btnConfirmPlaceOrder = document.getElementById('btn-confirm-place-order');
    const btnSwitchToAddAddr = document.getElementById('btn-switch-to-add-addr');

    const openModal = (modal) => {
        if (!modal) return;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('active');
        if (!document.querySelector('.neo-modal-backdrop.active')) {
            document.body.style.overflow = '';
        }
    };

    // Close handlers for select modal
    document.querySelectorAll('.btn-close-select-addr-modal').forEach(btn => {
        btn.addEventListener('click', () => closeModal(selectAddrModal));
    });

    // Close handlers for add modal
    document.querySelectorAll('.btn-close-add-addr-modal').forEach(btn => {
        btn.addEventListener('click', () => closeModal(addAddrModal));
    });

    // Close modal on backdrop click
    [selectAddrModal, addAddrModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal(modal);
                }
            });
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal(selectAddrModal);
            closeModal(addAddrModal);
        }
    });

    // Switch from Select Modal to Add Address Modal
    if (btnSwitchToAddAddr) {
        btnSwitchToAddAddr.addEventListener('click', () => {
            closeModal(selectAddrModal);
            openModal(addAddrModal);
        });
    }

    // Helper to render an address card in the list
    const renderAddressCard = (addr, isSelected = false) => {
        const card = document.createElement('div');
        card.className = `neo-address-card ${isSelected ? 'selected' : ''}`;
        card.setAttribute('data-address-id', addr.id);

        card.innerHTML = `
            <input type="radio" name="selected_delivery_address" value="${addr.id}" ${isSelected ? 'checked' : ''} style="display:none;" />
            <div class="address-card-header">
                <label class="address-card-radio-label">
                    <span class="address-custom-radio"></span>
                    <span>${addr.city}, ${addr.province}</span>
                </label>
                <button type="button" class="address-details-toggle" aria-label="Toggle details">
                    <span>Details</span>
                    <i class="ph-bold ph-caret-down"></i>
                </button>
            </div>
            <div class="address-expandable-details">
                <div class="address-detail-item">
                    <i class="ph-bold ph-phone"></i>
                    <div><span class="address-detail-label">Contact:</span> ${addr.contact}</div>
                </div>
                <div class="address-detail-item">
                    <i class="ph-bold ph-map-pin"></i>
                    <div><span class="address-detail-label">District:</span> ${addr.district || 'N/A'}</div>
                </div>
                <div class="address-detail-item">
                    <i class="ph-bold ph-house-line"></i>
                    <div><span class="address-detail-label">Address:</span> ${addr.address}</div>
                </div>
                <div class="address-detail-item">
                    <i class="ph-bold ph-mailbox"></i>
                    <div><span class="address-detail-label">Postal Code:</span> ${addr.postcode}</div>
                </div>
            </div>
        `;
        bindAddressCardEvents(card);
        return card;
    };

    const bindAddressCardEvents = (card) => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('.address-details-toggle')) return;

            document.querySelectorAll('.neo-address-card').forEach(c => {
                c.classList.remove('selected');
                const radio = c.querySelector('input[type="radio"]');
                if (radio) radio.checked = false;
            });

            card.classList.add('selected');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });

        const toggleBtn = card.querySelector('.address-details-toggle');
        const detailsDiv = card.querySelector('.address-expandable-details');
        if (toggleBtn && detailsDiv) {
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleBtn.classList.toggle('open');
                detailsDiv.classList.toggle('open');
            });
        }
    };

    // Bind existing cards rendered by PHP
    if (addressListContainer) {
        addressListContainer.querySelectorAll('.neo-address-card').forEach(bindAddressCardEvents);
    }

    // Checkout button click handler (Cart)
    if (checkoutBtnActive) {
        checkoutBtnActive.addEventListener('click', () => {
            window.pendingDailyDealOrder = null;
            const noticeEl = document.getElementById('modalOrderContextNotice');
            if (noticeEl) noticeEl.style.display = 'none';

            if (window.isUserLoggedIn === false) {
                window.showNeoToast("Please sign in to your account to place your order.", "warning");
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1200);
                return;
            }

            const addresses = window.initialUserAddresses || [];
            if (addresses.length === 0) {
                openModal(addAddrModal);
            } else {
                openModal(selectAddrModal);
            }
        });
    }

    // Daily Deal button click handler (Instant Order without adding to cart)
    const claimDealButtons = document.querySelectorAll('.btn-claim-daily-deal');
    claimDealButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();

            const dealId = btn.getAttribute('data-deal-id');
            const bookId = btn.getAttribute('data-book-id');
            const bookTitle = btn.getAttribute('data-book-title') || 'Daily Deal Book';
            const dealPrice = btn.getAttribute('data-deal-price') || '';

            window.pendingDailyDealOrder = {
                deal_id: dealId,
                book_id: bookId,
                book_title: bookTitle,
                deal_price: dealPrice
            };

            const noticeEl = document.getElementById('modalOrderContextNotice');
            const noticeText = document.getElementById('modalOrderContextText');
            if (noticeEl && noticeText) {
                noticeText.textContent = `Instant Flash Deal: ${bookTitle} ($${dealPrice})`;
                noticeEl.style.display = 'flex';
            }

            if (window.isUserLoggedIn === false) {
                window.showNeoToast("Please sign in to your account to claim this daily deal.", "warning");
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1200);
                return;
            }

            const addresses = window.initialUserAddresses || [];
            if (addresses.length === 0) {
                openModal(addAddrModal);
            } else {
                openModal(selectAddrModal);
            }
        });
    });

    // Client-side address field validation
    const validateField = (input, errorEl, rule, message) => {
        if (!rule) {
            input.classList.add('is-invalid');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.add('active');
            }
            return false;
        } else {
            input.classList.remove('is-invalid');
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.remove('active');
            }
            return true;
        }
    };

    // Handle Add Address Form Submission
    if (addAddressForm) {
        addAddressForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const provinceInput = document.getElementById('addr_province');
            const districtInput = document.getElementById('addr_district');
            const cityInput = document.getElementById('addr_city');
            const postcodeInput = document.getElementById('addr_postcode');
            const contactInput = document.getElementById('addr_contact');
            const addressInput = document.getElementById('addr_address');

            const errProvince = document.getElementById('err_province');
            const errDistrict = document.getElementById('err_district');
            const errCity = document.getElementById('err_city');
            const errPostcode = document.getElementById('err_postcode');
            const errContact = document.getElementById('err_contact');
            const errAddress = document.getElementById('err_address');

            let isValid = true;

            isValid = validateField(provinceInput, errProvince, provinceInput.value.trim() !== '', 'Please select a province') && isValid;
            isValid = validateField(districtInput, errDistrict, districtInput.value.trim() !== '', 'District name is required') && isValid;
            isValid = validateField(cityInput, errCity, cityInput.value.trim() !== '', 'City name is required') && isValid;
            
            // Exactly 5 numeric digits
            const postalVal = postcodeInput.value.trim();
            isValid = validateField(postcodeInput, errPostcode, /^[0-9]{5}$/.test(postalVal), 'Postal code must be exactly 5 digits') && isValid;

            // Exactly 11 numeric digits
            const contactVal = contactInput.value.trim();
            isValid = validateField(contactInput, errContact, /^[0-9]{11}$/.test(contactVal), 'Contact number must be exactly 11 digits (e.g. 03001234567)') && isValid;

            isValid = validateField(addressInput, errAddress, addressInput.value.trim() !== '', 'Permanent address is required') && isValid;

            if (!isValid) {
                window.showNeoToast("Please resolve the highlighted address errors.", "warning");
                return;
            }

            const btnSubmit = document.getElementById('btn-submit-add-addr');
            const originalBtnHtml = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Saving Address...';

            try {
                const formData = new FormData(addAddressForm);
                const res = await fetch('/handlers/address.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    window.showNeoToast(data.message || "Address saved successfully!", "success");

                    const newAddress = data.address;
                    if (!window.initialUserAddresses) window.initialUserAddresses = [];
                    window.initialUserAddresses.unshift(newAddress);

                    // Unselect existing cards
                    if (addressListContainer) {
                        addressListContainer.querySelectorAll('.neo-address-card').forEach(c => {
                            c.classList.remove('selected');
                            const r = c.querySelector('input[type="radio"]');
                            if (r) r.checked = false;
                        });
                        // Insert new card at top
                        const newCard = renderAddressCard(newAddress, true);
                        addressListContainer.prepend(newCard);
                    }

                    // Reset form
                    addAddressForm.reset();
                    closeModal(addAddrModal);

                    // Open select modal with new address selected
                    openModal(selectAddrModal);
                } else {
                    window.showNeoToast(data.message || "Could not save address.", "error");
                    if (data.field) {
                        const badInput = document.getElementById(`addr_${data.field}`);
                        const badErr = document.getElementById(`err_${data.field}`);
                        if (badInput) badInput.classList.add('is-invalid');
                        if (badErr) {
                            badErr.textContent = data.message;
                            badErr.classList.add('active');
                        }
                    }
                }
            } catch (err) {
                window.showNeoToast("Connection error while saving address.", "error");
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalBtnHtml;
            }
        });
    }

    // Confirm & Place Order Handler (Cart or Daily Deal)
    if (btnConfirmPlaceOrder) {
        btnConfirmPlaceOrder.addEventListener('click', async () => {
            const selectedRadio = document.querySelector('input[name="selected_delivery_address"]:checked');
            if (!selectedRadio) {
                window.showNeoToast("Please select a delivery address.", "warning");
                return;
            }

            const addressId = selectedRadio.value;
            const originalBtnHtml = btnConfirmPlaceOrder.innerHTML;
            btnConfirmPlaceOrder.disabled = true;
            btnConfirmPlaceOrder.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Processing Order...';

            try {
                const formData = new FormData();
                formData.append('address_id', addressId);

                let endpoint = '/handlers/placeOrder.php';
                if (window.pendingDailyDealOrder) {
                    endpoint = '/handlers/placeDailyDealOrder.php';
                    if (window.pendingDailyDealOrder.deal_id) {
                        formData.append('deal_id', window.pendingDailyDealOrder.deal_id);
                    }
                    if (window.pendingDailyDealOrder.book_id) {
                        formData.append('book_id', window.pendingDailyDealOrder.book_id);
                    }
                }

                const res = await fetch(endpoint, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    window.showNeoToast(data.message || "Order placed successfully!", "success", 2000);
                    setTimeout(() => {
                        window.location.href = data.redirect || `/orders.php?success=1&order_id=${data.order_id}`;
                    }, 600);
                } else {
                    window.showNeoToast(data.message || "Failed to place order.", "error");
                    btnConfirmPlaceOrder.disabled = false;
                    btnConfirmPlaceOrder.innerHTML = originalBtnHtml;
                }
            } catch (err) {
                window.showNeoToast("Connection error while processing order.", "error");
                btnConfirmPlaceOrder.disabled = false;
                btnConfirmPlaceOrder.innerHTML = originalBtnHtml;
            }
        });
    }
});