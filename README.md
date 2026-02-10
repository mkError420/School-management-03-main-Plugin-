# School Management System - WordPress Plugin

A complete, production-ready WordPress plugin for managing schools with comprehensive features for students, teachers, classes, exams, attendance, and fees.

## Features

### Core Modules
- **Student Management**: Add, edit, and manage student information with automated WordPress user creation
- **Teacher Management**: Manage teacher profiles and assignments
- **Class Management**: Create and manage classes with capacity tracking
- **Subject Management**: Organize subjects and link to classes
- **Enrollment**: Manage student enrollments in classes and subjects
- **Attendance**: Track student attendance with automated calculations
- **Fees Management**: Manage student fees with payment tracking
- **Exam Management**: Schedule exams and manage exam details
- **Results**: Record and display exam results with automatic grading

### Admin Features
- **Dashboard**: Quick overview with statistics and upcoming exams
- **WP Admin Integration**: Clean menu structure with multiple submenus
- **CRUD Operations**: Full Create, Read, Update, Delete functionality
- **AJAX Support**: Asynchronous submission for attendance and enrollment
- **Search Functionality**: Search students, teachers, classes, subjects, and exams

### Frontend Features
- **Student Portal**: Shortcode-based student login and profile view
- **Student Results Portal**: View exam results and academic progress
- **Parent Portal**: Shortcode for parents to monitor their children
- **Exam Results Lookup**: Public results lookup by roll number
- **Class Timetable**: Display class schedule (extensible)

### Security Features
- **Role-Based Access Control**: Admin, Teacher, Student, and Parent roles
- **Nonce Verification**: All forms include CSRF protection
- **Data Sanitization**: All input is sanitized before database storage
- **User Authentication**: Secure student and parent login system
- **WordPress Capability Checks**: Proper authorization on all admin pages

## Installation

1. **Download the Plugin**
   - Place the `school-management-system` folder in `/wp-content/plugins/`

2. **Activate the Plugin**
   - Go to WordPress admin panel → Plugins
   - Find "School Management System" and click "Activate"

3. **Automatic Setup**
   - The plugin automatically creates:
     - Custom database tables
     - WordPress custom roles (Teacher, Student, Parent)
     - Default settings

## Usage

### Admin Dashboard

Navigate to **School Management** in the WordPress admin menu to access all features.

#### Students Management
- **Add Student**: Click "Add New" in the Students section
- **Required Fields**: First Name, Last Name, Email, Roll Number
- **Auto User Creation**: WordPress user account created automatically
- **Edit/Delete**: Manage existing student records

#### Teachers Management
- **Add Teacher**: Add teacher profiles with qualifications
- **Employee ID**: Unique identifier for each teacher
- **Class Assignment**: Link teachers to classes

#### Classes
- **Create Classes**: Add new classes with capacity limits
- **Class Code**: Unique code for identification
- **Teacher Assignment**: Assign class teachers

#### Subjects
- **Subject Management**: Create and organize subjects
- **Teacher Assignment**: Link subjects to teachers
- **Class Association**: Associate subjects with specific classes

#### Enrollments
- **Enroll Students**: Assign students to classes and subjects
- **View Enrollments**: See all student enrollments
- **AJAX Support**: Use AJAX for quick enrollment

#### Attendance
- **Mark Attendance**: Record daily attendance
- **Status Options**: Present, Absent, Late, Excused
- **Reports**: Calculate attendance percentage for students
- **AJAX Submission**: Submit attendance via AJAX

#### Fees
- **Fee Management**: Create and track student fees
- **Payment Status**: Mark fees as paid or pending
- **Fee Types**: Support multiple fee types (Tuition, Transport, etc.)
- **Balance Tracking**: View paid and pending fees

#### Exams
- **Schedule Exams**: Create exam with dates and times
- **Total Marks**: Set total and passing marks
- **Subject Association**: Link exams to subjects
- **Status Tracking**: Track exam status (Scheduled, Completed, etc.)

#### Results
- **Enter Results**: Record student exam results
- **Auto Grading**: Automatic grade calculation
- **Percentage**: Automatic percentage calculation
- **Export**: View results in list format

### Frontend Shortcodes

#### Student Login Portal
```
[sms_student_login]
```
Displays student login form. Redirects to student portal on successful login.

#### Student Portal
```
[sms_student_portal]
```
Shows student information and exam results (requires student login).

#### Parent Portal
```
[sms_parent_portal]
```
Parent dashboard with child's academic information (requires parent login).

#### Class Timetable
```
[sms_class_timetable]
```
Displays class schedule.

#### Exam Results Lookup
```
[sms_exam_results]
```
Public results lookup by roll number.

## Database Structure

### Tables Created
- `wp_sms_students` - Student records
- `wp_sms_teachers` - Teacher records
- `wp_sms_classes` - Class information
- `wp_sms_subjects` - Subject details
- `wp_sms_enrollments` - Student enrollments
- `wp_sms_attendance` - Attendance records
- `wp_sms_fees` - Fee records
- `wp_sms_exams` - Exam information
- `wp_sms_results` - Exam results
- `wp_sms_timetable` - Class timetable

## API Reference

### Student Class
```php
use School_Management_System\Student;

// Add new student
Student::add($student_data);

// Get student
Student::get($student_id);
Student::get_by_roll_number($roll_number);
Student::get_by_user_id($user_id);

// Get all students
Student::get_all($filters, $limit, $offset);

// Update student
Student::update($student_id, $student_data);

// Delete student
Student::delete($student_id);

// Count students
Student::count($filters);

// Search students
Student::search($search_term);
```

