# RBAC Implementation - Quick Reference Card

## 🔐 Access Control Matrix

```
Route                     | Admin | Instr(Own) | Instr(Other) | Student
─────────────────────────┼───────┼────────────┼──────────────┼─────────
/admin/quizzes            | ✅    | ❌         | ❌           | ❌
/instructor/quizzes       | ❌    | ✅         | ❌           | ❌
/student/quizzes          | ❌    | ❌         | ❌           | ✅
/instructor/{id}/edit     | ✅*   | ✅*        | 🚫*          | ❌
/instructor/{id}/delete   | ✅*   | ✅*        | 🚫*          | ❌
```
*Admin no check, Instructor checks ownership, Others get error + redirect

---

## 📊 Status Transition Diagram

```
┌─────────────────────────────────────────────┐
│ NULL (Draft) - Only owner sees             │
└──────────────┬────────────────┬─────────────┘
               │                │
        (edit) │                │ (submit or resubmit after refuse)
               ↓                │
        ┌─────────────┐         │
        │ PENDING     │◄────────┘
        │ Awaits      │
        │ Approval    │
        └────┬─────┬──┘
             │     │
      (app)  │     │ (refuse)
             │     │
             ↓     ↓
        ┌─────┐  ┌──────────┐
        │ APP │  │ REFUSED  │
        │ ROVE│  │ + Reason │
        │ D   │  └──────────┘
        │     │
        │ ✅  │  → Visible to
        │     │    Students
        └─────┘
```

---

## 🛡️ Ownership Check Code Pattern

**All InstructorQuizController methods follow this pattern:**

```php
public function methodName(Quiz $quiz): Response
{
    // Step 1: Check ownership
    if ($quiz->getInstructor() !== $this->getUser()) {
        $this->addFlash('error', 'Vous n\'avez pas accès à ce quiz.');
        return $this->redirectToRoute('instructor_quiz_list');
    }
    
    // Step 2: Proceed with authorized action
    // ... rest of method
}
```

---

## 📍 Route Protection Reference

| Route | Verb | Ownership Check | Status Filter |
|-------|------|-----------------|---------------|
| `/instructor/quizzes/` | GET | List only own | N/A (all statuses) |
| `/instructor/quizzes/new` | GET/POST | Auto-assign owner | Auto PENDING |
| `/instructor/quizzes/{id}/edit` | GET/POST | ✅ Check | Revert to PENDING |
| `/instructor/quizzes/{id}/delete` | POST | ✅ Check | N/A |
| `/instructor/quizzes/{id}/show` | GET | ✅ Check | N/A |
| `/instructor/quizzes/{id}/question/new` | GET/POST | ✅ Check | N/A |
| `/instructor/quizzes/question/{id}/edit` | GET/POST | ✅ Check | N/A |
| `/instructor/quizzes/question/{id}` | POST | ✅ Check | N/A |
| `/student/quizzes/` | GET | N/A | WHERE status='APPROVED' |
| `/admin/quizzes/` | GET | None | All statuses |

---

## 🔑 Key Methods Modified

### Quiz Entity
```php
// NEW: Instructor relationship
private ?User $instructor = null;
public function getInstructor(): ?User
public function setInstructor(?User $instructor): static
```

### InstructorQuizController (7 of 9 methods modified)

1. **list()** → Filter by owner
2. **new()** → Auto-assign owner + set PENDING
3. **edit()** → Check ownership + reset to PENDING
4. **delete()** → Check ownership
5. **addQuestion()** → Check ownership
6. **editQuestion()** → Check ownership
7. **deleteQuestion()** → Check ownership
8. **show()** → Check ownership
9. **submit()** → Already had logic (no change)

---

## 🧪 Quick Test Commands

### Check Database Changes
```sql
DESCRIBE quiz;  -- Look for instructor_id column
SHOW KEYS FROM quiz;  -- Check foreign key exists
```

### Database Validation
```bash
php bin/console doctrine:schema:validate
```

### Clear Cache
```bash
php bin/console cache:clear
```

### Check Routes
```bash
php bin/console debug:router | grep instructor_quiz
```

---

## ⚡ Common Issues & Fixes

| Issue | Cause | Fix |
|-------|-------|-----|
| "Instructor still sees all" | List not filtered | `cache:clear` + verify line 35 in controller |
| "Student sees PENDING" | Status filter missing | Check StudentQuizController.php line 29 |
| "Migration fails" | Already run | `doctrine:migrations:list` to verify |
| "Foreign key error" | instructor_id missing | `php bin/console make:migration && migrate` |
| "Class not found" | Cache stale | `php bin/console cache:clear` |

