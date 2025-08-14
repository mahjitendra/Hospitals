<h2>Login to your Account</h2>

<?php
// Display any success messages from registration
$session = new \App\Libraries\Core\Session();
$success_message = $session->getFlash('success');
if ($success_message):
?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success_message) ?>
    </div>
<?php endif; ?>

<?php
// Display any error messages from a failed login attempt
$error_message = $session->getFlash('error');
if ($error_message):
?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>


<form action="/login" method="POST">
    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn">Login</button>
</form>

<p class="text-center mt-3">
    <a href="/register" class="auth-link">Don't have an account? Register</a>
</p>
