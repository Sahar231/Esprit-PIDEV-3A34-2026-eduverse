$files = @(
    "templates\student\student-bookmark.html.twig",
    "templates\main\wishlist.html.twig",
    "templates\main\university-admission-form.html.twig",
    "templates\main\request-demo.html.twig",
    "templates\main\pricing.html.twig",
    "templates\main\index.html.twig",
    "templates\main\index-8.html.twig",
    "templates\main\index-6.html.twig",
    "templates\main\index-5.html.twig",
    "templates\main\index-2.html.twig",
    "templates\main\index-12.html.twig",
    "templates\main\faq.html.twig",
    "templates\main\error-404.html.twig",
    "templates\main\contact-us.html.twig",
    "templates\main\index-9.html.twig",
    "templates\main\become-instructor.html.twig",
    "templates\main\abroad-single.html.twig",
    "templates\main\book-class.html.twig",
    "templates\main\about.html.twig",
    "templates\instructor\instructor-studentlist.html.twig",
    "templates\student\student-quiz.html.twig",
    "templates\student\student-payment-info.html.twig",
    "templates\student\student-dashboard.html.twig",
    "templates\student\student-subscription.html.twig",
    "templates\student\student-course-resume.html.twig"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        $content = Get-Content $file -Raw
        $original = $content
        
        # Remove event/workshop items and shop blocks with flexible regex
        $content = $content -replace '[\s]*<li> <a class="dropdown-item" href="\{\{ path\(''event_workshop_detail''[^)]*\) \}\}">Workshop Detail</a></li>[\s]*', ""
        $content = $content -replace '[\s]*<li> <a class="dropdown-item" href="\{\{ path\(''event_detail''[^)]*\) \}\}">Event Detail</a></li>[\s]*', ""
        
        # Remove Shop dropdown block (multiline)
        $content = $content -replace '<!-- Dropdown submenu -->\s*\n\s*<li class="dropdown-submenu dropend">\s*\n\s*<a class="dropdown-item dropdown-toggle" href="#">Shop</a>\s*\n\s*<ul class="dropdown-menu dropdown-menu-start" data-bs-popper="none">\s*\n([\s\S]*?)<\/ul>\s*\n\s*<\/li>', ""
        
        if ($content -ne $original) {
            Set-Content $file $content -Encoding UTF8
            Write-Host "Cleaned: $(Split-Path $file -Leaf)"
        }
    }
}

Write-Host "`nCleanup complete!"
