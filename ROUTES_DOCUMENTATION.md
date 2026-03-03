# Education Platform - Routes Documentation

## Home & Main Pages
- `/` - Home page (index)
- `/about` - About page
- `/contact` - Contact us
- `/faq` - FAQ page
- `/pricing` - Pricing page
- `/become-instructor` - Become instructor
- `/coming-soon` - Coming soon page
- `/error-404` - 404 error page
- `/request-demo` - Request demo  
- `/request-access` - Request access
- `/abroad-single` - Abroad information
- `/university-admission` - University admission form
- `/index-2` to `/index-12` - Alternative home page variants

## Authentication
- `/auth/login` - Login page
- `/auth/register` - Registration page
- `/auth/forgot-password` - Forgot password

## Courses
- `/courses/` - Course list
- `/courses/grid` - Course grid view
- `/courses/grid-2` - Course grid 2 view
- `/courses/categories` - Course categories
- `/courses/detail/{id}` - Course detail
- `/courses/detail-advanced/{id}` - Advanced course detail
- `/courses/detail-minimal/{id}` - Minimal course detail
- `/courses/detail-module/{id}` - Course detail with modules
- `/courses/video-player/{id}` - Course video player
- `/courses/list-2` - Alternative course list
- `/courses/added` - Course added confirmation
- `/courses/book-class` - Book a class

## Shop
- `/shop/` - Shop home
- `/shop/product/{id}` - Product detail
- `/shop/cart` - Shopping cart
- `/shop/empty-cart` - Empty cart page
- `/shop/checkout` - Checkout
- `/shop/wishlist` - Wishlist

## Blog
- `/blog/` - Blog list
- `/blog/grid` - Blog grid view
- `/blog/masonry` - Blog masonry view
- `/blog/detail/{id}` - Blog post detail

## Events
- `/event/{id}` - Event detail
- `/event/workshop/{id}` - Workshop detail

## Admin
- `/admin/dashboard` - Admin dashboard
- `/admin/courses` - All courses
- `/admin/course-category` - Course categories management
- `/admin/course-detail` - Course details
- `/admin/edit-course/{id}` - Edit course
- `/admin/students` - Student list
- `/admin/instructors` - Instructor list
- `/admin/instructor/{id}` - Instructor detail
- `/admin/instructor-requests` - Instructor requests
- `/admin/reviews` - Course reviews
- `/admin/earnings` - Platform earnings
- `/admin/settings` - Admin settings
- `/admin/error` - Admin error page

## Instructor Panel
- `/instructor/dashboard` - Instructor dashboard
- `/instructor/list` - Instructor list
- `/instructor/{id}` - Instructor profile
- `/instructor/create-course` - Create new course
- `/instructor/manage-courses` - Manage courses
- `/instructor/quiz` - Quiz management
- `/instructor/reviews` - Student reviews
- `/instructor/earnings` - Instructor earnings
- `/instructor/payout` - Payout management
- `/instructor/orders` - Orders
- `/instructor/students` - My students
- `/instructor/edit-profile` - Edit profile
- `/instructor/settings` - Settings
- `/instructor/delete-account` - Delete account

## Student Panel
- `/student/dashboard` - Student dashboard
- `/student/courses` - My courses
- `/student/course-resume/{id}` - Resume course
- `/student/quiz` - My quiz
- `/student/bookmarks` - Bookmarked courses
- `/student/subscription` - My subscription
- `/student/payment-info` - Payment information

## Help & Support
- `/help/center` - Help center
- `/help/center/{id}` - Help article detail

## Template Structure

```
templates/
├── base.html.twig                    # Base template with common layout
├── main/                             # Main & home pages
│   ├── index.html.twig
│   ├── about.html.twig
│   ├── contact-us.html.twig
│   └── ... (other main pages)
├── admin/                            # Admin dashboard pages
│   ├── admin-dashboard.html.twig
│   ├── admin-course-list.html.twig
│   └── ... (13 admin pages)
├── course/                           # Course pages
│   ├── course-list.html.twig
│   ├── course-detail.html.twig
│   └── ... (12 course pages)
├── instructor/                       # Instructor panel pages
│   ├── instructor-dashboard.html.twig
│   ├── instructor-create-course.html.twig
│   └── ... (14 instructor pages)
├── student/                          # Student panel pages
│   ├── student-dashboard.html.twig
│   ├── student-courses.html.twig
│   └── ... (7 student pages)
├── shop/                             # Shop pages
│   ├── shop.html.twig
│   ├── cart.html.twig
│   └── ... (6 shop pages)
├── blog/                             # Blog pages
│   ├── blog-grid.html.twig
│   ├── blog-detail.html.twig
│   └── ... (3 blog pages)
├── event/                            # Event pages
│   └── event-detail.html.twig
├── auth/                             # Authentication pages
│   ├── sign-in.html.twig
│   ├── sign-up.html.twig
│   └── forgot-password.html.twig
└── help/                             # Help pages
    ├── help-center.html.twig
    └── help-center-detail.html.twig
```

## Template Count

- **Admin**: 13 templates
- **Course**: 12 templates
- **Instructor**: 14 templates
- **Student**: 7 templates
- **Shop**: 6 templates (including workshop-detail)
- **Blog**: 3 templates
- **Event**: 1 template
- **Auth**: 3 templates
- **Help**: 2 templates
- **Main**: 29 templates

**Total**: 90 templates

## Controller Files

All controllers are located in `src/Controller/`:

1. **HomeController.php** - Main pages and index variants
2. **AdminController.php** - Admin dashboard & management
3. **CourseController.php** - Course browsing & management
4. **InstructorController.php** - Instructor panel
5. **StudentController.php** - Student dashboard & features
6. **ShopController.php** - Shop & e-commerce
7. **BlogController.php** - Blog pages
8. **EventController.php** - Events & workshops
9. **AuthController.php** - Authentication pages
10. **HelpController.php** - Help & support pages

## Key Features

✓ All 85+ HTML templates converted to Twig format
✓ Templates organized into logical folders by functionality
✓ Controllers created with proper routing attributes
✓ RESTful route naming conventions applied
✓ Parameter handling for dynamic pages (by ID)
✓ Base template for consistent layout
✓ Proper namespace organization
