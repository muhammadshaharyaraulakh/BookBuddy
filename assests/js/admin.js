document.addEventListener("DOMContentLoaded", function() {
    
    // Initialize Flatpickr for modern date selection
    const dateInputs = document.querySelectorAll('.publish_date');
    if (dateInputs.length > 0) {
        flatpickr(".publish_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            allowInput: true
        });
    }

    // Select Elements
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const closeBtn = document.getElementById('close-sidebar');
    const openBtn = document.getElementById('sidebarCollapse'); // The Hamburger in your main page

    // 1. Function to OPEN
    function openMenu() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    }

    // 2. Function to CLOSE
    function closeMenu() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    }

    // 3. Event Listeners
    if(openBtn) {
        openBtn.addEventListener('click', openMenu);
    }
    
    if(closeBtn) {
        closeBtn.addEventListener('click', closeMenu);
    }
    
    if(overlay) {
        overlay.addEventListener('click', closeMenu); // Click outside to close
    }

    // Optional: Close menu when a link is clicked (good for mobile)
    const links = document.querySelectorAll('.components li a');
    links.forEach(link => {
        link.addEventListener('click', closeMenu);
    });
});
document.addEventListener("DOMContentLoaded", function() {
    
    // --- SIDEBAR LOGIC ---
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

    if(openBtn) openBtn.addEventListener('click', () => toggleMenu(true));
    if(closeBtn) closeBtn.addEventListener('click', () => toggleMenu(false));
    if(overlay) overlay.addEventListener('click', () => toggleMenu(false));

    // --- CATEGORY FILTER LOGIC ---
    const filterBtns = document.querySelectorAll('.category-btn');
    const bookCards = document.querySelectorAll('.book-card');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // 1. Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // 2. Add active class to clicked button
            btn.classList.add('active');

            const filterValue = btn.getAttribute('data-filter');

            // 3. Loop through books and hide/show based on category
            bookCards.forEach(card => {
                const category = card.getAttribute('data-category');

                if (filterValue === 'all' || filterValue === category) {
                    card.style.display = 'flex'; // Use flex to maintain layout
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // --- AJAX FORM INTERCEPTOR ---
    const ajaxForms = document.querySelectorAll('.ajax-form');

    ajaxForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Clear previous errors
            const errorDivs = form.querySelectorAll('.error');
            errorDivs.forEach(div => div.innerText = '');

            const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
            if(submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
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
                if(submitBtn) {
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
                        alert(data.message);
                    }
                } catch (err) {
                    alert('Error updating category');
                }
            } else {
                alert('Category name cannot be empty');
            }
        });
    });
});