<div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            Welcome to 
                            <span class="text-gradient">College ERP</span>
                        </h1>
                        <p class="hero-subtitle">
                            Comprehensive Education Management System designed to streamline 
                            academic operations, enhance communication, and improve institutional efficiency.
                        </p>
                        
                        <div class="hero-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?= number_format($stats['total_students'] ?? 0) ?>+</div>
                                <div class="stat-label">Students</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?= number_format($stats['total_faculty'] ?? 0) ?>+</div>
                                <div class="stat-label">Faculty</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?= number_format($stats['total_courses'] ?? 0) ?>+</div>
                                <div class="stat-label">Courses</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?= $stats['years_of_excellence'] ?? 0 ?>+</div>
                                <div class="stat-label">Years</div>
                            </div>
                        </div>
                        
                        <div class="hero-buttons">
                            <a href="/admissions/apply" class="btn btn-hero-primary">
                                <i class="fas fa-graduation-cap me-2"></i>
                                Apply for Admission
                            </a>
                            <a href="/login" class="btn btn-hero-outline">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Student/Faculty Login
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="hero-image">
                        <div class="floating-cards">
                            <div class="floating-card card-1">
                                <i class="fas fa-user-graduate"></i>
                                <span>Student Management</span>
                            </div>
                            <div class="floating-card card-2">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Faculty Portal</span>
                            </div>
                            <div class="floating-card card-3">
                                <i class="fas fa-chart-line"></i>
                                <span>Analytics & Reports</span>
                            </div>
                            <div class="floating-card card-4">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Mobile Access</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Comprehensive ERP Features</h2>
                <p class="section-subtitle">
                    Everything you need to manage your educational institution efficiently
                </p>
            </div>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="feature-title">Student Management</h5>
                    <p class="feature-description">
                        Complete student lifecycle management from admission to graduation
                    </p>
                    <ul class="feature-list">
                        <li>Admission Processing</li>
                        <li>Student Profiles</li>
                        <li>Document Management</li>
                        <li>Parent Communication</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h5 class="feature-title">Faculty Portal</h5>
                    <p class="feature-description">
                        Comprehensive faculty management with performance tracking
                    </p>
                    <ul class="feature-list">
                        <li>Faculty Profiles</li>
                        <li>Attendance Management</li>
                        <li>Salary & Payroll</li>
                        <li>Leave Management</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h5 class="feature-title">Academic Management</h5>
                    <p class="feature-description">
                        Complete academic structure with courses, subjects, and timetables
                    </p>
                    <ul class="feature-list">
                        <li>Course Management</li>
                        <li>Timetable Scheduling</li>
                        <li>Syllabus Management</li>
                        <li>Academic Calendar</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h5 class="feature-title">Examination System</h5>
                    <p class="feature-description">
                        Complete examination management with result processing
                    </p>
                    <ul class="feature-list">
                        <li>Exam Scheduling</li>
                        <li>Result Processing</li>
                        <li>Grade Management</li>
                        <li>Certificate Generation</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h5 class="feature-title">Fee Management</h5>
                    <p class="feature-description">
                        Comprehensive fee collection with online payment integration
                    </p>
                    <ul class="feature-list">
                        <li>Fee Structure</li>
                        <li>Online Payments</li>
                        <li>Receipt Generation</li>
                        <li>Scholarship Management</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h5 class="feature-title">Reports & Analytics</h5>
                    <p class="feature-description">
                        Detailed reports and analytics for informed decision making
                    </p>
                    <ul class="feature-list">
                        <li>Performance Reports</li>
                        <li>Financial Analytics</li>
                        <li>Attendance Reports</li>
                        <li>Custom Reports</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <?php if (!empty($announcements)): ?>
    <section class="announcements-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Latest Announcements</h2>
                <p class="section-subtitle">Stay updated with the latest news and announcements</p>
            </div>
            
            <div class="announcements-grid">
                <?php foreach ($announcements as $announcement): ?>
                    <div class="announcement-card">
                        <div class="announcement-header">
                            <div class="announcement-date">
                                <div class="date"><?= date('d', strtotime($announcement['created_at'])) ?></div>
                                <div class="month"><?= date('M', strtotime($announcement['created_at'])) ?></div>
                            </div>
                            <div class="announcement-category">
                                <span class="badge bg-primary"><?= ucfirst($announcement['category'] ?? 'General') ?></span>
                            </div>
                        </div>
                        <div class="announcement-content">
                            <h6 class="announcement-title"><?= $announcement['title'] ?></h6>
                            <p class="announcement-excerpt">
                                <?= str_limit($announcement['content'], 150) ?>
                            </p>
                            <a href="/announcements/<?= $announcement['id'] ?>" class="read-more">
                                Read More <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="/announcements" class="btn btn-outline-primary">
                    View All Announcements
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Events Section -->
    <?php if (!empty($events)): ?>
    <section class="events-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Upcoming Events</h2>
                <p class="section-subtitle">Don't miss out on important events and activities</p>
            </div>
            
            <div class="events-timeline">
                <?php foreach ($events as $event): ?>
                    <div class="event-item">
                        <div class="event-date">
                            <div class="date"><?= date('d', strtotime($event['event_date'])) ?></div>
                            <div class="month"><?= date('M', strtotime($event['event_date'])) ?></div>
                            <div class="year"><?= date('Y', strtotime($event['event_date'])) ?></div>
                        </div>
                        <div class="event-content">
                            <h6 class="event-title"><?= $event['title'] ?></h6>
                            <p class="event-description"><?= $event['description'] ?></p>
                            <div class="event-meta">
                                <span class="event-time">
                                    <i class="fas fa-clock me-1"></i>
                                    <?= date('H:i', strtotime($event['start_time'])) ?>
                                </span>
                                <span class="event-location">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= $event['location'] ?? 'TBA' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="/events" class="btn btn-outline-primary">
                    View All Events
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content text-center">
                <h2 class="cta-title">Ready to Get Started?</h2>
                <p class="cta-subtitle">
                    Join thousands of students and faculty using our comprehensive ERP system
                </p>
                <div class="cta-buttons">
                    <a href="/admissions/apply" class="btn btn-hero-primary">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Apply for Admission
                    </a>
                    <a href="/login" class="btn btn-hero-outline">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Access Portal
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.home-page {
    overflow-x: hidden;
}

