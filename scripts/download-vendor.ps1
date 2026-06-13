$base = Join-Path $PSScriptRoot "..\assets\vendor" | Resolve-Path
New-Item -ItemType Directory -Force -Path "$base\bootstrap\css", "$base\bootstrap\js", "$base\bootstrap-icons\fonts", "$base\leaflet\images", "$base\chartjs" | Out-Null

$files = @{
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" = "$base\bootstrap\css\bootstrap.min.css"
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" = "$base\bootstrap\js\bootstrap.bundle.min.js"
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" = "$base\bootstrap-icons\bootstrap-icons.min.css"
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2" = "$base\bootstrap-icons\fonts\bootstrap-icons.woff2"
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff" = "$base\bootstrap-icons\fonts\bootstrap-icons.woff"
    "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" = "$base\leaflet\leaflet.css"
    "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" = "$base\leaflet\leaflet.js"
    "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png" = "$base\leaflet\images\marker-icon.png"
    "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png" = "$base\leaflet\images\marker-icon-2x.png"
    "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png" = "$base\leaflet\images\marker-shadow.png"
    "https://unpkg.com/leaflet@1.9.4/dist/images/layers.png" = "$base\leaflet\images\layers.png"
    "https://unpkg.com/leaflet@1.9.4/dist/images/layers-2x.png" = "$base\leaflet\images\layers-2x.png"
    "https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" = "$base\chartjs\chart.umd.min.js"
}

foreach ($entry in $files.GetEnumerator()) {
    curl.exe -fsSL $entry.Key -o $entry.Value
    Write-Host "Downloaded $($entry.Value)"
}

Write-Host "Vendor assets ready in $base"
