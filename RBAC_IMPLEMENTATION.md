# Role-Based Access Control (RBAC) Implementation Summary

## Overview
This document summarizes the comprehensive RBAC system implemented for the quiz management platform in Symfony 6.4, supporting three distinct user roles: **Admin**, **Instructor**, and **Student**.

---

## 1. Database Schema Changes

### New Field: `instructor` in Quiz Entity
- **Type**: ManyToOne relationship to User entity
- **Mapping**: `#[ORM\ManyToOne(targetEntity: User::class)]`
- **Cascade**: onDelete='SET NULL' (if instructor is deleted, quiz remains but ownership is cleared)
- **Nullable**: true (allows system-created quizzes without owner)

### Migration Required
Run the following command to apply the database schema change:
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate --no-interaction
```

This creates the `instructor_id` foreign key column in the `quiz` table.

---

## 2. Entity Relationship Setup

### Quiz Entity (`src/Entity/Quiz.php`)
```php
#[ORM\ManyToOne(targetEntity: User::class)]
#[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
private ?User $instructor = null;

public function getInstructor(): ?User
{
    return $this->instructor;
}

public function setInstructor(?User $instructor): static
{
    $this->instructor = $instructor;
    return $this;
}
```

---

## 3. Role-Based Permissions Matrix

| Action | Admin | Instructor (Owner) | Instructor (Non-Owner) | Student |
|--------|-------|-----|-------|---------|
| **View all quizzes** | ✅ | ❌ (only own) | ❌ | ❌ (only APPROVED) |
| **Create quiz** | ✅ | ✅ | ✅ | ❌ |
| **Edit own quiz** | ✅ | ✅ | ❌ | ❌ |
| **Edit others' quiz** | ✅ | ❌ | ❌ | ❌ |
| **Delete own quiz** | ✅ | ✅ | ❌ | ❌ |
| **Delete others' quiz** | ✅ | ❌ | ❌ | ❌ |
| **Submit quiz (→PENDING)** | N/A | ✅ (auto on edit) | ❌ | ❌ |
| **Approve quiz (PENDING→APPROVED)** | ✅ | ❌ | ❌ | ❌ |
| **Refuse quiz (PENDING→REFUSED)** | ✅ | ❌ | ❌ | ❌ |
| **View REFUSED reason** | ✅ | ✅ (own) | ❌ | ❌ |
| **Take quiz** | ❌ | ❌ | ❌ | ✅ (if APPROVED) |

---

## 4. Workflow States and Transitions

### Quiz Status Lifecycle

```
[Draft/null] 
    ↓ (Instructor creates or edits) → [PENDING]
    ↓
[PENDING]
    ├─→ (Admin approves) → [APPROVED] ✓ (visible to students)
    └─→ (Admin refuses) → [REFUSED] ✗ (hidden, reason shown to owner)
```

### Status Descriptions
- **null/Draft**: Local work-in-progress, visible only to owner
- **PENDING**: Awaiting admin review, hidden from students, owner can still edit
- **APPROVED**: Visible to all students, owner can no longer edit (must reject and recreate to modify)
- **REFUSED**: Rejected by admin with reason shown to owner, owner can edit and resubmit

---

## 5. Controller Implementation Details

### InstructorQuizController (`src/Controller/Instructor/InstructorQuizController.php`)

#### Method: `list()`
- **Filter**: Shows only quizzes owned by the current instructor
- **Query**: `findBy(['instructor' => $this->getUser()], ['createdAt' => 'DESC'])`
- **Statistics**: Calculated only for instructor's own quizzes
- **Search**: Preserves instructor filter in search queries

#### Method: `new()`
- **Auto-assignment**: Sets `$quiz->setInstructor($this->getUser())`
- **Status**: Automatically set to `PENDING`
- **Submitted Time**: Set to current datetime

#### Method: `edit()`
- **Ownership Check**: Verifies `$quiz->getInstructor() === $this->getUser()`
- **Access Denied**: Redirects to list with error message if not owner
- **Status Reset**: Sets status to `PENDING` on any edit (requires re-approval)

#### Method: `delete()`
- **Ownership Check**: Same as edit method
- **CSRF Protection**: Validates token before deletion

#### Method: `addQuestion()`
- **Ownership Check**: Verifies quiz owner before allowing question addition
- **Redirect on Failure**: Redirects to list with error message

#### Method: `editQuestion()`
- **Ownership Check**: Gets quiz from question and validates ownership
- **Prevents Cross-Ownership**: Blocks question edits on quizzes owned by others

#### Method: `deleteQuestion()`
- **Ownership Check**: Same as editQuestion
- **Maintains Referential Integrity**: Redirects to correct quiz edit page

#### Method: `show()`
- **Ownership Check**: Validates access before rendering details
- **Redirect Pattern**: Consistent with other methods

### StudentQuizController (`src/Controller/Student/StudentQuizController.php`)

#### Method: `list()` - Modified for Status Filter
```php
$queryBuilder->where('q.status = :status')
    ->setParameter('status', 'APPROVED');
