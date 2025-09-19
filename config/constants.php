<?php
/**
 * System Constants
 * 
 * Application-wide constants and enumerations
 */

// User Roles
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_PRINCIPAL', 'principal');
define('ROLE_VICE_PRINCIPAL', 'vice_principal');
define('ROLE_HOD', 'hod');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STAFF', 'staff');
define('ROLE_STUDENT', 'student');
define('ROLE_PARENT', 'parent');
define('ROLE_LIBRARIAN', 'librarian');
define('ROLE_ACCOUNTANT', 'accountant');
define('ROLE_RECEPTIONIST', 'receptionist');

// User Status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED', 'suspended');
define('STATUS_GRADUATED', 'graduated');
define('STATUS_TRANSFERRED', 'transferred');
define('STATUS_DROPPED', 'dropped');

// Academic Constants
define('SEMESTER_ODD', 'odd');
define('SEMESTER_EVEN', 'even');

// Attendance Status
define('ATTENDANCE_PRESENT', 'present');
define('ATTENDANCE_ABSENT', 'absent');
define('ATTENDANCE_LATE', 'late');
define('ATTENDANCE_HALF_DAY', 'half_day');
define('ATTENDANCE_MEDICAL_LEAVE', 'medical_leave');
define('ATTENDANCE_AUTHORIZED_LEAVE', 'authorized_leave');

// Exam Types
define('EXAM_INTERNAL', 'internal');
define('EXAM_EXTERNAL', 'external');
define('EXAM_PRACTICAL', 'practical');
define('EXAM_VIVA', 'viva');
define('EXAM_PROJECT', 'project');
define('EXAM_ASSIGNMENT', 'assignment');

// Grade System
define('GRADE_A_PLUS', 'A+');
define('GRADE_A', 'A');
define('GRADE_B_PLUS', 'B+');
define('GRADE_B', 'B');
define('GRADE_C_PLUS', 'C+');
define('GRADE_C', 'C');
define('GRADE_D', 'D');
define('GRADE_F', 'F');

// Fee Status
define('FEE_PAID', 'paid');
define('FEE_PENDING', 'pending');
define('FEE_OVERDUE', 'overdue');
define('FEE_PARTIAL', 'partial');
define('FEE_WAIVED', 'waived');

// Payment Status
define('PAYMENT_SUCCESS', 'success');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_CANCELLED', 'cancelled');
define('PAYMENT_REFUNDED', 'refunded');

// Library Constants
define('BOOK_AVAILABLE', 'available');
define('BOOK_ISSUED', 'issued');
define('BOOK_RESERVED', 'reserved');
define('BOOK_LOST', 'lost');
define('BOOK_DAMAGED', 'damaged');

// Hostel Constants
define('ROOM_VACANT', 'vacant');
define('ROOM_OCCUPIED', 'occupied');
define('ROOM_MAINTENANCE', 'maintenance');
define('ROOM_RESERVED', 'reserved');

// Transport Constants
define('ROUTE_ACTIVE', 'active');
define('ROUTE_INACTIVE', 'inactive');
define('ROUTE_MAINTENANCE', 'maintenance');

// Leave Types
define('LEAVE_SICK', 'sick');
define('LEAVE_CASUAL', 'casual');
define('LEAVE_EARNED', 'earned');
define('LEAVE_MATERNITY', 'maternity');
define('LEAVE_PATERNITY', 'paternity');
define('LEAVE_EMERGENCY', 'emergency');

// Leave Status
define('LEAVE_PENDING', 'pending');
define('LEAVE_APPROVED', 'approved');
define('LEAVE_REJECTED', 'rejected');
define('LEAVE_CANCELLED', 'cancelled');

// Communication Types
define('COMM_EMAIL', 'email');
define('COMM_SMS', 'sms');
define('COMM_NOTIFICATION', 'notification');
define('COMM_ANNOUNCEMENT', 'announcement');
define('COMM_CIRCULAR', 'circular');

// File Types
define('FILE_IMAGE', 'image');
define('FILE_DOCUMENT', 'document');
define('FILE_VIDEO', 'video');
define('FILE_AUDIO', 'audio');

// Priority Levels
define('PRIORITY_LOW', 'low');
define('PRIORITY_MEDIUM', 'medium');
define('PRIORITY_HIGH', 'high');
define('PRIORITY_URGENT', 'urgent');

// Gender
define('GENDER_MALE', 'male');
define('GENDER_FEMALE', 'female');
define('GENDER_OTHER', 'other');

