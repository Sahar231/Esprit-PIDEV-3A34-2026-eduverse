# Regenerate Twig templates from HTML bootstrap folder
$SRC = "c:\Users\Sahar\Bureau\symfony\template_education_bootstrap"
$DEST = "c:\Users\Sahar\Bureau\PIWEB\education\templates"

$categoryMap = @{
    'admin' = 'admin'
    'course' = 'course'
    'instructor' = 'instructor'
    'student' = 'student'
    'shop' = 'shop'
    'blog' = 'blog'
    'event' = 'event'
    'help-center' = 'help'
    'sign-in' = 'auth'
    'sign-up' = 'auth'
    'forgot-password' = 'auth'
}

function Get-Category {
    param([string]$filename)
    foreach ($keyword in $categoryMap.Keys) {
        if ($filename -like "*$keyword*") {
            return $categoryMap[$keyword]
        }
    }
    return 'main'
}

function Extract-Body {
    param([string]$html)
    $start = $html.IndexOf('<body')
    if ($start -eq -1) { return $html }
    
    $start = $html.IndexOf('>', $start) + 1
    $end = $html.IndexOf('</body>', $start)
    if ($end -eq -1) { $end = $html.Length }
    
    return $html.Substring($start, $end - $start)
}

function Fix-Assets {
    param([string]$content)
    
    # Replace src="assets/ with src="{{ asset('
    $content = $content -replace 'src="assets/([^"]*)"', 'src="{{ asset(''$1'') }}"'
    
    # Replace href="assets/ with href="{{ asset('
    $content = $content -replace 'href="assets/([^"]*)"', 'href="{{ asset(''$1'') }}"'
    
    return $content
}

$count = 0
Get-ChildItem $SRC -Filter "*.html" | ForEach-Object {
    $name = $_.BaseName
    $category = Get-Category -filename $name
    
    # Read HTML
    $html = [System.IO.File]::ReadAllText($_.FullName, [System.Text.Encoding]::UTF8)
    
    # Extract title from <title> tag
    $title = "Page"
    if ($html -match '<title[^>]*>([^<]+)</title>') {
        $title = $matches[1] -replace 'Eduport.*', '' -replace '.*- LMS.*', ''
        $title = $title.Trim()
        if ([string]::IsNullOrWhiteSpace($title)) {
            $title = $name -replace '-', ' '
        }
    }
    
    # Extract body content
    $body = Extract-Body -html $html
    
    # Fix asset paths
    $body = Fix-Assets -content $body
    
    # Create Twig content
    $twig = @"
{% extends 'base.html.twig' %}

{% block title %}$title{% endblock %}

{% block body %}
$body
{% endblock %}
"@
    
    # Write file
    $outPath = Join-Path $DEST $category "$name.html.twig"
    [System.IO.File]::WriteAllText($outPath, $twig, [System.Text.Encoding]::UTF8)
    $count++
    
    if ($count -le 10) {
        Write-Host "✓ $name -> $category"
    }
}

Write-Host "`nTotal: $count templates regenerated" -ForegroundColor Green
