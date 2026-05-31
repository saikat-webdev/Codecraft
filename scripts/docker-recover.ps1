# Recover when Docker Desktop / CLI is stuck (run PowerShell as Administrator if needed).
# Usage: .\scripts\docker-recover.ps1

$ErrorActionPreference = "SilentlyContinue"
$ProjectRoot = Split-Path -Parent $PSScriptRoot

Write-Host "Stopping CodeCraft containers (15s timeout)..."
Push-Location $ProjectRoot
$job = Start-Job -ScriptBlock {
    param($root)
    Set-Location $root
    docker compose down --remove-orphans 2>&1
} -ArgumentList $ProjectRoot
Wait-Job $job -Timeout 15 | Out-Null
if ($job.State -eq "Running") {
    Stop-Job $job
    Write-Host "docker compose down timed out - continuing with process kill."
}
Remove-Job $job -Force
Pop-Location

Write-Host "Ending Docker Desktop processes..."
Get-Process -Name "Docker Desktop", "com.docker.backend", "docker" -ErrorAction SilentlyContinue |
    Stop-Process -Force

Write-Host "Shutting down WSL2 (releases RAM)..."
wsl --shutdown

Start-Sleep -Seconds 5

Write-Host "Done. Start Docker Desktop from the Start menu, wait until it is green, then run:"
Write-Host "  cd $ProjectRoot"
Write-Host "  docker compose up --build"
