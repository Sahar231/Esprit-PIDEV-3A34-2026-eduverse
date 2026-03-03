$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

$filesFixed = 0
foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $original = $content
    # Fix cases where previous replacement left 'param($m)' after placeholder
    $content = $content -replace '(?m)<!-- Static list removed - add dynamic content -->\s*param\(\$m\)\s*', '<!-- Static list removed - add dynamic content -->'
    # Remove any stray param($m) lines
    $content = $content -replace '(?m)^\s*param\(\$m\)\s*\r?\n', ''
    if ($content -ne $original) {
        Set-Content $file.FullName $content -Encoding UTF8
        Write-Host "Fixed stray param in: $($file.FullName)"
        $filesFixed++
    }
}
Write-Host "`nTotal stray param fixes: $filesFixed"