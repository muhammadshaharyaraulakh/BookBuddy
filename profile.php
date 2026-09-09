<?php
require __DIR__ . "/config/config.php";
require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";

$userName = $_SESSION['name'] ?? 'Muhammad Shaharyar';
$userEmail = $_SESSION['gmail'] ?? 'reader@bookbuddy.com';
$userRole = $_SESSION['role'] ?? 'Member';
?>

<div class="page-header-banner">
    <h1 class="page-header-title">Reader Profile</h1>
    <p class="page-header-subtitle">Manage your BookBuddy credentials, preferences and library activity</p>
</div>

<div class="profile-layout">
    <div class="neo-panel-card">
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <div style="width: 84px; height: 84px; border: 2.5px solid var(--neo-black); border-radius: 999px; overflow: hidden; box-shadow: var(--neo-shadow-sm); background: var(--neo-yellow);">
                <img src="/userImages/<?= htmlspecialchars($_SESSION['image'] ?? '1788851470_aliraza1.png') ?>" alt="Profile" onerror="this.src='/images/logo.png'" style="width: 100%; height: 100%; object-fit: cover;" />
            </div>
            <div>
                <h2 style="font-size: 24px; font-weight: 800;"><?= htmlspecialchars($userName) ?></h2>
                <span style="font-size: 14px; font-weight: 600; color: #6B7280; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph-bold ph-envelope-simple"></i> <?= htmlspecialchars($userEmail) ?>
                </span>
                <div style="margin-top: 6px;">
                    <span style="background: var(--neo-teal); border: 2px solid var(--neo-black); border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                        <?= htmlspecialchars($userRole) ?>
                    </span>
                </div>
            </div>
        </div>

        <hr style="border: 1px solid var(--neo-black); margin: 24px 0;">

        <!-- Section: Saved Delivery Addresses -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="ph-bold ph-map-pin" style="color: var(--neo-teal);"></i> Saved Delivery Addresses
                </h3>
                <button type="button" id="btn-profile-add-addr" class="neo-btn" style="background: var(--neo-yellow); border: 2px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 8px 16px; font-size: 13px; font-weight: 800; cursor: pointer; box-shadow: var(--neo-shadow-sm); display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph-bold ph-plus"></i> Add New Address
                </button>
            </div>

            <?php
            $profileUserId = $_SESSION['id'] ?? null;
            $savedAddresses = [];
            if ($profileUserId) {
                $addrQuery = $connection->prepare("SELECT * FROM user_address WHERE user_id = :uid ORDER BY id DESC");
                $addrQuery->execute([':uid' => $profileUserId]);
                $savedAddresses = $addrQuery->fetchAll(PDO::FETCH_OBJ);
            }
            ?>

            <?php if (empty($savedAddresses)): ?>
                <div style="background: var(--neo-gray-light); border: 2px dashed var(--neo-black); border-radius: var(--neo-radius-sm); padding: 24px; text-align: center;">
                    <p style="font-weight: 700; color: #6B7280; margin-bottom: 8px;">No saved delivery addresses yet.</p>
                    <span style="font-size: 13px; color: #9CA3AF;">Add an address now so your book checkout is swift and verified.</span>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                    <?php foreach ($savedAddresses as $pAddr): ?>
                        <div class="profile-addr-card" style="background: var(--neo-white); border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 16px; box-shadow: var(--neo-shadow-sm); display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                    <div style="font-size: 16px; font-weight: 800; color: var(--neo-black);">
                                        <?= htmlspecialchars($pAddr->city) ?>, <?= htmlspecialchars($pAddr->province) ?>
                                    </div>
                                    <button type="button" class="btn-edit-profile-addr" 
                                        data-id="<?= $pAddr->id ?>"
                                        data-province="<?= htmlspecialchars($pAddr->province) ?>"
                                        data-district="<?= htmlspecialchars($pAddr->district ?? '') ?>"
                                        data-city="<?= htmlspecialchars($pAddr->city) ?>"
                                        data-postcode="<?= htmlspecialchars($pAddr->postcode) ?>"
                                        data-address="<?= htmlspecialchars($pAddr->address) ?>"
                                        data-contact="<?= htmlspecialchars($pAddr->contact) ?>"
                                        style="background: var(--neo-teal); border: 2px solid var(--neo-black); border-radius: 6px; padding: 4px 8px; font-size: 12px; font-weight: 800; cursor: pointer; box-shadow: 1.5px 1.5px 0px var(--neo-black); display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="ph-bold ph-pencil-simple"></i> Edit
                                    </button>
                                </div>
                                <div style="font-size: 13px; color: #4B5563; font-weight: 600; line-height: 1.5;">
                                    <div><strong>District:</strong> <?= htmlspecialchars($pAddr->district ?? 'N/A') ?></div>
                                    <div><strong>Permanent Address:</strong> <?= htmlspecialchars($pAddr->address) ?></div>
                                    <div><strong>Postal Code:</strong> <?= htmlspecialchars($pAddr->postcode) ?></div>
                                    <div><strong>Contact:</strong> <?= htmlspecialchars($pAddr->contact) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <hr style="border: 1px solid var(--neo-black); margin: 24px 0;">

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <a href="/orders.php" style="background: var(--neo-gray-light); border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 16px; box-shadow: var(--neo-shadow-sm); display: flex; align-items: center; gap: 12px;">
                <i class="ph-bold ph-package" style="font-size: 24px; color: var(--neo-teal);"></i>
                <div>
                    <div style="font-size: 15px; font-weight: 800;">My Orders</div>
                    <div style="font-size: 12px; color: #6B7280; font-weight: 600;">Track active deliveries</div>
                </div>
            </a>

            <a href="/cart.php" style="background: var(--neo-gray-light); border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 16px; box-shadow: var(--neo-shadow-sm); display: flex; align-items: center; gap: 12px;">
                <i class="ph-bold ph-shopping-cart" style="font-size: 24px; color: var(--neo-yellow);"></i>
                <div>
                    <div style="font-size: 15px; font-weight: 800;">My Cart</div>
                    <div style="font-size: 12px; color: #6B7280; font-weight: 600;">View items</div>
                </div>
            </a>

            <a href="/auth/logout.php" style="background: var(--neo-gray-light); border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 16px; box-shadow: var(--neo-shadow-sm); display: flex; align-items: center; gap: 12px;">
                <i class="ph-bold ph-sign-out" style="font-size: 24px; color: var(--neo-pink);"></i>
                <div>
                    <div style="font-size: 15px; font-weight: 800;">Log Out</div>
                    <div style="font-size: 12px; color: #6B7280; font-weight: 600;">End current session</div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Modal for Profile Add / Edit Address -->