.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 2;
    padding: 4rem 0;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 1.5rem;
}

.text-gradient {
    background: linear-gradient(45deg, #fbbf24, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.2rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin: 2rem 0;
    padding: 1.5rem;
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    display: block;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.floating-cards {
    position: relative;
    height: 400px;
    margin: 2rem 0;
}

.floating-card {
    position: absolute;
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    min-width: 150px;
    animation: float 6s ease-in-out infinite;
}

.floating-card i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #3b82f6;
}

.floating-card span {
    font-weight: 500;
    color: #1f2937;
    font-size: 0.9rem;
}

.card-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.card-2 {
    top: 10%;
    right: 20%;
    animation-delay: 1.5s;
}

.card-3 {
    bottom: 30%;
    left: 20%;
    animation-delay: 3s;
}

.card-4 {
    bottom: 20%;
    right: 10%;
    animation-delay: 4.5s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

.features-section {
    padding: 5rem 0;
    background: white;
}

.section-header {
    margin-bottom: 4rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #6b7280;
    max-width: 600px;
    margin: 0 auto;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    text-align: center;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 1.5rem;
}

.feature-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
}

.feature-description {
    color: #6b7280;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.feature-list {
    list-style: none;
    padding: 0;
    text-align: left;
}

.feature-list li {
    padding: 0.5rem 0;
    color: #374151;
    position: relative;
    padding-left: 1.5rem;
}

.feature-list li::before {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    color: #10b981;
    position: absolute;
    left: 0;
}

.announcements-section {
    padding: 5rem 0;
    background: #f8f9fa;
}

.announcements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.announcement-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.announcement-card:hover {
    transform: translateY(-3px);
}

.announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e5e7eb;
}

.announcement-date {
    text-align: center;
}

.announcement-date .date {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1f2937;
}

.announcement-date .month {
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
}

.announcement-content {
    padding: 1.5rem;
}

.announcement-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
}

.announcement-excerpt {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.read-more {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
}

.read-more:hover {
    color: #1d4ed8;
}

.events-section {
    padding: 5rem 0;
    background: white;
}

.events-timeline {
    max-width: 800px;
    margin: 0 auto;
}

.event-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
}

.event-date {
    margin-right: 2rem;
    text-align: center;
    min-width: 80px;
}

.event-date .date {
    font-size: 1.5rem;
    font-weight: 700;
    color: #3b82f6;
}

.event-date .month,
.event-date .year {
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
}

.event-content {
    flex: 1;
}

.event-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.event-description {
    color: #6b7280;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.event-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #9ca3af;
}

.cta-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #1f2937, #374151);
    color: white;
    text-align: center;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.cta-subtitle {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .hero-buttons {
        flex-direction: column;
    }
    
    .floating-cards {
        display: none;
    }
    
    .feature-grid {
        grid-template-columns: 1fr;
    }
    
    .announcements-grid {
        grid-template-columns: 1fr;
    }
    
    .event-item {
        flex-direction: column;
        text-align: center;
    }
    
    .event-date {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<script>
// Home page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Animate stats on scroll
    animateStatsOnScroll();
    
    // Initialize floating cards animation
    initializeFloatingCards();
    
    // Smooth scrolling for anchor links
    initializeSmoothScrolling();
});

function animateStatsOnScroll() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumbers = entry.target.querySelectorAll('.stat-number');
                statNumbers.forEach(stat => {
                    animateNumber(stat);
                });
            }
        });
    });
    
    const statsSection = document.querySelector('.hero-stats');
    if (statsSection) {
        observer.observe(statsSection);
    }
}

function animateNumber(element) {
    const target = parseInt(element.textContent.replace(/[^\d]/g, ''));
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        
        const suffix = element.textContent.includes('+') ? '+' : '';
        element.textContent = Math.floor(current).toLocaleString() + suffix;
    }, 16);
}

function initializeFloatingCards() {
    const cards = document.querySelectorAll('.floating-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 1.5}s`;
    });
}

function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Parallax effect for hero section
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const parallax = document.querySelector('.hero-section');
    
    if (parallax) {
        const speed = scrolled * 0.5;
        parallax.style.transform = `translateY(${speed}px)`;
    }
});
</script>