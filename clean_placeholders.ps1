$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

$filesFixed = 0
foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $original = $content
    # Remove the leftover comment + hash line
    $content = $content -replace '(?m)<!-- Static list removed - add dynamic content -->\s*# Provide a brief placeholder so developers know to add dynamic content\s*', '<!-- Static list removed - add dynamic content -->'
    # Remove stray if/elseif/else lines left behind
    $content = $content -replace '(?m)^\s*(if\s*\(\$pat.*|elseif\s*\(\$pat.*|else\s*\{\s*return\s*".*?"\s*\})\s*\r?\n', ''
    if ($content -ne $original) {
        Set-Content $file.FullName $content -Encoding UTF8
        Write-Host "Cleaned placeholders: $($file.FullName)"
        $filesFixed++
    }
}
Write-Host "`nTotal placeholder cleans: $filesFixed"