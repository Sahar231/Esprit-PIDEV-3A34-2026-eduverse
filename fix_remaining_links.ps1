$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

$replacements = @{
    # Admin routes
    'href="admin-dashboard.html"' = 'href="{{ path(''admin_dashboard'') }}"'
    'href="admin-course-list.html"' = 'href="{{ path(''admin_course_list'') }}"'
    'href="admin-course-category.html"' = 'href="{{ path(''admin_course_category'') }}"'
    'href="admin-course-detail.html"' = 'href="{{ path(''admin_course_detail'', {''id'': 1}) }}"'
    'href="admin-edit-course-detail.html"' = 'href="{{ path(''admin_edit_course'', {''id'': 1}) }}"'
    'href="admin-student-list.html"' = 'href="{{ path(''admin_student_list'') }}"'
    'href="admin-instructor-list.html"' = 'href="{{ path(''admin_instructor_list'') }}"'
    'href="admin-instructor-detail.html"' = 'href="{{ path(''admin_instructor_detail'', {''id'': 1}) }}"'
    'href="admin-instructor-detail.html.twig"' = 'href="{{ path(''admin_instructor_detail'', {''id'': 1}) }}"'
    'href="admin-instructor-request.html"' = 'href="{{ path(''admin_instructor_request'') }}"'
    'href="admin-review.html"' = 'href="{{ path(''admin_review'') }}"'
    'href="admin-earning.html"' = 'href="{{ path(''admin_earning'') }}"'
    'href="admin-setting.html"' = 'href="{{ path(''admin_setting'') }}"'
    'href="admin-error-404.html"' = 'href="{{ path(''admin_error'') }}"'
    
    # Course routes
    'href="course-list.html"' = 'href="{{ path(''course_list'') }}"'
    'href="course-grid.html"' = 'href="{{ path(''course_grid'') }}"'
    'href="course-categories.html"' = 'href="{{ path(''course_categories'') }}"'
    'href="course-detail.html"' = 'href="{{ path(''course_detail'', {''id'': 1}) }}"'
    'href="course-added.html"' = 'href="{{ path(''course_added'') }}"'
    
    # Main routes
    'href="request-demo.html"' = 'href="{{ path(''request_demo'') }}"'
    'href="request-access.html"' = 'href="{{ path(''request_access'') }}"'
    'href="university-admission-form.html"' = 'href="{{ path(''request_access'') }}"'
    'href="index.html"' = 'href="{{ path(''app_home'') }}"'
}

$filesFixed = 0
foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    foreach ($pattern in $replacements.Keys) {
        $content = $content -Replace [regex]::Escape($pattern), $replacements[$pattern]
    }
    
    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        $filesFixed++
        Write-Host "Fixed: $($file.Name)"
    }
}

Write-Host "`nTotal files fixed: $filesFixed"
