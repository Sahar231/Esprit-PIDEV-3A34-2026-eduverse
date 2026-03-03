# Fix injected PowerShell snippet accidentally inserted into templates
$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

$filesFixed = 0
# Match the injected snippet from 'param($m)' until the end of the if/elseif/else block
$pattern = '(?sm)^\s*param\(\$m\)\s*.*?else\s*\{\s*return\s*".*?"\s*\}\s*'

foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $original = $content
    $content = [regex]::Replace($content, $pattern, "<!-- Static list removed - add dynamic content -->")
    if ($content -ne $original) {
        Set-Content $file.FullName $content -Encoding UTF8
        Write-Host "Cleaned: $($file.FullName)"
        $filesFixed++
    }
}
Write-Host "`nTotal injected snippets cleaned: $filesFixed"