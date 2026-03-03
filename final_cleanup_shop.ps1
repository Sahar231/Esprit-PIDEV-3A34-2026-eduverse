$TemplatesPath = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$TwigFiles = Get-ChildItem -Path $TemplatesPath -Recurse -Filter "*.html.twig" -File

$filesFixed = 0

foreach ($file in $TwigFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Remove event/workshop items (these are just links without a dropdown container)
    $content = $content -Replace [regex]::Escape('							<li> <a class="dropdown-item" href="{{ path(''event_workshop_detail'', {''id'': 1}) }}">Workshop Detail</a></li>'), ""
    $content = $content -Replace [regex]::Escape('							<li> <a class="dropdown-item" href="{{ path(''event_detail'', {''id'': 1}) }}">Event Detail</a></li>'), ""
    
    # Remove entire Shop dropdown submenu block (with all its content)
    $content = $content -Replace @'
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
'@, ""
    
    # Alternative indentation patterns
    $content = $content -Replace @'
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
'@, ""
    
    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        $filesFixed++
        Write-Host "Cleaned: $($file.Name)"
    }
}

Write-Host "`nTotal files cleaned: $filesFixed"
