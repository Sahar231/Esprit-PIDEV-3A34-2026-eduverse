# Script to remove all static instructor and student lists from templates
$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"

# Only process admin, student, and instructor template folders
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File | Where-Object { $_.FullName -match '\\templates\\(admin|student|instructor)\\' }

$filesFixed = 0

# Patterns to remove (singleline + case-insensitive)
$patterns = @(
    '(?si)<!--\s*Instructor list START.*?<!--\s*Instructor list END\s*-->',
    '(?si)<!--\s*Instructor START.*?<!--\s*Instructor END\s*-->',
    '(?si)<!--\s*Related instructor START.*?<!--\s*Related instructor END\s*-->',
    '(?si)<!--\s*Student list START.*?<!--\s*Student list END\s*-->',
    '(?si)<!--\s*Student START.*?<!--\s*Student END\s*-->',
    '(?si)<!--\s*Card item START.*?Card item END\s*-->',
    '(?si)<ul[^>]*class="avatar-group".*?</ul>',
    '(?si)<div[^>]*class="avatar[^>]*>.*?</div>'
)

foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content

    foreach ($pat in $patterns) {
        $content = $content -replace $pat, {
            param($m)
            # Provide a brief placeholder so developers know to add dynamic content
            if ($pat -match 'Instructor') { return "<!-- Instructor section removed - add dynamic instructors from DB -->" }
            elseif ($pat -match 'Student') { return "<!-- Student section removed - add dynamic students from DB -->" }
            else { return "<!-- Static list removed -->" }
        }
    }

    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        $filesFixed++
        Write-Host "Fixed: $($file.FullName)"
    }
}

Write-Host "`nTotal files cleaned: $filesFixed"
