<h2>Quality Management Dashboard</h2>
<hr>

<p>This dashboard provides an overview of quality control metrics, ongoing incidents, and risk assessments.</p>

<div class="dashboard-widgets">
    <div class="widget">
        <h4>Open Incidents</h4>
        <p class="count">0</p>
        <a href="/quality/incidents">View Incidents</a>
    </div>
    <div class="widget">
        <h4>Pending Risk Assessments</h4>
        <p class="count">0</p>
        <a href="#">View Assessments</a>
    </div>
    <div class="widget">
        <h4>Compliance Status</h4>
        <p class="status-ok">100% Compliant</p>
        <a href="#">View Audits</a>
    </div>
</div>

<style>
    /* Using styles from previous views */
    .dashboard-widgets { display: flex; gap: 1.5rem; margin-top: 2rem; }
    .widget { flex: 1; background-color: #fff; padding: 1.5rem; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .widget h4 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: .5rem; }
    .widget .count { font-size: 2.5rem; font-weight: bold; color: #dc3545; margin: .5rem 0; }
    .widget .status-ok { font-size: 1.2rem; font-weight: bold; color: #28a745; }
    .widget a { text-decoration: none; color: #007bff; }
</style>
