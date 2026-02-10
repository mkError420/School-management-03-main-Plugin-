# School Management System - Quick Start Guide

## Installation

1. Download the plugin folder to `/wp-content/plugins/school-management-system/`
2. Go to WordPress Admin → Plugins
3. Find "School Management System" and click **Activate**
4. The plugin will automatically create all database tables

## First Steps

### Step 1: Access the Plugin
- Go to WordPress Admin
- Look for **School Management** in the left menu
- You'll see the dashboard with statistics

### Step 2: Add Your First Student
1. Click **School Management → Students**
2. Click **Add New**
3. Fill in:
   - **First Name**: John
   - **Last Name**: Doe
   - **Email**: john.doe@example.com
   - **Roll Number**: STU001
4. Click **Add Student**
5. A WordPress user account is created automatically

### Step 3: Add a Teacher
1. Click **School Management → Teachers**
2. Click **Add New**
3. Fill in teacher details
4. Click **Add Teacher**

### Step 4: Create a Class
1. Click **School Management → Classes**
2. Click **Add New**
3. Enter:
   - **Class Name**: Grade 10 - A
   - **Class Code**: G10A
   - **Capacity**: 50
4. Click **Add Class**

### Step 5: Add Subjects
1. Click **School Management → Subjects**
2. Click **Add New**
3. Enter subject details
4. Click **Add Subject**

### Step 6: Enroll Student
1. Click **School Management → Enrollments**
2. Select a student and class
3. Click **Enroll**

### Step 7: Schedule an Exam
1. Click **School Management → Exams**
2. Click **Add New**
3. Fill in:
   - **Exam Name**: First Term - Mathematics
   - **Exam Code**: EXAM001
   - **Class**: Select class
   - **Exam Date**: 2024-02-15
   - **Total Marks**: 100
   - **Passing Marks**: 40
4. Click **Add Exam**

### Step 8: Record Results
1. Click **School Management → Results**
2. Click **Add New**
3. Select exam and student
4. Enter obtained marks (e.g., 85)
5. Click **Add Result**
6. Percentage and grade calculated automatically

### Step 9: Create Student Portal Page
1. Go to WordPress Pages → Add New
2. Enter page title: "Student Portal"
3. Add content: `[sms_student_portal]`
4. Publish
5. Students can login here with their email

### Step 10: Create Results Lookup Page
1. Go to Pages → Add New
2. Title: "Results"
3. Add content: `[sms_exam_results]`
4. Publish
5. Public can search results by roll number

## Available Shortcodes

```
[sms_student_login]       - Student login form
[sms_student_portal]      - Student dashboard (requires login)
[sms_parent_portal]       - Parent dashboard (requires parent login)
[sms_class_timetable]     - Class schedule
[sms_exam_results]        - Public results lookup
```

## Admin Features

### Dashboard
- See total students, teachers, classes, exams
- View upcoming exams
- Quick statistics

### Students
- Add/Edit/Delete students
- View all students
- Search students
- Auto-generate WordPress accounts

### Teachers
- Manage teacher profiles
- Track qualifications
- Assign to classes

### Classes
- Create classes with capacity
- Assign teachers
- View students in class

### Subjects
- Add subjects
- Assign to teachers and classes
- Manage credit hours

### Enrollments
- Enroll students in classes
- Track enrollment dates
- Manage enrollment status

### Attendance
- Mark daily attendance
- View attendance records
- Calculate attendance percentage
- AJAX quick submission

### Fees
- Create fee records
- Track payment status
- Mark fees as paid
- View balance for student

### Exams
- Schedule exams
- Set total and passing marks
- Track exam status
- View upcoming exams

### Results
- Record student results
- Auto-calculate percentage and grade
- View exam-wise results
- View student-wise results

## Security

All forms are protected with:
- **Nonce tokens** for CSRF protection
- **Input sanitization** for all fields
- **User capability checks** for authorization
- **Role-based access** (Admin/Teacher/Student/Parent)

## Troubleshooting

### Tables Not Created
- Check if database user has CREATE TABLE permission
- Look at WordPress error logs
- Try deactivating and reactivating plugin

### Student Login Not Working
- Verify student email is correct
- Check if WordPress user was created
- Clear browser cookies

### Admin Menu Missing
- Make sure you're logged in as Administrator
- Clear WordPress transients
- Check if plugin is activated

### AJAX Not Working
- Check browser console for JavaScript errors
- Verify nonce is being sent correctly
- Check WordPress error logs

## Tips

1. **Bulk Data**: Consider adding students in bulk later
2. **Backup**: Always backup your database before major changes
3. **Testing**: Test the plugin on development site first
4. **Roles**: Set up teacher accounts for better data management
5. **Reports**: Plan to add custom reports for better insights
6. **Mobile**: Frontend is mobile-responsive

## Next Actions

After setup:
1. [ ] Create student accounts
2. [ ] Add teachers
3. [ ] Create classes and subjects
4. [ ] Enroll students
5. [ ] Schedule exams
6. [ ] Start recording attendance
7. [ ] Add fee structures
8. [ ] Create public results page
9. [ ] Train staff on usage
10. [ ] Plan for integration with other systems

## Support Resources

- **README.md**: Full documentation
- **COMPLETE.md**: Detailed feature list
- **Code Comments**: Each class has extensive documentation
- **API Examples**: Available in README.md

## Contact & Support

For issues, customization, or extension needs, refer to:
1. Plugin code documentation
2. WordPress codex and best practices
3. Theme and plugin documentation

---

**Happy School Managing!**
