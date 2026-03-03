# Quick cleanup: Remove duplicate instructor list END and all content between them
$filePath = "c:\Users\Sahar\Bureau\PIWEB\education\templates\instructor\instructor-list.html.twig"
$content = Get-Content $filePath -Raw

# Find first occurrence of "Instructor list END" and remove everything until the second occurrence (inclusive)
$pattern = "(\t<!-- Instructor list END -->)\n\t\t\t<!-- Card item START -->.*?(</div>\n\t\t<!-- Instructor list END -->)"
$replacement = "`$1"

$content = $content -replace $pattern, $replacement, [Text.RegularExpressions.RegexOptions]::Singleline

Set-Content $filePath $content -Encoding UTF8
Write-Host "Cleaned up instructor-list.html.twig"
