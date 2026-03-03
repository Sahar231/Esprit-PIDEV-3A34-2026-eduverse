$files = @(
    "c:\Users\Sahar\Bureau\PIWEB\education\templates\main\index.html.twig",
    "c:\Users\Sahar\Bureau\PIWEB\education\templates\main\index-12.html.twig",
    "c:\Users\Sahar\Bureau\PIWEB\education\templates\main\index-3.html.twig"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        $content = Get-Content $file -Raw
        
        # Remove entire Popular course section including all course cards
        $replacement = "<!-- =====================`r`nPopular course section removed - Please add dynamic courses from database`r`n-->"
        $content = $content -replace '(?s)<!-- =+\s*[\r\n]+Popular course START.*?<!-- =+\s*[\r\n]+Popular course END\s*-->', $replacement

        Set-Content $file $content -Encoding UTF8
        Write-Host "Cleaned: $(Split-Path $file -Leaf)"
    }
}

Write-Host "Done! Popular courses sections removed."
