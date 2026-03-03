$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

# Patterns to remove (looking for menu items/links related to shop and events)
$linesToRemove = @(
    # Shop links
    '\s*<li> <a class="dropdown-item" href="{{ path\(''shop_index''\) }}">Shop grid</a></li>',
    '\s*<li> <a class="dropdown-item" href="{{ path\(''shop_product_detail'', \{''id'': 1\}\) }}">Product Single</a></li>',
    '\s*<li> <a class="dropdown-item" href="{{ path\(''shop_cart''\) }}">Cart</a></li>',
    '\s*<li> <a class="dropdown-item" href="{{ path\(''shop_checkout''\) }}">Checkout</a></li>',
    '\s*<li> <a class="dropdown-item" href="{{ path\(''shop_wishlist''\) }}">Wishlist</a></li>',
    '\s*<li> <a class="dropdown-item" href="{{ path\(''shop_cart''\) }}">Empty Cart</a></li>',
    
    # Event/Workshop links
    '\s*<li> <a class="dropdown-item" href="{{ path\(''event_detail'', \{''id'': 1\}\) }}">Event Detail</a></li>',
    '\s*<li> <a class="dropdown-item" href="{{ path\(''event_workshop_detail'', \{''id'': 1\}\) }}">Workshop Detail</a></li>',
    '\s*<li> <a class="dropdown-item" href="{{ path\(''workshop_detail'', \{''id'': 1\}\) }}">Workshop Detail</a></li>'
)

$filesFixed = 0
$removedLines = 0

foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    foreach ($pattern in $linesToRemove) {
        if ($content -match $pattern) {
            $content = $content -Replace $pattern, ""
            $removedLines++
        }
    }
    
    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        $filesFixed++
        Write-Host "Fixed: $($file.Name)"
    }
}

Write-Host "`nTotal files modified: $filesFixed"
Write-Host "Total link lines removed: $removedLines"
