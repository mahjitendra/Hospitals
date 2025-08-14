<?php $this->layout('layouts::auth'); ?>

<h2>Create an Account</h2>

<?php if (isset($errors) && !empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="/register" method="POST">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="password_confirm">Confirm Password</label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
    </div>
    <button type="submit" class="btn">Register</button>
</form>

<p class="text-center mt-3">
    <a href="/login" class="auth-link">Already have an account? Login</a>
</p>
