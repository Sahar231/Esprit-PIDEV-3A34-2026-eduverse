# PowerShell script to fix asset paths in Twig templates
$TEMPLATES_DIR = "c:\Users\Sahar\Bureau\PIWEB\education\templates"

Write-Host "Fixing asset paths in Twig templates..." -ForegroundColor Cyan

$twig_files = Get-ChildItem $TEMPLATES_DIR -Filter "*.html.twig" -Recurse
$modified_count = 0

foreach ($file in $twig_files) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $original_content = $content
    
    # Replace asset paths with Twig asset() function
    # Pattern 1: src="assets/... -> src="{{ asset('...') }}"
    $content = $content -replace 'src="assets/', 'src="{{ asset('
    $content = $content -replace 'src=''assets/', "src='{{ asset('"
    
    # Pattern 2: href="assets/... -> href="{{ asset('...') }}"
    $content = $content -replace 'href="assets/', 'href="{{ asset('
    $content = $content -replace "href='assets/", "href='{{ asset('"
    
    if ($content -ne $original_content) {
        Set-Content $file.FullName -Value $content -Encoding UTF8
        $relative_path = $file.FullName -replace [regex]::Escape($TEMPLATES_DIR), ""
        Write-Host "✓ Fixed: $relative_path" -ForegroundColor Green
        $modified_count++
    }
}

Write-Host "`nModified $modified_count files" -ForegroundColor Cyan