<div class="neo-modal-backdrop" id="profileAddressModal" role="dialog" aria-modal="true">
    <div class="neo-modal-dialog">
        <div class="neo-modal-header">
            <h3 class="neo-modal-title" id="profileAddrModalTitle">
                <i class="ph-bold ph-map-pin"></i> Manage Address
            </h3>
            <button type="button" class="neo-modal-close-btn" id="btn-close-profile-addr-modal" aria-label="Close modal">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>
        <div class="neo-modal-body">
            <div id="profile-addr-lock-notice" style="background: #FEF3C7; border: 2px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 10px 14px; font-size: 12px; font-weight: 700; color: #92400E; display: flex; align-items: center; gap: 8px;">
                <i class="ph-bold ph-shield-warning" style="font-size: 18px; flex-shrink: 0;"></i>
                <span>Note: If you have active orders, updating this address will apply to future orders. Existing active orders will be delivered to the address locked at checkout.</span>
            </div>

            <form id="profileAddressForm" novalidate autocomplete="off">
                <input type="hidden" id="p_address_id" name="address_id" value="" />
                <div class="neo-form-grid">
                    <div class="neo-form-group">
                        <label class="neo-form-label" for="p_addr_province">Province <span class="req-star">*</span></label>
                        <select id="p_addr_province" name="province" class="neo-form-select" required>
                            <option value="">-- Select Province --</option>
                            <option value="Punjab">Punjab</option>
                            <option value="Sindh">Sindh</option>
                            <option value="Khyber Pakhtunkhwa">Khyber Pakhtunkhwa</option>
                            <option value="Balochistan">Balochistan</option>
                            <option value="Islamabad Capital Territory">Islamabad Capital Territory</option>
                            <option value="Gilgit-Baltistan">Gilgit-Baltistan</option>
                            <option value="Azad Jammu & Kashmir">Azad Jammu & Kashmir</option>
                        </select>
                        <span class="neo-field-error" id="p_err_province"></span>
                    </div>

                    <div class="neo-form-group">
                        <label class="neo-form-label" for="p_addr_district">District <span class="req-star">*</span></label>
                        <input type="text" id="p_addr_district" name="district" class="neo-form-input" placeholder="e.g. Lahore, Rawalpindi" required />
                        <span class="neo-field-error" id="p_err_district"></span>
                    </div>

                    <div class="neo-form-group">
                        <label class="neo-form-label" for="p_addr_city">City <span class="req-star">*</span></label>
                        <input type="text" id="p_addr_city" name="city" class="neo-form-input" placeholder="e.g. Lahore" required />
                        <span class="neo-field-error" id="p_err_city"></span>
                    </div>

                    <div class="neo-form-group">
                        <label class="neo-form-label" for="p_addr_postcode">Postal Code (5 Digits) <span class="req-star">*</span></label>
                        <input type="text" id="p_addr_postcode" name="postcode" class="neo-form-input" maxlength="5" placeholder="e.g. 54000" required />
                        <span class="neo-form-hint">Exactly 5 numeric digits</span>
                        <span class="neo-field-error" id="p_err_postcode"></span>
                    </div>

                    <div class="neo-form-group full-width">
                        <label class="neo-form-label" for="p_addr_contact">Contact Phone (11 Digits) <span class="req-star">*</span></label>
                        <input type="tel" id="p_addr_contact" name="contact" class="neo-form-input" maxlength="11" placeholder="e.g. 03001234567" required />
                        <span class="neo-form-hint">11-digit phone number</span>
                        <span class="neo-field-error" id="p_err_contact"></span>
                    </div>

                    <div class="neo-form-group full-width">
                        <label class="neo-form-label" for="p_addr_address">Permanent Address <span class="req-star">*</span></label>
                        <textarea id="p_addr_address" name="address" rows="3" class="neo-form-textarea" placeholder="House/Flat #, Street address, Area/Colony" required></textarea>
                        <span class="neo-field-error" id="p_err_address"></span>
                    </div>
                </div>
            </form>
        </div>
        <div class="neo-modal-footer">
            <button type="button" class="neo-btn-cancel" id="btn-cancel-profile-addr">Cancel</button>
            <button type="submit" form="profileAddressForm" id="btn-save-profile-addr" class="neo-btn-confirm">
                <i class="ph-bold ph-check-circle"></i> Save Address
            </button>
        </div>
    </div>
