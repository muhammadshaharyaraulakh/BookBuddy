<?php
require __DIR__."/../../config/config.php";
require __DIR__."/../../includes/header.php";
?>
    <section class="login">
      <h3>Login</h3>
      <div class="login-form">
        <h4>Login</h4>
        <p>If you have an account with us, please log in.</p>
        <div class="input-form">
  <form action="/auth/login/handler.php" method="post" class="ajax-form">
          <div class="input-field">
            <label for="email">Email</label>
            <input type="email" name="gmail" id="email" placeholder="Your Email">
            <span class="error-text" id="gmail-error" style="color:red; font-size:12px;"></span>
          </div>
          <div class="input-field">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Password">
            <span class="error-text" id="password-error" style="color:red; font-size:12px;"></span>
          </div>
          
          <p>Forgot Password ?<a href="/auth/forgot/forgot.php"> Click Here</a></p>
          <button type="submit">Login Account</button>
          <p>Don't Have an Account ? <a href="/auth/registration/registration.php">Create Account</a></p>
          <span class="error-text" id="general-error" style="color:red; font-size:12px;"></span>
                  </form>
        </div>

      </div>
    </section>
    <?php 
require __DIR__."/../../includes/footer.php";
?>