// Blood Groups
define('BLOOD_A_POSITIVE', 'A+');
define('BLOOD_A_NEGATIVE', 'A-');
define('BLOOD_B_POSITIVE', 'B+');
define('BLOOD_B_NEGATIVE', 'B-');
define('BLOOD_AB_POSITIVE', 'AB+');
define('BLOOD_AB_NEGATIVE', 'AB-');
define('BLOOD_O_POSITIVE', 'O+');
define('BLOOD_O_NEGATIVE', 'O-');

// Marital Status
define('MARITAL_SINGLE', 'single');
define('MARITAL_MARRIED', 'married');
define('MARITAL_DIVORCED', 'divorced');
define('MARITAL_WIDOWED', 'widowed');

// Days of Week
define('DAY_MONDAY', 'monday');
define('DAY_TUESDAY', 'tuesday');
define('DAY_WEDNESDAY', 'wednesday');
define('DAY_THURSDAY', 'thursday');
define('DAY_FRIDAY', 'friday');
define('DAY_SATURDAY', 'saturday');
define('DAY_SUNDAY', 'sunday');

// Time Slots
define('SLOT_MORNING', 'morning');
define('SLOT_AFTERNOON', 'afternoon');
define('SLOT_EVENING', 'evening');

// Application Settings
define('ITEMS_PER_PAGE', 25);
define('MAX_UPLOAD_SIZE', 10485760); // 10MB
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('OTP_EXPIRY_MINUTES', 10);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

return [
    'roles' => [
        ROLE_SUPER_ADMIN => 'Super Administrator',
        ROLE_ADMIN => 'Administrator',
        ROLE_PRINCIPAL => 'Principal',
        ROLE_VICE_PRINCIPAL => 'Vice Principal',
        ROLE_HOD => 'Head of Department',
        ROLE_TEACHER => 'Teacher',
        ROLE_STAFF => 'Staff',
        ROLE_STUDENT => 'Student',
        ROLE_PARENT => 'Parent',
        ROLE_LIBRARIAN => 'Librarian',
        ROLE_ACCOUNTANT => 'Accountant',
        ROLE_RECEPTIONIST => 'Receptionist',
    ],
    
    'status' => [
        STATUS_ACTIVE => 'Active',
        STATUS_INACTIVE => 'Inactive',
        STATUS_SUSPENDED => 'Suspended',
        STATUS_GRADUATED => 'Graduated',
        STATUS_TRANSFERRED => 'Transferred',
        STATUS_DROPPED => 'Dropped',
    ],
    
    'attendance' => [
        ATTENDANCE_PRESENT => 'Present',
        ATTENDANCE_ABSENT => 'Absent',
        ATTENDANCE_LATE => 'Late',
        ATTENDANCE_HALF_DAY => 'Half Day',
        ATTENDANCE_MEDICAL_LEAVE => 'Medical Leave',
        ATTENDANCE_AUTHORIZED_LEAVE => 'Authorized Leave',
    ],
    
    'grades' => [
        GRADE_A_PLUS => ['min' => 90, 'max' => 100, 'points' => 10],
        GRADE_A => ['min' => 80, 'max' => 89, 'points' => 9],
        GRADE_B_PLUS => ['min' => 70, 'max' => 79, 'points' => 8],
        GRADE_B => ['min' => 60, 'max' => 69, 'points' => 7],
        GRADE_C_PLUS => ['min' => 50, 'max' => 59, 'points' => 6],
        GRADE_C => ['min' => 40, 'max' => 49, 'points' => 5],
        GRADE_D => ['min' => 33, 'max' => 39, 'points' => 4],
        GRADE_F => ['min' => 0, 'max' => 32, 'points' => 0],
    ],
    
    'blood_groups' => [
        BLOOD_A_POSITIVE, BLOOD_A_NEGATIVE,
        BLOOD_B_POSITIVE, BLOOD_B_NEGATIVE,
        BLOOD_AB_POSITIVE, BLOOD_AB_NEGATIVE,
        BLOOD_O_POSITIVE, BLOOD_O_NEGATIVE,
    ],
    
    'days' => [
        DAY_MONDAY => 'Monday',
        DAY_TUESDAY => 'Tuesday',
        DAY_WEDNESDAY => 'Wednesday',
        DAY_THURSDAY => 'Thursday',
        DAY_FRIDAY => 'Friday',
        DAY_SATURDAY => 'Saturday',
        DAY_SUNDAY => 'Sunday',
    ],
];