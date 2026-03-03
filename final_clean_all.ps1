$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

# Pattern to find and remove the entire Shop dropdown submenu block
$shopBlockPattern = @'
						<!-- Dropdown submenu -->
						<li class="dropdown-submenu dropend">
							<a class="dropdown-item dropdown-toggle" href="#">Shop</a>
							<ul class="dropdown-menu dropdown-menu-start" data-bs-popper="none">
								<li> <a class="dropdown-item" href="{{ path('shop_index') }}">Shop grid</a></li>
								<li> <a class="dropdown-item" href="{{ path('shop_product_detail', {'id': 1}) }}">Product detail</a></li>
								<li> <a class="dropdown-item" href="{{ path('shop_cart') }}">Cart</a></li>
								<li> <a class="dropdown-item" href="{{ path('shop_checkout') }}">Checkout</a></li>
								<li> <a class="dropdown-item" href="{{ path('shop_cart') }}">Empty Cart</a></li>
								<li> <a class="dropdown-item" href="{{ path('shop_wishlist') }}">Wishlist</a></li>
							</ul>
						</li>
'@

# Alternative pattern with different indentation
$shopBlockPattern2 = @'
									<!-- Dropdown submenu -->
									<li class="dropdown-submenu dropend">
										<a class="dropdown-item dropdown-toggle" href="#">Shop</a>
										<ul class="dropdown-menu dropdown-menu-start" data-bs-popper="none">
											<li> <a class="dropdown-item" href="{{ path('shop_index') }}">Shop grid</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_product_detail', {'id': 1}) }}">Product detail</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_cart') }}">Cart</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_checkout') }}">Checkout</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_cart') }}">Empty Cart</a></li>
											<li> <a class="dropdown-item" href="{{ path('shop_wishlist') }}">Wishlist</a></li>
										</ul>
									</li>
'@

$filesFixed = 0

foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Try first pattern
    if ($content -like "*$shopBlockPattern*") {
        $content = $content -Replace ([regex]::Escape($shopBlockPattern)), ""
    }
    
    # Try second pattern
    if ($content -like "*$shopBlockPattern2*") {
        $content = $content -Replace ([regex]::Escape($shopBlockPattern2)), ""
    }
    
    # Remove any remaining individual shop links
    $content = $content -Replace '\s*<li> <a class="dropdown-item" href="{{ path\(''shop[^)]*''\) }}">.*?</a></li>', ""
    $content = $content -Replace '\s*<li> <a class="dropdown-item" href="{{ path\(''shop[^)]*'', [^)]*\) }}">.*?</a></li>', ""
    
    # Remove remaining event links  
    $content = $content -Replace '\s*<li> <a class="dropdown-item" href="{{ path\(''event[^)]*'', [^)]*\) }}">.*?</a></li>', ""
    
    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        $filesFixed++
        Write-Host "Cleaned: $($file.Name)"
    }
}

Write-Host "`nTotal files cleaned: $filesFixed"
