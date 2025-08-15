<h2>Laboratory Dashboard</h2>
<hr>

<p>Welcome to the central hub for laboratory operations. From here, you can manage test orders, enter results, and monitor quality control.</p>

<!-- In the future, this dashboard would be populated with dynamic data -->
<div class="dashboard-widgets">
    <div class="widget">
        <h4>Pending Orders</h4>
        <p class="count">0</p>
        <small>Awaiting specimen collection or processing.</small>
    </div>
    <div class="widget">
        <h4>Results to Review</h4>
        <p class="count">0</p>
        <small>Completed tests that need verification.</small>
    </div>
    <div class="widget">
        <h4>Equipment Status</h4>
        <p class="status-ok">All Systems Normal</p>
        <small>No maintenance alerts.</small>
    </div>
</div>

<style>
    .dashboard-widgets {
        display: flex;
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .widget {
        flex: 1;
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .widget h4 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: .5rem;
    }
    .widget .count {
        font-size: 2.5rem;
        font-weight: bold;
        color: #007bff;
        margin: .5rem 0;
    }
    .widget .status-ok {
        font-size: 1.2rem;
        font-weight: bold;
        color: #28a745;
    }
</style>
