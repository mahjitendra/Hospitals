<h2>EMR Dashboard</h2>
<hr>

<p>Welcome to the Electronic Medical Records dashboard. From here you can manage all clinical aspects of patient care.</p>

<div class="dashboard-widgets">
    <div class="widget">
        <h4>Recent Patients</h4>
        <p class="count">0</p>
    </div>
    <div class="widget">
        <h4>Upcoming Appointments</h4>
        <p class="count">0</p>
    </div>
    <div class="widget">
        <h4>Open Clinical Alerts</h4>
        <p class="count">0</p>
    </div>
</div>

<style>
    /* Using styles from previous views */
    .dashboard-widgets { display: flex; gap: 1.5rem; margin-top: 2rem; }
    .widget { flex: 1; background-color: #fff; padding: 1.5rem; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .widget h4 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: .5rem; }
    .widget .count { font-size: 2.5rem; font-weight: bold; color: #007bff; margin: .5rem 0; }
</style>
