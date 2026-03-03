$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

$filesFixed = 0

foreach ($file in $TwigFiles) {
    $lines = Get-Content $file.FullName
    $newLines = @()
    $changed = $false
    
    foreach ($line in $lines) {
        # Skip lines containing event_workshop_detail, event_detail, or shop dropdown items
        if ($line -match "event_workshop_detail|event_detail" -or 
            ($line -match "Dropdown submenu" -or $line -match "Shop</a>" -or $line -match 'shop_')) {
            # Skip this line
            $changed = $true
        }
        # Also skip empty lines after removals
        elseif (-not ($line -match "^\s*$" -and $lastRemoved)) {
            $newLines += $line
            $lastRemoved = $false
        }
        else {
            $lastRemoved = $true
        }
    }
    
    if ($changed) {
        $newLines | Set-Content $file.FullName -Encoding UTF8
        $filesFixed++
        Write-Host "Cleaned: $($file.Name)"
    }
}

Write-Host "`nTotal files cleaned: $filesFixed"
