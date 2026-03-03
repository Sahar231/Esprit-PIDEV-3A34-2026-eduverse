# Script to remove event and shop references from all templates
$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

$filesFixed = 0

foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Remove event_workshop_detail and event_detail lines
    $content = $content -replace '\s*<li>\s*<a class="dropdown-item" href="\{\{\s*path\(''event_workshop_detail''[^)]*\)\s*\}\}">Workshop Detail</a></li>\s*', ""
    $content = $content -replace '\s*<li>\s*<a class="dropdown-item" href="\{\{\s*path\(''event_detail''[^)]*\)\s*\}\}">Event Detail</a></li>\s*', ""
    
    # Remove card titles with workshop links
    $content = $content -replace '<h5 class="card-title"><a href="\{\{\s*path\(''event_workshop_detail''[^)]*\)\s*\}\}"[^>]*>[^<]*</a></h5>', ""
    
    # Remove Shop submenu block
    $content = $content -replace '(?sm)<!-- Dropdown submenu -->\s*<li class="dropdown-submenu dropend">\s*<a class="dropdown-item dropdown-toggle" href="#">Shop</a>\s*<ul class="dropdown-menu dropdown-menu-start" data-bs-popper="none">\s*(?:<li>[^<]*<a class="dropdown-item" href="\{\{\s*path\(''shop[^)]*\)\s*\}\}">[^<]*</a></li>\s*)*</ul>\s*</li>\s*', ""
    
    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        $filesFixed++
        Write-Host "Fixed: $($file.Name)"
    }
}

Write-Host "`nTotal files fixed: $filesFixed"
