<?php
$title = 'Login';
require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="login-container">
  <h2>Login</h2>
  <form class="form-login" action="/login/form-submit" method="post">
    <input type="email" name="email" id="email" placeholder="Enter your email" />
    <input type="password" name="password" id="password" placeholder="Enter your password" />
    <button type="submit">Login</button>
  </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>