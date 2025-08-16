<h2>Admin Dashboard</h2>
<hr>

<p>Welcome to the administration panel. From here you can manage users, roles, system settings, and more.</p>

<div class="admin-links">
    <a href="/admin/users" class="admin-link-card">
        <h4>User Management</h4>
        <p>Manage user accounts and assign roles.</p>
    </a>
    <a href="/admin/roles" class="admin-link-card">
        <h4>Role Management</h4>
        <p>Define roles and their permissions.</p>
    </a>
    <a href="#" class="admin-link-card disabled">
        <h4>System Configuration</h4>
        <p>Manage system-wide settings.</p>
    </a>
</div>

<style>
    .admin-links {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .admin-link-card {
        display: block;
        text-decoration: none;
        color: #333;
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: box-shadow .2s ease-in-out;
    }
    .admin-link-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .admin-link-card.disabled {
        background-color: #f8f9fa;
        color: #6c757d;
        pointer-events: none;
    }
    .admin-link-card h4 {
        margin-top: 0;
    }
</style>