</div>

<div class="neo-toast-container" id="neoToastContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const profileModal = document.getElementById('profileAddressModal');
    const form = document.getElementById('profileAddressForm');
    const titleEl = document.getElementById('profileAddrModalTitle');
    const btnAdd = document.getElementById('btn-profile-add-addr');
    const btnClose = document.getElementById('btn-close-profile-addr-modal');
    const btnCancel = document.getElementById('btn-cancel-profile-addr');

    const openProfileModal = (title) => {
        if (titleEl) titleEl.innerHTML = `<i class="ph-bold ph-map-pin"></i> ${title}`;
        profileModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeProfileModal = () => {
        profileModal.classList.remove('active');
        document.body.style.overflow = '';
        form.reset();
        document.getElementById('p_address_id').value = '';
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.neo-field-error').forEach(el => { el.textContent = ''; el.classList.remove('active'); });
    };

    if (btnAdd) {
        btnAdd.addEventListener('click', () => {
            openProfileModal('Add Delivery Address');
        });
    }

    if (btnClose) btnClose.addEventListener('click', closeProfileModal);
    if (btnCancel) btnCancel.addEventListener('click', closeProfileModal);

    if (profileModal) {
        profileModal.addEventListener('click', (e) => {
            if (e.target === profileModal) closeProfileModal();
        });
    }

    document.querySelectorAll('.btn-edit-profile-addr').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('p_address_id').value = btn.getAttribute('data-id');
            document.getElementById('p_addr_province').value = btn.getAttribute('data-province');
            document.getElementById('p_addr_district').value = btn.getAttribute('data-district');
            document.getElementById('p_addr_city').value = btn.getAttribute('data-city');
            document.getElementById('p_addr_postcode').value = btn.getAttribute('data-postcode');
            document.getElementById('p_addr_contact').value = btn.getAttribute('data-contact');
            document.getElementById('p_addr_address').value = btn.getAttribute('data-address');
            openProfileModal('Update Delivery Address');
        });
    });

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const province = document.getElementById('p_addr_province').value.trim();
            const district = document.getElementById('p_addr_district').value.trim();
            const city = document.getElementById('p_addr_city').value.trim();
            const postcode = document.getElementById('p_addr_postcode').value.trim();
            const contact = document.getElementById('p_addr_contact').value.trim();
            const address = document.getElementById('p_addr_address').value.trim();

            if (!province || !district || !city || !address) {
                window.showNeoToast("All fields are required.", "warning");
                return;
            }

            if (!/^[0-9]{5}$/.test(postcode)) {
                window.showNeoToast("Postal code must be exactly 5 digits.", "warning");
                return;
            }

            if (!/^[0-9]{11}$/.test(contact)) {
                window.showNeoToast("Contact number must be exactly 11 digits.", "warning");
                return;
            }

            const btnSave = document.getElementById('btn-save-profile-addr');
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Saving...';

            try {
                const formData = new FormData(form);
                const res = await fetch('/handlers/address.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.status === 'success') {
                    window.showNeoToast(data.message, "success", 5000);
                    closeProfileModal();
                    setTimeout(() => window.location.reload(), 1400);
                } else {
                    window.showNeoToast(data.message || "Failed to save address.", "error");
                }
            } catch (err) {
                window.showNeoToast("Connection error while saving address.", "error");
            } finally {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="ph-bold ph-check-circle"></i> Save Address';
            }
        });
    }
});
</script>

<?php 
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>
