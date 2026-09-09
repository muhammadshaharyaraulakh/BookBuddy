<?php
// Section 1: Data Initialization & Handlers Loading
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/handlers/cartDetails.php";
require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";
?>

<!-- Section 2: Page Header & Notifications Banner -->
<div class="page-header-banner">
    <h1 class="page-header-title">Shopping Cart</h1>
    <p class="page-header-subtitle">Review your selected literary treasures before checkout</p>
</div>

<?php if (!empty($cartNotifications)): ?>
    <div style="max-width: 1100px; margin: 0 auto 20px; padding: 0 24px;">
        <?php foreach ($cartNotifications as $notice): ?>
            <div style="background: #FEF3C7; border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); padding: 14px 18px; font-weight: 700; color: #92400E; display: flex; align-items: center; gap: 10px; box-shadow: var(--neo-shadow-sm); margin-bottom: 10px;">
                <i class="ph-bold ph-warning-circle" style="font-size: 22px;"></i>
                <span><?= htmlspecialchars($notice) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!$user_id): ?>
    <!-- Section 3: Guest User State (Login Required Card) -->
    <div style="max-width: 600px; margin: 40px auto 80px; padding: 0 20px;">
        <div style="background: var(--neo-white); border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); padding: 48px 24px; text-align: center; box-shadow: var(--neo-shadow-lg);">
            <div style="font-size: 48px; margin-bottom: 12px; color: var(--neo-pink);">
                <i class="ph-bold ph-lock-key"></i>
            </div>
            <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Log In to View Your Cart</h2>
            <p style="color: var(--neo-gray-muted); font-size: 15px; margin-bottom: 24px;">Please sign in with your account to access and synchronize your cart across devices.</p>
            <a href="/login" class="neo-btn btn-yellow" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; font-weight: 800; text-decoration: none; border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); box-shadow: var(--neo-shadow); background: var(--neo-yellow); color: var(--neo-black);">
                <i class="ph-bold ph-sign-in"></i> Sign In to Account
            </a>
        </div>
    </div>
