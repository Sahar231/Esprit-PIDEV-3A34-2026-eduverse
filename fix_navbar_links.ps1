# Script to fix navbar links in all template files
$templateDir = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$count = 0
$errorCount = 0

# Route mapping
$routes = @{
    # Home pages
    'index\.html' = 'app_home'
    'index-2\.html' = 'app_index_2'
    'index-3\.html' = 'app_index_3'
    'index-4\.html' = 'app_index_4'
    'index-5\.html' = 'app_index_5'
    'index-6\.html' = 'app_index_6'
    'index-7\.html' = 'app_index_7'
    'index-8\.html' = 'app_index_8'
    'index-9\.html' = 'app_index_9'
    'index-10\.html' = 'app_index_10'
    'index-11\.html' = 'app_index_11'
    'index-12\.html' = 'app_index_12'
    'about\.html' = 'app_about'
    'contact-us\.html' = 'app_contact'
    'faq\.html' = 'app_faq'
    'pricing\.html' = 'app_pricing'
    'become-instructor\.html' = 'app_become_instructor'
    'coming-soon\.html' = 'app_coming_soon'
    'error-404\.html' = 'app_error_404'
    'abroad-single\.html' = 'app_abroad_single'
    'university-admission\.html' = 'app_university_admission'
    
    # Course pages
    'course-list\.html' = 'course_list'
    'course-grid\.html' = 'course_grid'
    'course-grid-2\.html' = 'course_grid_2'
    'course-categories\.html' = 'course_categories'
    'course-detail\.html' = 'course_detail'
    'course-detail-adv\.html' = 'course_detail_advanced'
    'course-detail-min\.html' = 'course_detail_minimal'
    'course-detail-module\.html' = 'course_detail_module'
    'course-video-player\.html' = 'course_video_player'
    'course-list-2\.html' = 'course_list_2'
    'course-added\.html' = 'course_added'
    'book-class\.html' = 'course_book_class'
    
    # Blog pages
    'blog-grid\.html' = 'blog_list'
    'blog-masonry\.html' = 'blog_masonry'
    'blog-detail\.html' = 'blog_detail'
    
    # Shop pages
    'shop\.html' = 'shop_index'
    'shop-product-detail\.html' = 'shop_product_detail'
    'cart\.html' = 'shop_cart'
    'checkout\.html' = 'shop_checkout'
    'empty-cart\.html' = 'shop_empty_cart'
    'wishlist\.html' = 'shop_wishlist'
    
    # Instructor pages
    'instructor-list\.html' = 'instructor_list'
    'instructor-single\.html' = 'instructor_detail'
    'instructor-dashboard\.html' = 'instructor_dashboard'
    'instructor-create-course\.html' = 'instructor_create_course'
    'instructor-manage-course\.html' = 'instructor_manage_courses'
    'instructor-quiz\.html' = 'instructor_quiz'
    'instructor-review\.html' = 'instructor_reviews'
    'instructor-earning\.html' = 'instructor_earnings'
    'instructor-payout\.html' = 'instructor_payout'
    'instructor-order\.html' = 'instructor_orders'
    'instructor-studentlist\.html' = 'instructor_students'
    'instructor-edit-profile\.html' = 'instructor_edit_profile'
    'instructor-setting\.html' = 'instructor_settings'
    'instructor-delete-account\.html' = 'instructor_delete_account'
    
    # AUTH pages
    'sign-in\.html' = 'auth_login'
    'sign-up\.html' = 'auth_register'
    'forgot-password\.html' = 'auth_forgot_password'
    
    # Help pages
    'help-center\.html' = 'help_center'
    'help-center-detail\.html' = 'help_center_detail'
    
    # Event pages
    'event-detail\.html' = 'event_detail'
    'workshop-detail\.html' = 'event_workshop_detail'
    
    # Student pages
    'student-dashboard\.html' = 'student_dashboard'
    'student-course-list\.html' = 'student_courses'
    'student-course-resume\.html' = 'student_course_resume'
    'student-quiz\.html' = 'student_quiz'
    'student-bookmark\.html' = 'student_bookmarks'
    'student-subscription\.html' = 'student_subscription'
    'student-payment-info\.html' = 'student_payment_info'
}

Get-ChildItem -Path $templateDir -Recurse -Include "*.html.twig" -File | ForEach-Object {
    $file = $_
    $content = Get-Content -Path $file.FullName -Raw
    $original = $content

    # Replace each HTML file with corresponding route
    foreach ($htmlFile in $routes.Keys) {
        $routeName = $routes[$htmlFile]
        
        # Replace href="filename.html" with Twig path
        $pattern = "href=`"[^`"]*\b" + $htmlFile + "\b[^`"]*`""
        $replacement = "href=`"{{ path('" + $routeName + "') }}`""
        $content = $content -replace $pattern, $replacement
    }

    # Write back if changed
    if ($content -ne $original) {
        try {
            Set-Content -Path $file.FullName -Value $content -ErrorAction Stop
            $count++
            Write-Host "OK: $($file.Name)"
        }
        catch {
            $errorCount++
            Write-Host "ERROR in $($file.Name): $_" -ForegroundColor Red
        }
    }
}

Write-Host "`n========================"
Write-Host "Files updated: $count"
Write-Host "Errors: $errorCount"
Write-Host "========================"
