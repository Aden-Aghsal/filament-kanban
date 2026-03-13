$ErrorActionPreference = "Stop"

param(
    [string]$Queue = "database",
    [int]$Sleep = 3,
    [int]$Tries = 3,
    [int]$Timeout = 120
)

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
Set-Location $projectRoot

Write-Host "Starting Laravel queue worker..."
Write-Host "Queue=$Queue Sleep=$Sleep Tries=$Tries Timeout=$Timeout"

while ($true) {
    try {
        php artisan queue:work $Queue --sleep=$Sleep --tries=$Tries --timeout=$Timeout
    } catch {
        Write-Host "Queue worker crashed. Restarting in 2 seconds..."
        Start-Sleep -Seconds 2
    }
}