<?php elseif (empty($cartItems)): ?>
    <!-- Section 4: Empty Cart State (Browse Books Card) -->
    <div style="max-width: 640px; margin: 40px auto 80px; padding: 0 20px;">
        <div style="background: var(--neo-white); border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); padding: 48px 24px; text-align: center; box-shadow: var(--neo-shadow-lg);">
            <div style="font-size: 52px; margin-bottom: 14px; color: var(--neo-yellow);">
                <i class="ph-bold ph-shopping-cart"></i>
            </div>
            <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Your Cart is Empty</h2>
            <p style="color: var(--neo-gray-muted); font-size: 15px; margin-bottom: 24px;">You have not added any books to your cart yet. Explore our curated selection of fine books.</p>
            <a href="/shop.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; font-weight: 800; text-decoration: none; border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); box-shadow: var(--neo-shadow); background: var(--neo-teal); color: var(--neo-black);">
                <i class="ph-bold ph-storefront"></i> Explore Books
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Section 5: Active Cart Table & Item Rows (Live Prices, Deals, Stock Badges) -->
    <div class="cart-layout" id="cart-main-container">
        <div class="cart-table-card">
            <h2 style="font-size: 20px; font-weight: 800; text-transform: uppercase; padding-bottom: 12px; border-bottom: 2px solid var(--neo-black); display: flex; align-items: center; justify-content: space-between;">
                <span>Your Items (<span id="cart-header-count"><?= count($cartItems) ?></span>)</span>
                <span id="cart-header-stock-badge" style="font-size: 13px; font-weight: 800; color: #991B1B; background: #FEE2E2; border: 1.5px solid var(--neo-black); border-radius: var(--neo-radius-pill); padding: 3px 12px; <?= $hasStockIssue ? '' : 'display: none;' ?>">
                    <i class="ph-bold ph-warning"></i> Stock issues detected
                </span>
            </h2>

            <div id="cart-items-wrapper">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item-row <?= ($item->is_out_of_stock || !empty($item->is_daily_deal)) ? 'item-disabled' : '' ?>" id="cart-row-<?= $item->cart_id ?>" data-cart-id="<?= $item->cart_id ?>" data-stock="<?= (int)$item->Stock ?>" data-daily-deal="<?= !empty($item->is_daily_deal) ? '1' : '0' ?>">
                        <img src="/images/<?= htmlspecialchars($item->coverImage ?? 'logo.png') ?>" alt="<?= htmlspecialchars($item->title) ?>" class="cart-item-img" onerror="this.src='/images/logo.png'" />
                        
                        <div>
                            <a href="/book?id=<?= $item->id ?>" style="font-size: 16px; font-weight: 800; color: var(--neo-black); text-decoration: none;">
                                <?= htmlspecialchars($item->title) ?>
                            </a>
                            <div style="font-size: 13px; font-weight: 600; color: #6B7280; margin-top: 2px;">
                                <?= htmlspecialchars($item->author) ?>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 6px;" id="item-badges-<?= $item->cart_id ?>">
                                <?php if (!empty($item->is_daily_deal)): ?>
                                    <span class="cart-badge badge-danger">
                                        <i class="ph-bold ph-lightning-slash"></i> In Daily Deal (Remove to Checkout)
                                    </span>
                                <?php elseif ($item->is_out_of_stock): ?>
                                    <span class="cart-badge badge-danger">
                                        <i class="ph-bold ph-prohibit"></i> Out of Stock
                                    </span>
                                <?php elseif ((int)$item->Stock < 5): ?>
                                    <span class="cart-badge badge-warning">
                                        <i class="ph-bold ph-warning"></i> Only <?= (int)$item->Stock ?> left in stock
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="font-size: 16px; font-weight: 800; margin-top: 8px; display: flex; align-items: baseline; gap: 8px;">
                                <span class="row-live-price">$<?= number_format($item->effective_price, 2) ?></span>
                                <?php if ($item->Discount_Percentage !== null && $item->Discount_Percentage > 0): ?>
                                    <span style="font-size: 13px; font-weight: 700; color: #9CA3AF; text-decoration: line-through;">
                                        $<?= number_format($item->Original_Price, 2) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Section 6: Interactive Quantity Steppers & Removal Triggers -->
                        <div class="cart-qty-control">
                            <button type="button" class="cart-qty-btn btn-cart-dec" data-cart-id="<?= $item->cart_id ?>" <?= (!empty($item->is_daily_deal) || $item->is_out_of_stock) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?> aria-label="Decrease quantity">&minus;</button>
                            <input type="text" value="<?= $item->cart_quantity ?>" readonly class="cart-qty-input row-qty-input" data-cart-id="<?= $item->cart_id ?>" data-stock="<?= (int)$item->Stock ?>" data-max="<?= min(10, max(1, (int)$item->Stock)) ?>" />
                            <button type="button" class="cart-qty-btn btn-cart-inc" data-cart-id="<?= $item->cart_id ?>" <?= (!empty($item->is_daily_deal) || $item->is_out_of_stock) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?> aria-label="Increase quantity">&plus;</button>
                        </div>

                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="cart-row-subtotal" id="row-subtotal-<?= $item->cart_id ?>" style="font-weight: 800; font-size: 17px; min-width: 70px; text-align: right;">
                                $<?= number_format($item->item_subtotal, 2) ?>
                            </span>
                            <button type="button" class="btn-cart-remove" data-cart-id="<?= $item->cart_id ?>" data-book-title="<?= htmlspecialchars($item->title, ENT_QUOTES) ?>" style="background: none; border: none; cursor: pointer; color: var(--neo-pink); font-size: 20px; padding: 4px; display: inline-flex; align-items: center; justify-content: center;" aria-label="Remove item">
                                <i class="ph-bold ph-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 16px; border-top: 2px solid #E5E7EB; flex-wrap: wrap; gap: 12px;">
                <a href="/shop.php" style="font-size: 14px; font-weight: 800; text-decoration: underline; display: inline-flex; align-items: center; gap: 6px; color: var(--neo-black);">
                    <i class="ph-bold ph-arrow-left"></i> Continue Shopping
                </a>
                <span style="font-size: 13px; font-weight: 700; color: var(--neo-teal); display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph-bold ph-shield-check"></i> Authentic Books &amp; Express Delivery
                </span>
            </div>
        </div>

        <!-- Section 7: Order Summary & Live Financial Totals -->
        <aside class="cart-summary-card">
            <h2 style="font-size: 20px; font-weight: 800; text-transform: uppercase; padding-bottom: 12px; border-bottom: 2px solid var(--neo-black);">
                Order Summary
            </h2>

            <div class="summary-row">
                <span>Subtotal</span>
                <span id="cart-summary-subtotal">$<?= number_format($allTotal, 2) ?></span>
            </div>

            <div class="summary-row">
                <span>Delivery</span>
                <span style="color: var(--neo-teal); font-weight: 800;">Free</span>
            </div>

            <div class="summary-row">
                <span>Payment Method</span>
                <span style="font-weight: 800;">Cash On Delivery</span>
            </div>

            <div class="summary-total-row">
                <span>Total Payable</span>
                <span id="cart-summary-total">$<?= number_format($total, 2) ?></span>
            </div>

            <!-- Section 8: Checkout Action & Stock Compliance Validation -->
            <div id="checkout-container" style="display: flex; flex-direction: column; gap: 12px;">
                <div id="checkout-stock-warning" style="background: #FEE2E2; border: 1.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 10px 14px; font-size: 13px; font-weight: 700; color: #991B1B; <?= $hasStockIssue ? '' : 'display: none;' ?>">
                    <i class="ph-bold ph-warning"></i> Remove out-of-stock items, daily deal items, or reduce over-limit quantities to checkout.
                </div>
                <button type="button" id="btn-checkout-disabled" class="btn-checkout-cod" disabled style="opacity: 0.55; cursor: not-allowed; background: var(--neo-gray-muted); width: 100%; <?= $hasStockIssue ? '' : 'display: none;' ?>">
                    Place Order (COD) <i class="ph-bold ph-prohibit"></i>
                </button>
                <button type="button" id="btn-checkout-active" class="btn-checkout-cod" data-logged-in="<?= $user_id ? '1' : '0' ?>" style="text-align: center; text-decoration: none; width: 100%; <?= $hasStockIssue ? 'display: none;' : 'display: flex;' ?>">
                    Place Order (COD) <i class="ph-bold ph-arrow-right"></i>
                </button>
            </div>
        </aside>
    </div>
