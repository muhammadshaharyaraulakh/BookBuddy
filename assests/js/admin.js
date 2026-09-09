document.addEventListener("DOMContentLoaded", function() {
    const dateInputs = document.querySelectorAll('.publish_date');
    if (dateInputs.length > 0) {
        flatpickr(".publish_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            allowInput: true
        });
    }

    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('close-sidebar');
    const openBtn = document.getElementById('sidebarCollapse');

    function toggleMenu(show) {
        if (show) {
            sidebar.classList.add('active');
            overlay.classList.add('active');
        } else {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    }

    if (openBtn) openBtn.addEventListener('click', () => toggleMenu(true));
    if (closeBtn) closeBtn.addEventListener('click', () => toggleMenu(false));
    if (overlay) overlay.addEventListener('click', () => toggleMenu(false));

    const links = document.querySelectorAll('.components li a');
    links.forEach(link => {
        link.addEventListener('click', () => toggleMenu(false));
    });

    const filterBtns = document.querySelectorAll('.category-btn');
    const bookCards = document.querySelectorAll('.book-card');

    filterBtns.forEach(btn => {
        if (btn.tagName === 'A') return;
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filterValue = btn.getAttribute('data-filter');

            bookCards.forEach(card => {
                const category = card.getAttribute('data-category');

                if (filterValue === 'all' || filterValue === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    const ajaxForms = document.querySelectorAll('.ajax-form');

    ajaxForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const errorDivs = form.querySelectorAll('.error');
            errorDivs.forEach(div => div.innerText = '');

            const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> ...';
            }

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'error') {
                    if (data.field && data.field !== 'general') {
                        const errorDiv = form.querySelector(`.error.${data.field}`);
                        if (errorDiv) {
                            errorDiv.innerText = data.message;
                            errorDiv.style.color = 'red';
                            errorDiv.style.fontSize = '12px';
                            errorDiv.style.marginTop = '4px';
                        } else {
                            alert(data.message);
                        }
                    } else {
                        alert(data.message);
                    }
                } else if (data.status === 'success') {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload(); 
                    }
                }
            } catch (err) {
                console.error(err);
                alert("An unexpected error occurred. Check console for details.");
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            }
        });
    });

    document.querySelectorAll('.edit-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const item = document.getElementById('cat-item-' + id);
            item.querySelector('.cat-name').style.display = 'none';
            item.querySelector('.edit-cat-input').style.display = 'inline-block';
            this.style.display = 'none';
            item.querySelector('.save-category-btn').style.display = 'inline-block';
        });
    });

    document.querySelectorAll('.save-category-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            const item = document.getElementById('cat-item-' + id);
            const input = item.querySelector('.edit-cat-input');
            const newTitle = input.value;
            
            if (newTitle && newTitle.trim() !== '') {
                try {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('category', newTitle.trim());
                    
                    const response = await fetch('/admin/handlers/editCategory.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        item.querySelector('.cat-name').innerText = newTitle.trim();
                        item.querySelector('.cat-name').style.display = 'inline-block';
                        input.style.display = 'none';
                        this.style.display = 'none';
                        item.querySelector('.edit-category-btn').style.display = 'inline-block';
                    } else {
                        showCategoryError(data.message || 'Error updating category');
                    }
                } catch (err) {
                    showCategoryError('Error updating category. Please try again.');
                }
            } else {
                showCategoryError('Category name cannot be empty');
            }
        });
    });

    function showCategoryError(message) {
        const toast = document.getElementById('categoryErrorToast');
        const msgEl = document.getElementById('categoryErrorMessage');
        if (!toast || !msgEl) {
            alert(message);
            return;
        }
        msgEl.textContent = message;
        toast.classList.add('active');

        if (window.categoryToastTimeout) {
            clearTimeout(window.categoryToastTimeout);
        }
        window.categoryToastTimeout = setTimeout(() => {
            toast.classList.remove('active');
        }, 5000);
    }

    const closeToastBtn = document.getElementById('closeToast');
    if (closeToastBtn) {
        closeToastBtn.addEventListener('click', () => {
            const toast = document.getElementById('categoryErrorToast');
            if (toast) {
                toast.classList.remove('active');
            }
            if (window.categoryToastTimeout) {
                clearTimeout(window.categoryToastTimeout);
            }
        });
    }
});