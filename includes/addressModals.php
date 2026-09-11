<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/config.php";

$modalUserId = !empty($_SESSION['id']) ? (int)$_SESSION['id'] : null;
$userAddresses = [];
if ($modalUserId && isset($connection)) {
    $mAddrStmt = $connection->prepare("
        SELECT id, user_id, province, district, city, postcode, contact, address, created_at 
        FROM user_address 
        WHERE user_id = :uid 
        ORDER BY id DESC
    ");
    $mAddrStmt->execute([':uid' => $modalUserId]);
    $userAddresses = $mAddrStmt->fetchAll(PDO::FETCH_OBJ);
}
?>

<!-- Section: Neo-Brutalist Select Delivery Address Modal -->
<div class="neo-modal-backdrop" id="selectAddressModal" role="dialog" aria-modal="true" aria-labelledby="selectAddrTitle">
    <div class="neo-modal-dialog">
        <div class="neo-modal-header">
            <h3 class="neo-modal-title" id="selectAddrTitle">
                <i class="ph-bold ph-map-pin"></i> Select Delivery Address
            </h3>
            <button type="button" class="neo-modal-close-btn btn-close-select-addr-modal" aria-label="Close modal">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>
        <div class="neo-modal-body">
            <div id="modalOrderContextNotice" style="display:none; margin-bottom: 12px; background: var(--neo-yellow); border: 2px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 8px 12px; font-size: 13px; font-weight: 800; color: var(--neo-black);">
                <i class="ph-bold ph-lightning"></i> <span id="modalOrderContextText">Instant Daily Deal Order</span>
            </div>

            <p style="font-size: 13px; font-weight: 700; color: #4B5563; margin-bottom: 12px;">
                Choose where you want your order delivered with Cash on Delivery:
            </p>

            <div class="address-list-container" id="addressListContainer">
                <?php if (!empty($userAddresses)): ?>
                    <?php foreach ($userAddresses as $idx => $addr): ?>
                        <div class="neo-address-card <?= $idx === 0 ? 'selected' : '' ?>" data-address-id="<?= $addr->id ?>">
                            <input type="radio" name="selected_delivery_address" value="<?= $addr->id ?>" <?= $idx === 0 ? 'checked' : '' ?> style="display:none;" />
                            <div class="address-card-header">
                                <label class="address-card-radio-label">
                                    <span class="address-custom-radio"></span>
                                    <span><?= htmlspecialchars($addr->city) ?>, <?= htmlspecialchars($addr->province) ?></span>
                                </label>
                                <button type="button" class="address-details-toggle" aria-label="Toggle details">
                                    <span>Details</span>
                                    <i class="ph-bold ph-caret-down"></i>
                                </button>
                            </div>
                            <div class="address-expandable-details">
                                <div class="address-detail-item">
                                    <i class="ph-bold ph-phone"></i>
                                    <div><span class="address-detail-label">Contact:</span> <?= htmlspecialchars($addr->contact) ?></div>
                                </div>
                                <div class="address-detail-item">
                                    <i class="ph-bold ph-map-pin"></i>
                                    <div><span class="address-detail-label">District:</span> <?= htmlspecialchars($addr->district ?? 'N/A') ?></div>
                                </div>
                                <div class="address-detail-item">
                                    <i class="ph-bold ph-house-line"></i>
                                    <div><span class="address-detail-label">Address:</span> <?= htmlspecialchars($addr->address) ?></div>
                                </div>
                                <div class="address-detail-item">
                                    <i class="ph-bold ph-mailbox"></i>
                                    <div><span class="address-detail-label">Postal Code:</span> <?= htmlspecialchars($addr->postcode) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" class="neo-btn-add-new-addr" id="btn-switch-to-add-addr">
                <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Add Another Delivery Address
            </button>
        </div>
        <div class="neo-modal-footer">
            <button type="button" class="neo-btn-cancel btn-close-select-addr-modal">Cancel</button>
            <button type="button" id="btn-confirm-place-order" class="neo-btn-confirm">
                <i class="ph-bold ph-lock-key"></i> Confirm &amp; Place Order (COD)
            </button>
        </div>
    </div>
</div>

<!-- Section: Neo-Brutalist Add Address Modal -->
<div class="neo-modal-backdrop" id="addAddressModal" role="dialog" aria-modal="true" aria-labelledby="addAddrTitle">
    <div class="neo-modal-dialog">
        <div class="neo-modal-header">
            <h3 class="neo-modal-title" id="addAddrTitle">
                <i class="ph-bold ph-map-pin-plus"></i> Add Delivery Address
            </h3>
            <button type="button" class="neo-modal-close-btn btn-close-add-addr-modal" aria-label="Close modal">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>
        <div class="neo-modal-body">
            <div style="background: #FEF3C7; border: 2px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 10px 14px; font-size: 13px; font-weight: 700; color: #92400E; display: flex; align-items: center; gap: 8px;">
                <i class="ph-bold ph-shield-check" style="font-size: 18px; flex-shrink: 0;"></i>
                <span>All 5 address details are required for verified Cash on Delivery shipment.</span>
            </div>

            <form id="addAddressForm" novalidate autocomplete="off">
                <div class="neo-form-grid">
                    <div class="neo-form-group">
                        <label class="neo-form-label" for="addr_province">Province <span class="req-star">*</span></label>
                        <select id="addr_province" name="province" class="neo-form-select" required>
                            <option value="">-- Select Province --</option>
                            <option value="Punjab">Punjab</option>
                            <option value="Sindh">Sindh</option>
                            <option value="Khyber Pakhtunkhwa">Khyber Pakhtunkhwa</option>
                            <option value="Balochistan">Balochistan</option>
                            <option value="Islamabad Capital Territory">Islamabad Capital Territory</option>
                            <option value="Gilgit-Baltistan">Gilgit-Baltistan</option>
                            <option value="Azad Jammu & Kashmir">Azad Jammu & Kashmir</option>
                        </select>
                        <span class="neo-field-error" id="err_province"></span>
                    </div>

                    <div class="neo-form-group">
                        <label class="neo-form-label" for="addr_district">District <span class="req-star">*</span></label>
                        <input type="text" id="addr_district" name="district" class="neo-form-input" placeholder="e.g. Lahore, Rawalpindi" required />
                        <span class="neo-field-error" id="err_district"></span>
                    </div>

                    <div class="neo-form-group">
                        <label class="neo-form-label" for="addr_city">City <span class="req-star">*</span></label>
                        <input type="text" id="addr_city" name="city" class="neo-form-input" placeholder="e.g. Lahore" required />
                        <span class="neo-field-error" id="err_city"></span>
                    </div>

                    <div class="neo-form-group">
                        <label class="neo-form-label" for="addr_postcode">Postal Code (5 Digits) <span class="req-star">*</span></label>
                        <input type="text" id="addr_postcode" name="postcode" class="neo-form-input" maxlength="5" placeholder="e.g. 54000" required />
                        <span class="neo-form-hint">Exactly 5 numeric digits</span>
                        <span class="neo-field-error" id="err_postcode"></span>
                    </div>

                    <div class="neo-form-group full-width">
                        <label class="neo-form-label" for="addr_contact">Contact Phone (11 Digits) <span class="req-star">*</span></label>
                        <input type="tel" id="addr_contact" name="contact" class="neo-form-input" maxlength="11" placeholder="e.g. 03001234567" required />
                        <span class="neo-form-hint">11-digit mobile number for courier dispatch</span>
                        <span class="neo-field-error" id="err_contact"></span>
                    </div>

                    <div class="neo-form-group full-width">
                        <label class="neo-form-label" for="addr_address">Permanent Address <span class="req-star">*</span></label>
                        <textarea id="addr_address" name="address" rows="3" class="neo-form-textarea" placeholder="House/Flat #, Street address, Area/Colony, Landmark" required></textarea>
                        <span class="neo-field-error" id="err_address"></span>
                    </div>
                </div>
            </form>
        </div>
        <div class="neo-modal-footer">
            <button type="button" class="neo-btn-cancel btn-close-add-addr-modal">Cancel</button>
            <button type="submit" form="addAddressForm" id="btn-submit-add-addr" class="neo-btn-confirm">
                <i class="ph-bold ph-check-circle"></i> Save Address &amp; Continue
            </button>
        </div>
    </div>
</div>

<script>
    window.initialUserAddresses = <?= json_encode($userAddresses ?? []) ?>;
    window.isUserLoggedIn = <?= $modalUserId ? 'true' : 'false' ?>;
</script>

<div class="neo-toast-container" id="neoToastContainer"></div>