<?php endif; ?>

<!-- Section 9: Neo-Brutalist Delivery Address Selection Modal -->
<div class="neo-modal-backdrop" id="selectAddressModal" role="dialog" aria-modal="true" aria-labelledby="selectAddrTitle">
    <div class="neo-modal-dialog">
        <div class="neo-modal-header">
            <h3 class="neo-modal-title" id="selectAddrTitle">
                <i class="ph-bold ph-map-pin-line"></i> Select Delivery Address
            </h3>
            <button type="button" class="neo-modal-close-btn btn-close-select-addr-modal" aria-label="Close modal">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>
        <div class="neo-modal-body">
            <div style="background: #E0F2FE; border: 2px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 10px 14px; font-size: 13px; font-weight: 700; color: #0369A1; display: flex; align-items: center; gap: 8px;">
                <i class="ph-bold ph-info" style="font-size: 18px; flex-shrink: 0;"></i>
                <span>Choose which address to deliver your Cash on Delivery package to.</span>
            </div>

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

<!-- Section 10: Neo-Brutalist Add Address Modal -->
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
    window.isUserLoggedIn = <?= $user_id ? 'true' : 'false' ?>;
</script>

<!-- Section: Neo-Brutalist Toast Notification Container -->
<div class="neo-toast-container" id="neoToastContainer"></div>

<?php 
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>