```
- **Visibility**: Students see only APPROVED quizzes
- **Exclusion**: PENDING, REFUSED, and null-status quizzes are hidden
- **Pagination**: 5 items per page with searchable title/description

### AdminQuizController (`src/Controller/Admin/AdminQuizController.php`)

#### Features:
- **All Access**: Admin can view, edit, delete ALL quizzes regardless of owner
- **Approval Workflow**: 
  - `approve()` route: Marks quiz as APPROVED
  - `refuse()` route: Marks quiz as REFUSED with reason stored
- **Dashboard**: Shows all quizzes with status badges and statistics
- **Buttons Conditional**: Approve/Refuse buttons show only for PENDING quizzes

---

## 6. Template Updates

### instructor/quiz/list.html.twig
- Shows only quizzes owned by the instructor
- Statistics reflect only instructor's quizzes
- Edit/Delete buttons visible only for listed (owned) quizzes

### instructor/quiz/edit.html.twig
- Conditional "Submit for Approval" button (shown if draft/editable)
- Status badges display current quiz status
- Edit/Delete buttons enabled for owned quizzes

### admin/quiz/list.html.twig
- Shows ALL quizzes with status badges
- Conditional approve/refuse buttons (only for PENDING)
- Rejection reasons displayed in alert boxes for REFUSED quizzes

### admin/quiz/show.html.twig
- Detailed view with all quiz information
- Approve/Refuse buttons with modal dialog
- Status alerts and warnings

### student/quiz/liste.html.twig
- Shows only APPROVED quizzes
- Search and pagination features
- Read-only quiz cards (no edit/delete options)

---

## 7. Security Best Practices Implemented

### ✅ Ownership Verification
- Every instructor action checks `$quiz->getInstructor() === $this->getUser()`
- Prevents unauthorized access via direct URL manipulation

### ✅ Status-Based Visibility
- Query-level filtering ensures students cannot see unapproved quizzes
- Admin sees all, instructor sees own, student sees approved

### ✅ CSRF Protection
- Delete operations require valid CSRF token
- Form submissions protected by Symfony form handling

### ✅ Referential Integrity
- Soft delete approach: `instructor_id` set to NULL if user deleted
- Questions/Responses cascade delete with quiz

### ✅ Error Messages
- Consistent "Vous n'avez pas accès à ce quiz" for access denials
- Flash messages inform users of permission issues
- Redirects to safe pages (quiz list) instead of showing blank pages

---

## 8. Testing Checklist

### Admin Tests
- [ ] Can view all quizzes (owned and others)
- [ ] Can edit any quiz
- [ ] Can delete any quiz
- [ ] Can approve PENDING quizzes
- [ ] Can refuse PENDING quizzes
- [ ] Can see rejection reasons in list and detail views
- [ ] Statistics show all quizzes in system

### Instructor Tests
- [ ] Can create new quiz (auto-set as PENDING, auto-assigned owner)
- [ ] Can see only own quizzes in list
- [ ] Can edit own quizzes (status reverts to PENDING)
- [ ] Can delete own quizzes
- [ ] Can add/edit/delete questions on own quizzes
- [ ] Cannot access other instructors' quizzes (no edit/delete)
- [ ] Cannot see other instructors' quizzes in list (except if APPROVED as read-only)
- [ ] Can view PENDING quizzes waiting for approval
- [ ] Can see rejection reason when quiz is REFUSED
- [ ] Search filters only own quizzes

### Student Tests
- [ ] Can see only APPROVED quizzes
- [ ] Cannot see PENDING or REFUSED quizzes
- [ ] Can take APPROVED quizzes
- [ ] Cannot edit or delete any quizzes
- [ ] Search returns only APPROVED quizzes
- [ ] Pagination works correctly (5 per page)

### Edge Cases
- [ ] Accessing deleted quiz returns 404
- [ ] Setting instructor_id to NULL doesn't break quiz
- [ ] Switching roles updates visibility correctly
- [ ] Multiple instructors don't see each other's drafts
- [ ] APPROVED quiz from other instructor shown as read-only

---

## 9. Code Locations Reference

| File | Purpose | Changes Made |
|------|---------|--------------|
| `src/Entity/Quiz.php` | Quiz domain model | Added instructor ManyToOne relationship |
| `src/Controller/Instructor/InstructorQuizController.php` | Instructor CRUD | Added ownership checks to all 7 methods |
| `src/Controller/Admin/QuizAdminController.php` | Admin management | Approve/Refuse routes (pre-existing, no changes) |
| `src/Controller/Student/QuizController.php` | Student access | Status filter to show APPROVED only |
| `templates/instructor/quiz/list.html.twig` | Instructor dashboard | Shows only owned quizzes |
| `templates/admin/quiz/list.html.twig` | Admin dashboard | Shows all with status badges |
| `templates/student/quiz/liste.html.twig` | Student discovery | Shows only APPROVED |

---

## 10. Known Limitations and Future Enhancements

### Current Limitations
1. **No Shared Ownership**: Each quiz has exactly one instructor owner (no co-ownership)
2. **No Delegation**: Instructors cannot delegate quiz management to others
3. **No Audit Log**: No historical record of approvals/rejections
4. **No Email Notifications**: Instructors aren't notified when quiz is approved/refused

### Potential Enhancements
- [ ] Add audit logger for approval/rejection actions
- [ ] Send email notifications to instructor when status changes
- [ ] Implement quiz sharing with read-only access for other instructors
- [ ] Add bulk approval actions for admins
- [ ] Implement quiz versioning (new version after refusal)
- [ ] Add instructor comments/feedback on REFUSED quizzes
- [ ] Implement role-based permissions using Symfony Security Roles

---

## 11. Deployment Checklist

Before deploying to production:

1. **Database Migration**
   ```bash
   php bin/console make:migration
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

