# RBAC Implementation - Quick Start Checklist

## ✅ Completed Code Changes
- [x] Added `instructor` relationship to Quiz entity
- [x] Added ownership checks to InstructorQuizController (7 methods)
- [x] Updated instructor list() to filter only owned quizzes
- [x] Verified PHP syntax on all modified files
- [x] Created RBAC documentation file

## 🎯 Your Next Steps (DO THESE NOW)

### Step 1️⃣: Run Database Migration
```powershell
cd c:\Users\YOSRA\Desktop\projet\ProjetSymfony3A
php bin/console make:migration
php bin/console doctrine:migrations:migrate --no-interaction
```
**Expected output**: "Successfully executed 1 migration" or similar
**Time**: ~1 minute
**Critical**: ⚠️ MUST DO FIRST - Without this, the instructor_id column won't exist in database

### Step 2️⃣: Clear Application Cache
```powershell
php bin/console cache:clear
```
**Expected output**: "/var/cache/dev has been removed" or similar
**Time**: ~30 seconds
**Why**: PHP needs to reload entity mappings

### Step 3️⃣: Test Admin Role
Open your app and login as admin user:
- [ ] Navigate to Admin → Quizzes
- [ ] Verify: Can see ALL quizzes (including PENDING from other instructors)
- [ ] Verify: Can click "Approve" button on PENDING quizzes
- [ ] Verify: Can click "Refuse" button on PENDING quizzes
- [ ] Verify: Can edit any quiz
- [ ] Verify: Can delete any quiz

### Step 4️⃣: Test Instructor Role (Owner)
Login as Instructor #1 who created some quizzes:
- [ ] Navigate to Instructor → My Quizzes
- [ ] Verify: Only see quizzes you created (ownership filter working)
- [ ] Create new quiz (title: "Test Quiz")
- [ ] Verify: New quiz marked as PENDING in list
- [ ] Verify: Edit quiz → status shows PENDING
- [ ] Click Edit → status should still be PENDING
- [ ] Click Delete button
- [ ] Verify: Quiz removed from list

### Step 5️⃣: Test Instructor Role (Non-Owner)
Login as Instructor #2 (different instructor):
- [ ] Navigate to Instructor → My Quizzes
- [ ] Verify: Don't see Instructor #1's quizzes
- [ ] Create your own new quiz (title: "My Test Quiz")
- [ ] Verify: Only your quiz appears in list
- [ ] **Security Test**: Try accessing Instructor #1's quiz directly
  - Copy quiz edit URL: `/instructor/quizzes/1/edit` (or whatever ID)
  - Paste in address bar
  - Verify: Get error message "Vous n'avez pas accès à ce quiz"
  - Verify: Redirected back to your quiz list

### Step 6️⃣: Test Student Role
Login as any student user:
- [ ] Navigate to Student Quizzes
- [ ] Verify: Only see APPROVED quizzes (not PENDING or REFUSED)
- [ ] Try search for quiz (should work on APPROVED only)
- [ ] Try clicking Edit or Delete buttons (should NOT exist)
- [ ] **Security Test**: Try accessing PENDING quiz directly
  - Copy quiz view URL if visible
  - Verify: Quiz filtered out, cannot access

### Step 7️⃣: Test Approval Workflow
1. As Instructor, create new quiz with at least 1 question
2. Status should be PENDING
3. Logout, login as Admin
4. Navigate to Admin → Quizzes
5. Find the instructor's quiz (marked PENDING)
6. Click "Approve" button
7. Verify: Status changes to APPROVED in admin list
8. Logout, login as Student
9. Verify: Now you can see the quiz in student list

## 🔍 Verification Checklist

After completing all 7 tests above, verify:
- [ ] No error messages in browser console
- [ ] No database connection errors in logs
- [ ] All CSRF tokens working (no 403 errors)
- [ ] Page load times are acceptable
- [ ] Bootstrap styling is intact
- [ ] All flash messages are displaying correctly

## ⚠️ If Something Goes Wrong

### Error: "No instructor_id column" or similar database error
- Run: `php bin/console doctrine:migrations:list`
- Verify the migration appears and is marked as executed
- If not executed, run: `php bin/console doctrine:migrations:migrate --no-interaction`

### Error: "SQLSTATE[HY000]" during migration
- Possible duplicate migration
- Run: `php bin/console make:migration --check`
- If conflicts, resolve them before running migrate

### Error: Instructor still sees all quizzes (filter not working)
1. Verify you ran: `php bin/console cache:clear`
2. Check `src/Controller/Instructor/InstructorQuizController.php` line ~35
3. Look for: `->andWhere('q.instructor = :instructor')`
4. If missing, reapply file from repo

### Error: Student can still see PENDING quizzes
1. Check `src/Controller/Student/QuizController.php`
2. Look for: `->where('q.status = :status')` with `'APPROVED'` parameter
3. If missing, reapply file

### Error: "Doctrine mapping error"
- Solution: `php bin/console cache:clear && php bin/console cache:warmup`

## 📊 Quick Test Data Setup

To test with sample data before running production tests:

```sql
-- Assuming you have these user IDs from your database
UPDATE quiz SET instructor_id = 2 WHERE id IN (1, 3, 5); -- Instructor 2 owns these
UPDATE quiz SET instructor_id = 3 WHERE id IN (2, 4, 6); -- Instructor 3 owns these

-- Set some quizzes to APPROVED for student testing
UPDATE quiz SET status = 'APPROVED' WHERE id IN (1, 2);

-- Set some to PENDING for admin testing
UPDATE quiz SET status = 'PENDING' WHERE id IN (3, 4);
```

## 📋 File Changes Summary

**Modified Files:**
1. `src/Entity/Quiz.php` - Added instructor field
2. `src/Controller/Instructor/InstructorQuizController.php` - Added ownership checks + list filter

**Created Files:**
1. `RBAC_IMPLEMENTATION.md` - Full technical documentation
2. `RBAC_STATUS_REPORT.md` - Completion status and next steps
3. `RBAC_QUICK_START.md` - This file!

## 🎉 Success Criteria

You'll know the RBAC system is working correctly when:
- ✅ Admin can see/modify all quizzes
- ✅ Instructors only see their own quizzes
- ✅ Instructors cannot access others' quizzes (error message shown)
- ✅ Students see only APPROVED quizzes
- ✅ Approval workflow (PENDING → APPROVED) works
- ✅ All tests 1-7 pass without errors

## 📞 Support

If you encounter issues:
1. Check the "If Something Goes Wrong" section above
2. Review the `RBAC_IMPLEMENTATION.md` file for detailed architecture
3. Read `RBAC_STATUS_REPORT.md` for troubleshooting tip
4. Check Symfony logs: `tail -f var/log/dev.log`

---

**Remember**: The most critical step is Step 1️⃣ (Run Migration). Everything else depends on it!

Good luck! 🚀
