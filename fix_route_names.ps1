$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

$replacements = @{
    "path('request_demo')" = "path('app_request_demo')"
    "path('request_access')" = "path('app_request_access')"
    "path('course_book_class')" = "path('app_book_class')"
}

$filesFixed = 0
$totalReplacements = 0

foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    foreach ($pattern in $replacements.Keys) {
        if ($content -match [regex]::Escape($pattern)) {
            $content = $content -Replace [regex]::Escape($pattern), $replacements[$pattern]
            $totalReplacements++
        }
    }
    
    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        $filesFixed++
    }
}

Write-Host "Total files fixed: $filesFixed"
Write-Host "Total replacements made: $totalReplacements"