---

## 📝 File Quick Lookup

| Need to... | File | Where to Look |
|-----------|------|---------------|
| Check ownership logic | `src/Controller/Instructor/InstructorQuizController.php` | Any method with `$quiz->getInstructor() !== $this->getUser()` |
| Add new instructor field | `src/Entity/Quiz.php` | Look for `#[ORM\ManyToOne` |
| Hide student quizzes | `src/Controller/Student/QuizController.php` | Line ~29 with `status = :status` |
| Show admin buttons | `templates/admin/quiz/list.html.twig` | Approve/Refuse button section |
| Edit form | `src/Form/QuizType.php` | Form field definitions |

---

## 🚀 Migration Commands (Copy-Paste Ready)

```powershell
# Step 1: Generate migration from entity changes
php bin/console make:migration

# Step 2: Execute the migration
php bin/console doctrine:migrations:migrate --no-interaction

# Step 3: Clear application cache
php bin/console cache:clear

# Step 4: Validate schema matches entities
php bin/console doctrine:schema:validate
```

---

## 🎯 Testing Endpoints

### Admin Access (No ownership check)
- GET `/admin/quizzes/` → See all
- GET `/admin/quizzes/1/` → See any quiz
- POST `/admin/quizzes/1/approve` → Approve any
- POST `/admin/quizzes/1/refuse` → Refuse any

### Instructor Access (Ownership checked)
- GET `/instructor/quizzes/` → See only own
- GET `/instructor/quizzes/1/edit` → Edit only own ← Will fail if not owner
- POST `/instructor/quizzes/1/delete` → Delete only own ← Will fail if not owner
- GET `/instructor/quizzes/999/edit` → Redirect if 999 belongs to other

### Student Access (Status filtered at query level)
- GET `/student/quizzes/` → Only APPROVED
- Direct DB query `SELECT * BY STATUS NOT IN ('APPROVED')` → 0 results

---

## 🔐 Security Layer Summary

| Layer | Method | Enforcement |
|-------|--------|-------------|
| Database | Foreign Key + Constraint | Prevents invalid instructor_id |
| Entity | ManyToOne Mapping | Type-safe relationship |
| Controller | Ownership Check | `if ($quiz->getInstructor() !== $user)` |
| Query | Status Filter | `WHERE status = 'APPROVED'` in StudentController |
| Template | Button Visibility | Conditional display via Twig |

---

## 📊 Data Model

```
User (id, email, roles, ...)
  ↑
  │ (1:N - One instructor owns many quizzes)
  │
Quiz (id, title, instructor_id, status, ...)
  ↓
  │ (1:N - One quiz has many questions)
  │
Question (id, quiz_id, content, ...)
  ↓
  │ (1:N - One question has many responses)
  │
Reponse (id, question_id, content, isCorrect, ...)
```

---

## ✅ Pre-Deployment Checklist

- [ ] Migration file created and reviewed
- [ ] Migration executed without errors
- [ ] `instructor_id` column exists in database
- [ ] Foreign key constraint created
- [ ] Cache cleared
- [ ] Schema validation passes
- [ ] Syntax check passes
- [ ] Routes debug shows correct endpoints
- [ ] No uncommitted changes in git
- [ ] Documentation files generated

---

## 🎓 Key Files for Reference

1. **RBAC_IMPLEMENTATION.md** (350+ lines)
   - Full architecture
   - Security details
   - Testing strategy
   - Enhancement roadmap

2. **RBAC_QUICK_START.md** (Testing guide)
   - 7-step test procedure
   - Verification checklist
   - Troubleshooting section

3. **RBAC_STATUS_REPORT.md** (Summary)
   - What's completed
   - What's pending
   - Deployment guide

4. **IMPLEMENTATION_COMPLETE.md** (Overview)
   - Session summary
   - Timeline
   - Quality metrics

---

## 💾 Session Deliverables

| Deliverable | Type | Status |
|------------|------|--------|
| Instructor ownership field | Code | ✅ Complete |
| 7 ownership checks | Code | ✅ Complete |
| List filtering | Code | ✅ Complete |
| Error handling | Code | ✅ Complete |
| RBAC documentation | Docs | ✅ Complete |
| Testing guide | Docs | ✅ Complete |
| Quick reference | Docs | ✅ Complete |
| Database migration | Action | ⏳ Pending user |
| Testing & validation | Action | ⏳ Pending user |

---

**Keep this card handy during testing and deployment!** 📌

Last Updated: Current Session
Status: Ready for Migration & Testing