2. **Clear Cache**
   ```bash
   php bin/console cache:clear
   php bin/console cache:warmup
   ```

3. **Verify Relationships**
   - Test instructor-owned quiz filtering
   - Confirm null instructor handling
   - Validate foreign key constraints

4. **Test All Roles**
   - Create test users with each role
   - Verify access controls work as intended
   - Check edge cases (deleted users, orphaned quizzes)

5. **Monitor Logs**
   - Watch for any 403/404 errors in access attempts
   - Check for database constraint violations
   - Verify no orphaned quiz records

---

## 12. Summary of Changes

### Phase 1: Entity Relationships ✅
- Added `instructor` field to Quiz entity
- Created ManyToOne relationship to User
- Added getter/setter methods

### Phase 2: Ownership Enforcement ✅
- Added ownership checks to all 7 InstructorQuizController methods
- Implemented access denial with flash messages
- Ensured consistent redirect behavior

### Phase 3: Query Filtering ✅
- Updated instructor list to show only owned quizzes
- Student list already filters by status=APPROVED
- Admin list shows all quizzes

### Phase 4: Database Migration ⏳ (Manual)
- User must run `php bin/console make:migration && php bin/console doctrine:migrations:migrate`

### Phase 5: Testing & Deployment ⏳
- Run comprehensive test suite
- Verify all role-based access controls
- Deploy and monitor

---

## Contact & Documentation

For questions about this RBAC implementation, refer to:
- Symfony Security documentation: https://symfony.com/doc/current/security.html
- Doctrine ORM relationships: https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html
- This project's ROUTES_DOCUMENTATION.md for endpoint reference
