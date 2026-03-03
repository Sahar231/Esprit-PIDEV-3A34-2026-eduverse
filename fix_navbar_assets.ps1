# Fix image src in navbar (assets paths)
$templateDir = "c:\Users\Sahar\Bureau\PIWEB\education\templates"
$count = 0

Get-ChildItem -Path $templateDir -Recurse -Include "*.html.twig" -File | ForEach-Object {
    $file = $_
    $content = Get-Content -Path $file.FullName -Raw
    $original = $content

    # Replace navbar logo images
    $content = $content -replace 'src="assets/images/logo\.svg"', 'src="{{ asset("images/logo.svg") }}"'
    $content = $content -replace 'src="assets/images/logo-light\.svg"', 'src="{{ asset("images/logo-light.svg") }}"'
    $content = $content -replace 'src="assets/images/logo-mobile\.svg"', 'src="{{ asset("images/logo-mobile.svg") }}"'
    $content = $content -replace 'src="assets/images/logo-mobile-light\.svg"', 'src="{{ asset("images/logo-mobile-light.svg") }}"'
    
    # Replace all remaining assets/ paths in src attributes
    $content = $content -replace 'src="assets/([^"]*)"', 'src="{{ asset("$1") }}'
    
    # Replace all remaining assets/ paths in href attributes (if any)
    $content = $content -replace 'href="assets/([^"]*)"', 'href="{{ asset("$1") }}'

    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content
        $count++
        Write-Host "Fixed: $($file.Name)"
    }
}

Write-Host "Total files updated: $count"
