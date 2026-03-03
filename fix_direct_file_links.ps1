# Fix direct file links (xxx.html or xxx.html.twig) to use path() instead
$templateDir = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$count = 0

# Map of old file links to new path() calls
$linkMap = @{
    'admin-dashboard\.html' = "{{ path('admin_dashboard') }}"
    'admin-course-list\.html' = "{{ path('admin_courses') }}"
    'admin-course-category\.html' = "{{ path('admin_course_category') }}"
    'admin-course-detail\.html' = "{{ path('admin_course_detail') }}"
    'admin-course-detail\.html\.twig' = "{{ path('admin_course_detail') }}"
    'admin-edit-course-detail\.html' = "{{ path('admin_edit_course', {'id': 1}) }}"
    'admin-student-list\.html' = "{{ path('admin_students') }}"
    'admin-instructor-list\.html' = "{{ path('admin_instructors') }}"
    'admin-instructor-detail\.html' = "{{ path('admin_instructor_detail', {'id': 1}) }}"
    'admin-instructor-detail\.html\.twig' = "{{ path('admin_instructor_detail', {'id': 1}) }}"
    'admin-instructor-request\.html' = "{{ path('admin_instructor_requests') }}"
    'admin-review\.html' = "{{ path('admin_reviews') }}"
    'admin-earning\.html' = "{{ path('admin_earnings') }}"
    'admin-setting\.html' = "{{ path('admin_settings') }}"
    'admin-error-404\.html' = "{{ path('app_error_404') }}"
    'course-list\.html' = "{{ path('course_list') }}"
    'course-grid\.html' = "{{ path('course_grid') }}"
    'course-grid-2\.html' = "{{ path('course_grid_2') }}"
    'course-categories\.html' = "{{ path('course_categories') }}"
    'course-detail\.html' = "{{ path('course_detail', {'id': 1}) }}"
    'course-detail-adv\.html' = "{{ path('course_detail_advanced', {'id': 1}) }}"
    'course-detail-min\.html' = "{{ path('course_detail_minimal', {'id': 1}) }}"
    'course-detail-module\.html' = "{{ path('course_detail_module', {'id': 1}) }}"
    'course-video-player\.html' = "{{ path('course_video_player', {'id': 1}) }}"
    'course-list-2\.html' = "{{ path('course_list_2') }}"
    'course-added\.html' = "{{ path('course_added') }}"
    'book-class\.html' = "{{ path('course_book_class') }}"
    'blog-grid\.html' = "{{ path('blog_list') }}"
    'blog-masonry\.html' = "{{ path('blog_masonry') }}"
    'blog-detail\.html' = "{{ path('blog_detail', {'id': 1}) }}"
    'shop\.html' = "{{ path('shop_index') }}"
    'shop-product-detail\.html' = "{{ path('shop_product_detail', {'id': 1}) }}"
    'cart\.html' = "{{ path('shop_cart') }}"
    'checkout\.html' = "{{ path('shop_checkout') }}"
    'empty-cart\.html' = "{{ path('shop_empty_cart') }}"
    'wishlist\.html' = "{{ path('shop_wishlist') }}"
    'instructor-list\.html' = "{{ path('instructor_list') }}"
    'instructor-dashboard\.html' = "{{ path('instructor_dashboard') }}"
    'instructor-single\.html' = "{{ path('instructor_detail', {'id': 1}) }}"
    'instructor-create-course\.html' = "{{ path('instructor_create_course') }}"
    'instructor-manage-course\.html' = "{{ path('instructor_manage_courses') }}"
    'instructor-quiz\.html' = "{{ path('instructor_quiz') }}"
    'instructor-review\.html' = "{{ path('instructor_reviews') }}"
    'instructor-earning\.html' = "{{ path('instructor_earnings') }}"
    'instructor-payout\.html' = "{{ path('instructor_payout') }}"
    'instructor-order\.html' = "{{ path('instructor_orders') }}"
    'instructor-studentlist\.html' = "{{ path('instructor_students') }}"
    'instructor-edit-profile\.html' = "{{ path('instructor_edit_profile') }}"
    'instructor-setting\.html' = "{{ path('instructor_settings') }}"
    'instructor-delete-account\.html' = "{{ path('instructor_delete_account') }}"
    'sign-in\.html' = "{{ path('auth_login') }}"
    'sign-up\.html' = "{{ path('auth_register') }}"
    'forgot-password\.html' = "{{ path('auth_forgot_password') }}"
    'help-center\.html' = "{{ path('help_center') }}"
    'help-center-detail\.html' = "{{ path('help_center_detail', {'id': 1}) }}"
    'event-detail\.html' = "{{ path('event_detail', {'id': 1}) }}"
    'workshop-detail\.html' = "{{ path('event_workshop_detail', {'id': 1}) }}"
    'student-dashboard\.html' = "{{ path('student_dashboard') }}"
    'student-course-list\.html' = "{{ path('student_courses') }}"
    'student-course-resume\.html' = "{{ path('student_course_resume', {'id': 1}) }}"
    'student-quiz\.html' = "{{ path('student_quiz') }}"
    'student-bookmark\.html' = "{{ path('student_bookmarks') }}"
    'student-subscription\.html' = "{{ path('student_subscription') }}"
    'student-payment-info\.html' = "{{ path('student_payment_info') }}"
    'index\.html' = "{{ path('app_home') }}"
    'about\.html' = "{{ path('app_about') }}"
    'contact-us\.html' = "{{ path('app_contact') }}"
    'faq\.html' = "{{ path('app_faq') }}"
    'pricing\.html' = "{{ path('app_pricing') }}"
    'become-instructor\.html' = "{{ path('app_become_instructor') }}"
    'coming-soon\.html' = "{{ path('app_coming_soon') }}"
    'abroad-single\.html' = "{{ path('app_abroad_single') }}"
}

Get-ChildItem -Path $templateDir -Recurse -Include "*.html.twig" -File | ForEach-Object {
    $file = $_
    $content = Get-Content -Path $file.FullName -Raw
    $original = $content

    # Replace each file link with path() call
    foreach ($pattern in $linkMap.Keys) {
        $replacement = $linkMap[$pattern]
        # Match href="filename or href='filename
        $content = $content -replace "href=`"$pattern`"", "href=`"$replacement`""
        $content = $content -replace "href='$pattern'", "href='$replacement'"
    }

    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content
        $count++
        Write-Host "Fixed: $($file.Name)"
    }
}

Write-Host "`nTotal files fixed: $count"