### Attendance Class
```php
use School_Management_System\Attendance;

// Mark attendance
Attendance::mark_attendance($student_id, $class_id, $date, $status);

// Get attendance percentage
Attendance::get_attendance_percentage($student_id, $class_id);

// Get student attendance
Attendance::get_student_attendance($student_id, $class_id);
```

### Fee Class
```php
use School_Management_System\Fee;

// Get pending fees
Fee::get_pending_fees($student_id);

// Calculate totals
Fee::get_total_fees($student_id);
Fee::get_paid_fees($student_id);

// Mark fee as paid
Fee::mark_paid($fee_id, $payment_date);
```

### Result Class
```php
use School_Management_System\Result;

// Get student results
Result::get_student_results($student_id);

// Get exam results
Result::get_exam_results($exam_id);

// Get exam average
Result::get_exam_average($exam_id);
```

## AJAX Endpoints

### Submit Attendance
```javascript
$.ajax({
    url: smsFrontend.ajaxurl,
    type: 'POST',
    data: {
        action: 'sms_submit_attendance',
        nonce: smsFrontend.nonce,
        student_id: 1,
        class_id: 1,
        attendance_date: '2024-01-25',
        status: 'present'
    }
});
```

### Enroll Student
```javascript
$.ajax({
    url: smsFrontend.ajaxurl,
    type: 'POST',
    data: {
        action: 'sms_enroll_student',
        nonce: smsFrontend.nonce,
        student_id: 1,
        class_id: 1,
        subject_id: 1
    }
});
```

### Search Data
```javascript
$.ajax({
    url: smsFrontend.ajaxurl,
    type: 'POST',
    data: {
        action: 'sms_search_data',
        nonce: smsFrontend.nonce,
        search_term: 'John',
        type: 'students'
    }
});
```

## Grading System

Grades are automatically calculated based on percentage:
- **F**: Below passing marks
- **D**: 40-49%
- **C**: 50-59%
- **B**: 60-69%
- **A**: 70-79%
- **A+**: 80% and above

## Settings

Access plugin settings from **School Management → Settings** to configure:
- School Name
- School Email
- School Phone
- Passing Marks
- Academic Year
- Currency

## File Structure

```
school-management-system/
├── school-management-system.php          # Main plugin file
├── includes/
│   ├── class-activator.php              # Plugin activation
│   ├── class-deactivator.php            # Plugin deactivation
│   ├── class-plugin.php                 # Main plugin class
│   ├── class-admin.php                  # Admin menu handler
│   ├── class-database.php               # Database operations
│   ├── class-student.php                # Student CRUD
│   ├── class-teacher.php                # Teacher CRUD
│   ├── class-class.php                  # Class CRUD
│   ├── class-subject.php                # Subject CRUD
│   ├── class-enrollment.php             # Enrollment CRUD
│   ├── class-attendance.php             # Attendance CRUD
│   ├── class-fee.php                    # Fee CRUD
│   ├── class-exam.php                   # Exam CRUD
│   ├── class-result.php                 # Result CRUD
│   ├── class-assets-loader.php          # CSS/JS loading
│   ├── class-auth.php                   # Authentication
│   └── class-shortcodes.php             # Frontend shortcodes
├── admin/
│   ├── templates/                       # Admin page templates
│   │   ├── students.php
│   │   ├── teachers.php
│   │   ├── classes.php
│   │   ├── subjects.php
│   │   ├── enrollments.php
│   │   ├── attendance.php
│   │   ├── fees.php
│   │   ├── exams.php
│   │   └── results.php
│   └── pages/                           # Form templates
│       └── student-form.php
├── assets/
│   └── ajax-handlers.php                # AJAX endpoint handlers
├── public/
│   ├── css/
│   │   ├── admin-style.css
│   │   └── style.css
│   ├── js/
│   │   ├── admin-script.js
│   │   └── script.js
│   └── templates/                       # Frontend templates
└── README.md                            # This file
```

## Security Considerations

1. **Nonce Verification**: All forms use WordPress nonce for CSRF protection
2. **Sanitization**: All inputs are sanitized using WordPress functions
3. **Capability Checks**: Admin functions check user capabilities
4. **Role-Based Access**: Custom roles control feature access
5. **HTTPS**: Ensure your site uses HTTPS in production
6. **Regular Updates**: Keep WordPress and plugins updated

## Performance Tips

1. **Database Indexing**: The plugin creates indexed columns for faster queries
2. **Pagination**: Use pagination when displaying large lists
3. **Caching**: Use WordPress transients for frequently accessed data
4. **AJAX**: Use AJAX for form submissions to avoid page reloads

## Troubleshooting

### Tables Not Created
- Check WordPress database user has CREATE TABLE permission
- Verify database connection settings
- Check PHP error logs for MySQL errors

### Authentication Issues
- Ensure user roles are created (check WordPress Users)
- Clear browser cookies and try logging in again
- Verify email addresses are unique

### Missing Features
- Clear browser cache (Ctrl+Shift+Delete)
- Deactivate and reactivate the plugin
- Check plugin is properly activated

## Extending the Plugin

### Adding New CRUD Module
1. Create a new class in `includes/class-modulename.php`
2. Extend with add(), get(), update(), delete() methods
3. Create database table in Activator class
4. Add admin template in `admin/templates/`
5. Add menu item in Admin class

### Adding Custom Shortcodes
```php
add_shortcode('sms_custom', function() {
    return 'Custom content';
});
```

## Support & License

This plugin is provided as-is for educational and production use. For support or customization, please refer to the code documentation and WordPress best practices.

## Changelog

### Version 1.0.0
- Initial release
- Complete CRUD operations for all modules
- Admin dashboard and menu
- Frontend portals with shortcodes
- AJAX support for key operations
- Role-based access control
- Comprehensive database structure

---

**Developed with WordPress best practices and coding standards.**
