<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/header.php";
require __DIR__."/../handlers/cartDetails.php";
?>
<link rel="stylesheet" href="/assests/css/filter.css">

      <section class="checkout-section page">
        <h2>Checkout</h2>
        <div class="main">
          <div class="checkout-form">
            <h4>Billing & Shipping Address</h4>
            <form action="/handlers/address.php" method="post">
        <div class="form-container">
            
            <div class="form-control Country-field">
                <select id="province" class="select-box" name="province" style="border: 1px solid #f0f0f0;padding: 5px 10px;height: 45px;border-radius: 5px;width: 100%; outline: none;">
                    <option value="">Select Province</option>
                    <option value="PB">Punjab</option>
                    <option value="SD">Sindh</option>
                    <option value="KP">KPK</option>
                    <option value="BA">Balochistan</option>
                    <option value="IS">Islamabad</option>
                </select>
            </div>

            <div class="input-field" style="margin-top: 10px;">
                <select id="city" name="city" style="border: 1px solid #f0f0f0;padding: 5px 10px;height: 45px;border-radius: 5px;width: 100%; outline: none;" disabled>
                    <option value="">Select City</option>
                </select>
            </div>

            <div class="input-field"  style="margin-top: 10px;">
                <select id="postcode" name="postcode" style="border: 1px solid #f0f0f0;padding: 5px 10px;height: 45px;border-radius: 5px;width: 100%; outline: none;" disabled>
                    <option value="">Select Postcode</option>
                </select>
            </div>
             <input type="text" placeholder="Phone no." maxlength="11" name="contact">
             <div class="address-field">
                <textarea rows="3" placeholder="Address" name="address"></textarea>
              </div>

            <button style="margin-top: 15px; padding: 10px 20px;">Add Address</button>
        </div>
    </form>
          </div>
          <div class="your-order">
            <h4>Your Order</h4>
            <div class="order-table">
              <table cellspacing="0">
                <tr class="heading">
                  <th>Image</th>
                  <th> Name</th>
                  <th>Total</th>
                </tr>
                <?php foreach ($cartItems as $item): ?>
                  <?php
                    $price = ($item->Discount_Percentage === NULL) ? $item->Original_Price : $item->Discount_Price;
                    $lineTotal = $price * $item->quantity;
                ?>
                <tr>
                  <td>
                    <img src="/images/<?= htmlspecialchars($item->coverImage) ?>" alt="img">
                  </td>
                  <td><?= htmlspecialchars($item->title) ?></td>
                   <td><?= htmlspecialchars($lineTotal) ?> $</td>
                </tr>
                <?php endforeach; ?>
              </table>
            </div>
          </div>
        </div>
      </section>
      <section class="detail-payment">
            <div class="summary-section">
        <h4>Cart Subtotal</h4>
        <div class="order-detail-table">
            <table>
                <tr>
                    <td>Order Subtotal</td>
                    <td><?= htmlspecialchars($allTotal) ?>$</td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td><?= $shipping ?>$</td>
                </tr>

                <tr>
                    <td>Total</td>
                    <td><?= htmlspecialchars($total) ?>$</td>
                </tr>
            </table>
            <button><a href="/pages/checkout.php">Proceed To Checkout</a></button>
        </div>
    </div>
        <div class="payment-section">
          <h4>Payment Method</h4>
          <form action="/handlers/process_payment.php" method="POST" class="payment-form">
            <div class="payment-option">
              <select name="payment_method" readonly style="pointer-events: none; background-color: #f5f5f5;">
                <option value="Cash On Delivery" selected>Cash On Delivery (COD)</option>
              </select>
              <p style="font-size: 0.9rem; color: #666; margin-top: 10px;">
                  All online payments are disabled. Please pay with cash when your books are delivered.
              </p>
            </div>
            
            <button type="submit" style="margin-top: 20px;">Place Order Now</button>
          </form>
        </div>
      </section>
      <script>
        const CSC_API_KEY = '9d4cd266812e9884316769b1fb3010b6cf88daccf5380cb51535068cdadc20be'; // Get from countrystatecity.in
        const GEO_USERNAME = 'shaharyar786'; // Get from geonames.org

        const provinceSelect = document.getElementById('province');
        const citySelect = document.getElementById('city');
        const postcodeSelect = document.getElementById('postcode');

        // Headers for CountryStateCity API
        var headers = new Headers();
        headers.append("X-CSCAPI-KEY", CSC_API_KEY);
        var requestOptions = { method: 'GET', headers: headers, redirect: 'follow' };

        // 1. PROVINCE CHANGED -> FETCH CITIES
        provinceSelect.addEventListener('change', function() {
            const provinceCode = this.value;
            
            // Reset Cities & Postcodes
            citySelect.innerHTML = '<option value="">Select City</option>';
            postcodeSelect.innerHTML = '<option value="">Select Postcode</option>';
            citySelect.disabled = true;
            postcodeSelect.disabled = true;

            if (provinceCode) {
                citySelect.innerHTML = '<option>Loading</option>';
                
                fetch(`https://api.countrystatecity.in/v1/countries/PK/states/${provinceCode}/cities`, requestOptions)
                .then(response => response.json())
                .then(data => {
                    citySelect.innerHTML = '<option value="">Select City</option>';
                    data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.name;
                        option.textContent = city.name;
                        citySelect.appendChild(option);
                    });
                    citySelect.disabled = false;
                });
            }
        });

        // 2. CITY CHANGED -> FETCH POSTCODES (GeoNames)
        citySelect.addEventListener('change', function() {
            const cityName = this.value;

            // Reset Postcodes
            postcodeSelect.innerHTML = '<option value="">Select Postcode</option>';
            postcodeSelect.disabled = true;

            if (cityName) {
                postcodeSelect.innerHTML = '<option>Fetching codes</option>';

                // GeoNames API Call
                // filters: placename=Lahore, country=PK, maxRows=30
                const url = `http://api.geonames.org/postalCodeSearchJSON?placename=${cityName}&country=PK&maxRows=50&username=${GEO_USERNAME}`;

                fetch(url)
                .then(response => response.json())
                .then(data => {
                    postcodeSelect.innerHTML = '<option value="">Select Postcode</option>';
                    
                    if (data.postalCodes && data.postalCodes.length > 0) {
                        // Use a Set to avoid duplicate codes
                        const uniqueCodes = new Set();

                        data.postalCodes.forEach(item => {
                            // Format: "54000 (Lahore GPO)"
                            const code = item.postalCode;
                            const place = item.placeName;
                            
                            if(!uniqueCodes.has(code)){
                                uniqueCodes.add(code);
                                const option = document.createElement('option');
                                option.value = code;
                                option.textContent = `${code} - ${place}`;
                                postcodeSelect.appendChild(option);
                            }
                        });
                        postcodeSelect.disabled = false;
                    } else {
                        postcodeSelect.innerHTML = '<option>No codes found</option>';
                    }
                })
                .catch(err => {
                    console.error("GeoNames Error:", err);
                    postcodeSelect.innerHTML = '<option>Error fetching codes</option>';
                });
            }
        });
      </script>
<?php
require __DIR__."/../includes/footer.php";
?>