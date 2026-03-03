$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

# Blocks to remove completely (Shop and Event dropdowns)
$blocksToRemove = @(
    # Shop submenu block
    @'
									<!-- Shop submenu -->
									<li class="dropdown-submenu dropend">
										<a class="dropdown-item dropdown-toggle" href="#">Shop</a>
										<ul class="dropdown-menu dropdown-menu-start" data-bs-popper="none">
											<li> <a class="dropdown-item" href="{{ path('shop_index') }}">Shop grid</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_product_detail', {'id': 1}) }}">Product Single</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_cart') }}">Cart</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_checkout') }}">Checkout</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_cart') }}">Empty Cart</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_wishlist') }}">Wishlist</a></li>
										</ul>
									</li>
'@,
    # Events submenu block
    @'
									<!-- Events submenu -->
									<li class="dropdown-submenu dropend">
										<a class="dropdown-item dropdown-toggle" href="#">Events</a>
										<ul class="dropdown-menu dropdown-menu-start" data-bs-popper="none">
											<li> <a class="dropdown-item" href="{{ path('event_detail', {'id': 1}) }}">Event Detail</a></li>
											<li> <a class="dropdown-item" href="{{ path('event_workshop_detail', {'id': 1}) }}">Workshop Detail</a></li>
										</ul>
									</li>
'@
)

$filesFixed = 0
$removedBlocks = 0

foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    foreach ($block in $blocksToRemove) {
        if ($content -match [regex]::Escape($block)) {
            $content = $content -Replace [regex]::Escape($block), ""
            $removedBlocks++
        }
    }
    
    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        $filesFixed++
        Write-Host "Cleaned: $($file.Name)"
    }
}

Write-Host "`nTotal files modified: $filesFixed"
Write-Host "Total blocks removed: $removedBlocks"
