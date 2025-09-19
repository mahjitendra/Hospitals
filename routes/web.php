<?php

/**
 * Web Routes
 * 
 * Define all web routes for the application
 */

// Home route
$router->get('/', [HomeController::class, 'index']);

// Authentication routes
$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegisterForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPasswordForm']);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink']);
$router->get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes (require authentication)
$router->group([AuthMiddleware::class], function($router) {
    
    // Dashboard routes
    $router->get('/dashboard', [DashboardController::class, 'index']);
    
    // Profile routes
    $router->get('/profile', [ProfileController::class, 'show']);
    $router->post('/profile', [ProfileController::class, 'update']);
    $router->get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm']);
    $router->post('/profile/change-password', [ProfileController::class, 'changePassword']);
    
    // Admin routes
    $router->group([RoleMiddleware::class . ':admin'], function($router) {
        $router->prefix('/admin', function($router) {
            $router->get('/', [AdminController::class, 'index']);
            
            // User management
            $router->get('/users', [UserController::class, 'index']);
            $router->get('/users/create', [UserController::class, 'create']);
            $router->post('/users', [UserController::class, 'store']);
            $router->get('/users/{id}', [UserController::class, 'show']);
            $router->get('/users/{id}/edit', [UserController::class, 'edit']);
            $router->post('/users/{id}', [UserController::class, 'update']);
            $router->post('/users/{id}/delete', [UserController::class, 'destroy']);
            
            // Role management
            $router->get('/roles', [RoleController::class, 'index']);
            $router->get('/roles/create', [RoleController::class, 'create']);
            $router->post('/roles', [RoleController::class, 'store']);
            $router->get('/roles/{id}/edit', [RoleController::class, 'edit']);
            $router->post('/roles/{id}', [RoleController::class, 'update']);
            $router->post('/roles/{id}/delete', [RoleController::class, 'destroy']);
            
            // Permission management
            $router->get('/permissions', [PermissionController::class, 'index']);
            $router->get('/permissions/create', [PermissionController::class, 'create']);
            $router->post('/permissions', [PermissionController::class, 'store']);
            
            // Settings
            $router->get('/settings', [SettingsController::class, 'index']);
            $router->post('/settings', [SettingsController::class, 'update']);
            
            // Backup management
            $router->get('/backups', [BackupController::class, 'index']);
            $router->post('/backups/create', [BackupController::class, 'create']);
            $router->get('/backups/{id}/download', [BackupController::class, 'download']);
            $router->post('/backups/{id}/restore', [BackupController::class, 'restore']);
            $router->post('/backups/{id}/delete', [BackupController::class, 'delete']);
            
            // Logs
            $router->get('/logs', [LogController::class, 'index']);
            $router->get('/logs/{file}', [LogController::class, 'show']);
            $router->post('/logs/{file}/clear', [LogController::class, 'clear']);
            
            // Reports
            $router->get('/reports', [ReportController::class, 'index']);
            $router->get('/reports/users', [ReportController::class, 'users']);
            $router->get('/reports/activity', [ReportController::class, 'activity']);
            $router->get('/reports/system', [ReportController::class, 'system']);
        });
    });
    
    // Student routes
    $router->prefix('/students', function($router) {
        $router->get('/', [StudentController::class, 'index']);
        $router->get('/create', [StudentController::class, 'create']);
        $router->post('/', [StudentController::class, 'store']);
        $router->get('/{id}', [StudentController::class, 'show']);
        $router->get('/{id}/edit', [StudentController::class, 'edit']);
        $router->post('/{id}', [StudentController::class, 'update']);
        $router->post('/{id}/delete', [StudentController::class, 'destroy']);
        
        // Student admission
        $router->get('/admission/create', [AdmissionController::class, 'create']);
        $router->post('/admission', [AdmissionController::class, 'store']);
        $router->get('/admission/{id}', [AdmissionController::class, 'show']);
        $router->get('/admission/{id}/approve', [AdmissionController::class, 'approve']);
        $router->get('/admission/{id}/reject', [AdmissionController::class, 'reject']);
        
        // Student documents
        $router->get('/{id}/documents', [DocumentController::class, 'index']);
        $router->get('/{id}/documents/upload', [DocumentController::class, 'create']);
        $router->post('/{id}/documents', [DocumentController::class, 'store']);
        $router->get('/documents/{docId}/download', [DocumentController::class, 'download']);
        $router->post('/documents/{docId}/delete', [DocumentController::class, 'destroy']);
        
        // Parent/Guardian management
        $router->get('/{id}/parents', [ParentController::class, 'index']);
        $router->get('/{id}/parents/create', [ParentController::class, 'create']);
        $router->post('/{id}/parents', [ParentController::class, 'store']);
        $router->get('/parents/{parentId}/edit', [ParentController::class, 'edit']);
        $router->post('/parents/{parentId}', [ParentController::class, 'update']);
        
        // Transfer
        $router->get('/{id}/transfer', [TransferController::class, 'create']);
        $router->post('/{id}/transfer', [TransferController::class, 'store']);
    });
    
    // Faculty routes
    $router->prefix('/faculty', function($router) {
        $router->get('/', [FacultyController::class, 'index']);
        $router->get('/create', [FacultyController::class, 'create']);
        $router->post('/', [FacultyController::class, 'store']);
        $router->get('/{id}', [FacultyController::class, 'show']);
        $router->get('/{id}/edit', [FacultyController::class, 'edit']);
        $router->post('/{id}', [FacultyController::class, 'update']);
        $router->post('/{id}/delete', [FacultyController::class, 'destroy']);
        
        // Teachers
        $router->get('/teachers', [TeacherController::class, 'index']);
        $router->get('/teachers/create', [TeacherController::class, 'create']);
        $router->post('/teachers', [TeacherController::class, 'store']);
        $router->get('/teachers/{id}', [TeacherController::class, 'show']);
        $router->get('/teachers/{id}/edit', [TeacherController::class, 'edit']);
        $router->post('/teachers/{id}', [TeacherController::class, 'update']);
        
        // Staff
        $router->get('/staff', [StaffController::class, 'index']);
        $router->get('/staff/create', [StaffController::class, 'create']);
        $router->post('/staff', [StaffController::class, 'store']);
        $router->get('/staff/{id}', [StaffController::class, 'show']);
        $router->get('/staff/{id}/edit', [StaffController::class, 'edit']);
        $router->post('/staff/{id}', [StaffController::class, 'update']);
        
        // Departments
        $router->get('/departments', [DepartmentController::class, 'index']);
        $router->get('/departments/create', [DepartmentController::class, 'create']);
        $router->post('/departments', [DepartmentController::class, 'store']);
        $router->get('/departments/{id}/edit', [DepartmentController::class, 'edit']);
        $router->post('/departments/{id}', [DepartmentController::class, 'update']);
        
        // Salary management
        $router->get('/salary', [SalaryController::class, 'index']);
        $router->get('/salary/{id}', [SalaryController::class, 'show']);
        $router->get('/salary/{id}/edit', [SalaryController::class, 'edit']);
        $router->post('/salary/{id}', [SalaryController::class, 'update']);
        
        // Leave management
        $router->get('/leave', [LeaveController::class, 'index']);
        $router->get('/leave/create', [LeaveController::class, 'create']);
        $router->post('/leave', [LeaveController::class, 'store']);
        $router->get('/leave/{id}', [LeaveController::class, 'show']);
        $router->post('/leave/{id}/approve', [LeaveController::class, 'approve']);
        $router->post('/leave/{id}/reject', [LeaveController::class, 'reject']);
    });
    
    // Academic routes
    $router->prefix('/academic', function($router) {
        // Courses
        $router->get('/courses', [CourseController::class, 'index']);
        $router->get('/courses/create', [CourseController::class, 'create']);
        $router->post('/courses', [CourseController::class, 'store']);
        $router->get('/courses/{id}', [CourseController::class, 'show']);
        $router->get('/courses/{id}/edit', [CourseController::class, 'edit']);
        $router->post('/courses/{id}', [CourseController::class, 'update']);
        
        // Subjects
        $router->get('/subjects', [SubjectController::class, 'index']);
        $router->get('/subjects/create', [SubjectController::class, 'create']);
        $router->post('/subjects', [SubjectController::class, 'store']);
        $router->get('/subjects/{id}/edit', [SubjectController::class, 'edit']);
        $router->post('/subjects/{id}', [SubjectController::class, 'update']);
        
        // Classes
        $router->get('/classes', [ClassController::class, 'index']);
        $router->get('/classes/create', [ClassController::class, 'create']);
        $router->post('/classes', [ClassController::class, 'store']);
        $router->get('/classes/{id}', [ClassController::class, 'show']);
        $router->get('/classes/{id}/edit', [ClassController::class, 'edit']);
        $router->post('/classes/{id}', [ClassController::class, 'update']);
        
        // Timetable
        $router->get('/timetable', [TimetableController::class, 'index']);
        $router->get('/timetable/create', [TimetableController::class, 'create']);
        $router->post('/timetable', [TimetableController::class, 'store']);
        $router->get('/timetable/{id}/edit', [TimetableController::class, 'edit']);
        $router->post('/timetable/{id}', [TimetableController::class, 'update']);
        
        // Sessions
        $router->get('/sessions', [SessionController::class, 'index']);
        $router->get('/sessions/create', [SessionController::class, 'create']);
        $router->post('/sessions', [SessionController::class, 'store']);
        $router->get('/sessions/{id}/edit', [SessionController::class, 'edit']);
        $router->post('/sessions/{id}', [SessionController::class, 'update']);
        
        // Calendar
        $router->get('/calendar', [CalendarController::class, 'index']);
        $router->get('/calendar/events', [CalendarController::class, 'events']);
        $router->post('/calendar/events', [CalendarController::class, 'store']);
        $router->post('/calendar/events/{id}', [CalendarController::class, 'update']);
        $router->post('/calendar/events/{id}/delete', [CalendarController::class, 'destroy']);
    });
    
    // Examination routes
    $router->prefix('/examinations', function($router) {
        $router->get('/', [ExamController::class, 'index']);
        $router->get('/create', [ExamController::class, 'create']);
        $router->post('/', [ExamController::class, 'store']);
        $router->get('/{id}', [ExamController::class, 'show']);
        $router->get('/{id}/edit', [ExamController::class, 'edit']);
        $router->post('/{id}', [ExamController::class, 'update']);
        
        // Grades and marks
        $router->get('/{id}/grades', [GradeController::class, 'index']);
        $router->get('/{id}/grades/create', [GradeController::class, 'create']);
        $router->post('/{id}/grades', [GradeController::class, 'store']);
        $router->get('/grades/{gradeId}/edit', [GradeController::class, 'edit']);
        $router->post('/grades/{gradeId}', [GradeController::class, 'update']);
        
        // Results
        $router->get('/results', [ResultController::class, 'index']);
        $router->get('/results/{id}', [ResultController::class, 'show']);
        $router->get('/results/{id}/publish', [ResultController::class, 'publish']);
        $router->get('/results/{id}/pdf', [ResultController::class, 'downloadPdf']);
        
        // Certificates
        $router->get('/certificates', [CertificateController::class, 'index']);
        $router->get('/certificates/{id}', [CertificateController::class, 'show']);
        $router->get('/certificates/{id}/download', [CertificateController::class, 'download']);
        
        // Hall tickets
        $router->get('/hall-tickets', [HallTicketController::class, 'index']);
        $router->get('/hall-tickets/{id}', [HallTicketController::class, 'show']);
        $router->get('/hall-tickets/{id}/download', [HallTicketController::class, 'download']);
    });
    
    // Attendance routes
    $router->prefix('/attendance', function($router) {
        $router->get('/', [AttendanceController::class, 'index']);
        
        // Student attendance
        $router->get('/students', [StudentAttendanceController::class, 'index']);
        $router->get('/students/mark', [StudentAttendanceController::class, 'create']);
        $router->post('/students/mark', [StudentAttendanceController::class, 'store']);
        $router->get('/students/bulk', [StudentAttendanceController::class, 'bulk']);
        $router->post('/students/bulk', [StudentAttendanceController::class, 'storeBulk']);
        $router->get('/students/report', [StudentAttendanceController::class, 'report']);
        
        // Faculty attendance
        $router->get('/faculty', [FacultyAttendanceController::class, 'index']);
        $router->get('/faculty/mark', [FacultyAttendanceController::class, 'create']);
        $router->post('/faculty/mark', [FacultyAttendanceController::class, 'store']);
        $router->get('/faculty/report', [FacultyAttendanceController::class, 'report']);
        
        // Biometric integration
        $router->get('/biometric', [BiometricController::class, 'index']);
        $router->post('/biometric/sync', [BiometricController::class, 'sync']);
        
        // Reports
        $router->get('/reports', [AttendanceReportController::class, 'index']);
        $router->get('/reports/daily', [AttendanceReportController::class, 'daily']);
        $router->get('/reports/monthly', [AttendanceReportController::class, 'monthly']);
        $router->get('/reports/student/{id}', [AttendanceReportController::class, 'student']);
    });
    
    // Fees routes
    $router->prefix('/fees', function($router) {
        $router->get('/', [FeeController::class, 'index']);
        
        // Fee structure
        $router->get('/structure', [FeeStructureController::class, 'index']);
        $router->get('/structure/create', [FeeStructureController::class, 'create']);
        $router->post('/structure', [FeeStructureController::class, 'store']);
        $router->get('/structure/{id}/edit', [FeeStructureController::class, 'edit']);
        $router->post('/structure/{id}', [FeeStructureController::class, 'update']);
        
        // Payments
        $router->get('/payments', [PaymentController::class, 'index']);
        $router->get('/payments/create', [PaymentController::class, 'create']);
        $router->post('/payments', [PaymentController::class, 'store']);
        $router->get('/payments/{id}', [PaymentController::class, 'show']);
        $router->get('/payments/{id}/receipt', [PaymentController::class, 'receipt']);
        
        // Receipts
        $router->get('/receipts', [ReceiptController::class, 'index']);
        $router->get('/receipts/{id}', [ReceiptController::class, 'show']);
        $router->get('/receipts/{id}/download', [ReceiptController::class, 'download']);
        
        // Scholarships
        $router->get('/scholarships', [ScholarshipController::class, 'index']);
        $router->get('/scholarships/create', [ScholarshipController::class, 'create']);
        $router->post('/scholarships', [ScholarshipController::class, 'store']);
        $router->get('/scholarships/{id}/edit', [ScholarshipController::class, 'edit']);
        $router->post('/scholarships/{id}', [ScholarshipController::class, 'update']);
        
        // Dues
        $router->get('/dues', [DueController::class, 'index']);
        $router->get('/dues/send-reminders', [DueController::class, 'sendReminders']);
        
        // Discounts
        $router->get('/discounts', [DiscountController::class, 'index']);
        $router->get('/discounts/create', [DiscountController::class, 'create']);
        $router->post('/discounts', [DiscountController::class, 'store']);
    });
    
    // Library routes
    $router->prefix('/library', function($router) {
        $router->get('/', [LibraryController::class, 'index']);
        
        // Books
        $router->get('/books', [BookController::class, 'index']);
        $router->get('/books/create', [BookController::class, 'create']);
        $router->post('/books', [BookController::class, 'store']);
        $router->get('/books/{id}', [BookController::class, 'show']);
        $router->get('/books/{id}/edit', [BookController::class, 'edit']);
        $router->post('/books/{id}', [BookController::class, 'update']);
        
        // Issue/Return
        $router->get('/issue', [IssueController::class, 'index']);
        $router->get('/issue/create', [IssueController::class, 'create']);
        $router->post('/issue', [IssueController::class, 'store']);
        $router->get('/return', [ReturnController::class, 'index']);
        $router->post('/return/{id}', [ReturnController::class, 'store']);
        
        // Fines
        $router->get('/fines', [FineController::class, 'index']);
        $router->get('/fines/{id}', [FineController::class, 'show']);
        $router->post('/fines/{id}/pay', [FineController::class, 'pay']);
        
        // Categories
        $router->get('/categories', [CategoryController::class, 'index']);
        $router->get('/categories/create', [CategoryController::class, 'create']);
        $router->post('/categories', [CategoryController::class, 'store']);
        
        // Members
        $router->get('/members', [MemberController::class, 'index']);
        $router->get('/members/{id}', [MemberController::class, 'show']);
    });
    
    // Communication routes
    $router->prefix('/communication', function($router) {
        // Messages
        $router->get('/messages', [MessageController::class, 'index']);
        $router->get('/messages/create', [MessageController::class, 'create']);
        $router->post('/messages', [MessageController::class, 'store']);
        $router->get('/messages/{id}', [MessageController::class, 'show']);
        
        // Notifications
        $router->get('/notifications', [NotificationController::class, 'index']);
        $router->get('/notifications/create', [NotificationController::class, 'create']);
        $router->post('/notifications', [NotificationController::class, 'store']);
        $router->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        
        // Announcements
        $router->get('/announcements', [AnnouncementController::class, 'index']);
        $router->get('/announcements/create', [AnnouncementController::class, 'create']);
        $router->post('/announcements', [AnnouncementController::class, 'store']);
        $router->get('/announcements/{id}', [AnnouncementController::class, 'show']);
        $router->get('/announcements/{id}/edit', [AnnouncementController::class, 'edit']);
        $router->post('/announcements/{id}', [AnnouncementController::class, 'update']);
        
        // Events
        $router->get('/events', [EventController::class, 'index']);
        $router->get('/events/create', [EventController::class, 'create']);
        $router->post('/events', [EventController::class, 'store']);
        $router->get('/events/{id}', [EventController::class, 'show']);
        $router->get('/events/{id}/edit', [EventController::class, 'edit']);
        $router->post('/events/{id}', [EventController::class, 'update']);
        
        // Circulars
        $router->get('/circulars', [CircularController::class, 'index']);
        $router->get('/circulars/create', [CircularController::class, 'create']);
        $router->post('/circulars', [CircularController::class, 'store']);
        $router->get('/circulars/{id}', [CircularController::class, 'show']);
        $router->get('/circulars/{id}/download', [CircularController::class, 'download']);
    });
    
    // Reports routes
    $router->prefix('/reports', function($router) {
        $router->get('/', [ReportController::class, 'index']);
        
        // Student reports
        $router->get('/students', [StudentReportController::class, 'index']);
        $router->get('/students/enrollment', [StudentReportController::class, 'enrollment']);
        $router->get('/students/performance', [StudentReportController::class, 'performance']);
        $router->get('/students/attendance', [StudentReportController::class, 'attendance']);
        
        // Faculty reports
        $router->get('/faculty', [FacultyReportController::class, 'index']);
        $router->get('/faculty/performance', [FacultyReportController::class, 'performance']);
        $router->get('/faculty/attendance', [FacultyReportController::class, 'attendance']);
        
        // Finance reports
        $router->get('/finance', [FinanceReportController::class, 'index']);
        $router->get('/finance/income', [FinanceReportController::class, 'income']);
        $router->get('/finance/expenses', [FinanceReportController::class, 'expenses']);
        $router->get('/finance/dues', [FinanceReportController::class, 'dues']);
        
        // Academic reports
        $router->get('/academic', [AcademicReportController::class, 'index']);
        $router->get('/academic/results', [AcademicReportController::class, 'results']);
        $router->get('/academic/courses', [AcademicReportController::class, 'courses']);
        
        // Custom reports
        $router->get('/custom', [CustomReportController::class, 'index']);
        $router->get('/custom/create', [CustomReportController::class, 'create']);
        $router->post('/custom', [CustomReportController::class, 'store']);
        $router->get('/custom/{id}', [CustomReportController::class, 'show']);
    });
});

