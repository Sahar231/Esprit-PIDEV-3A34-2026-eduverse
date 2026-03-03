# Fix path() calls that require ID parameters
$templateDir = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$count = 0

# Routes that require 'id' parameter
$routesWithId = @(
    'course_detail',
    'course_detail_advanced',
    'course_detail_minimal',
    'course_detail_module',
    'course_video_player',
    'blog_detail',
    'instructor_detail',
    'event_detail',
    'event_workshop_detail',
    'student_course_resume',
    'shop_product_detail',
    'help_center_detail',
    'admin_edit_course',
    'admin_instructor_detail'
)

Get-ChildItem -Path $templateDir -Recurse -Include "*.html.twig" -File | ForEach-Object {
    $file = $_
    $content = Get-Content -Path $file.FullName -Raw
    $original = $content

    # For each route that require ID, replace path('routeName') with path('routeName', {'id': 1})
    foreach ($route in $routesWithId) {
        $pattern1 = "path\('" + $route + "'\)"
        $replacement1 = "path('" + $route + "', {'id': 1})"
        $content = $content -replace $pattern1, $replacement1
        
        $pattern2 = 'path\("' + $route + '"\)'
        $replacement2 = 'path("' + $route + '", {"id": 1})'
        $content = $content -replace $pattern2, $replacement2
    }

    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content
        $count++
        Write-Host "Fixed: $($file.Name)"
    }
}

Write-Host "Total files fixed: $count"
