<h2>Reports & Analytics Dashboard</h2>
<hr>

<p>Select a report to view from the list below.</p>

<div class="report-links">
    <div class="report-link-card">
        <h4>Patient Reports</h4>
        <ul>
            <li><a href="/reports/analytics/patient-demographics">Patient Demographics</a></li>
        </ul>
    </div>
    <div class="report-link-card">
        <h4>Financial Reports</h4>
        <ul>
            <li><a href="/reports/analytics/financial-summary">Financial Summary</a></li>
        </ul>
    </div>
    <div class="report-link-card">
        <h4>Inventory Reports</h4>
        <ul>
            <li><a href="/reports/analytics/inventory-status">Inventory Status</a></li>
        </ul>
    </div>
</div>

<style>
    .report-links {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .report-link-card {
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .report-link-card h4 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: .5rem;
    }
    .report-link-card ul {
        list-style: none;
        padding: 0;
    }
    .report-link-card li a {
        text-decoration: none;
        color: #007bff;
        display: block;
        padding: .5rem 0;
    }
</style>
