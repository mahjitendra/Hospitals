<div class="dashboard-welcome">
    <h2><?= $pageHeading ?? 'Dashboard' ?></h2>
    <p>The core MVC framework is up and running. From here, we can start building out the specific modules for the Hospital ERP system.</p>

    <h3>Next Steps:</h3>
    <ul>
        <li>Implement User Authentication (Login, Registration, Roles).</li>
        <li>Build the Patient Management module.</li>
        <li>Develop the Appointment Scheduling system.</li>
        <li>... and much more, based on the project plan.</li>
    </ul>

    <style>
        .dashboard-welcome {
            background-color: #fff;
            padding: 2rem;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        h2 {
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
            margin-top: 0;
        }
        ul {
            list-style-type: '✅';
            padding-left: 1.5rem;
        }
        li {
            padding-left: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</div>