// API routes
$router->prefix('/api/v1', function($router) {
    // Authentication
    $router->post('/auth/login', [AuthApiController::class, 'login']);
    $router->post('/auth/logout', [AuthApiController::class, 'logout']);
    $router->post('/auth/refresh', [AuthApiController::class, 'refresh']);
    $router->get('/auth/profile', [AuthApiController::class, 'profile']);
    
    // Protected API routes
    $router->group([AuthMiddleware::class], function($router) {
        // Students
        $router->get('/students', [StudentApiController::class, 'index']);
        $router->get('/students/{id}', [StudentApiController::class, 'show']);
        $router->post('/students', [StudentApiController::class, 'store']);
        $router->put('/students/{id}', [StudentApiController::class, 'update']);
        $router->delete('/students/{id}', [StudentApiController::class, 'destroy']);
        
        // Faculty
        $router->get('/faculty', [FacultyApiController::class, 'index']);
        $router->get('/faculty/{id}', [FacultyApiController::class, 'show']);
        
        // Attendance
        $router->get('/attendance', [AttendanceApiController::class, 'index']);
        $router->post('/attendance/mark', [AttendanceApiController::class, 'mark']);
        $router->post('/attendance/bulk', [AttendanceApiController::class, 'bulk']);
        
        // Notifications
        $router->get('/notifications', [NotificationApiController::class, 'index']);
        $router->post('/notifications/send', [NotificationApiController::class, 'send']);
        $router->post('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
    });
});

// Error routes
$router->get('/404', [ErrorController::class, 'notFound']);
$router->get('/403', [ErrorController::class, 'forbidden']);
$router->get('/500', [ErrorController::class, 'serverError']);