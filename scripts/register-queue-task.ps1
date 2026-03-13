$ErrorActionPreference = "Stop"

param(
    [string]$TaskName = "LaravelQueueWorker",
    [string]$Queue = "database",
    [int]$Sleep = 3,
    [int]$Tries = 3,
    [int]$Timeout = 120,
    [ValidateSet("Logon", "Startup")]
    [string]$Trigger = "Logon",
    [switch]$RunAsSystem
)

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
$xmlPath = Join-Path $projectRoot "deploy\\windows\\laravel-queue-task.xml"
$workerPath = Join-Path $projectRoot "scripts\\queue-worker.ps1"

if (-not (Test-Path $xmlPath)) {
    throw "Task XML not found: $xmlPath"
}

if (-not (Test-Path $workerPath)) {
    throw "Worker script not found: $workerPath"
}

function Escape-XmlText([string]$value) {
    return $value.Replace("&", "&amp;").Replace("<", "&lt;").Replace(">", "&gt;")
}

$workerArgs = "-NoProfile -ExecutionPolicy Bypass -File `"$workerPath`" -Queue $Queue -Sleep $Sleep -Tries $Tries -Timeout $Timeout"
$workerArgsXml = Escape-XmlText $workerArgs

$triggerBlock = switch ($Trigger) {
    "Startup" {
        @"
    <BootTrigger>
      <Enabled>true</Enabled>
    </BootTrigger>
"@
    }
    default {
        @"
    <LogonTrigger>
      <Enabled>true</Enabled>
    </LogonTrigger>
"@
    }
}

$principalBlock = if ($RunAsSystem) {
    @"
    <Principal id="Author">
      <UserId>S-1-5-18</UserId>
      <LogonType>ServiceAccount</LogonType>
      <RunLevel>HighestAvailable</RunLevel>
    </Principal>
"@
} else {
    @"
    <Principal id="Author">
      <RunLevel>HighestAvailable</RunLevel>
    </Principal>
"@
}

$xml = Get-Content -Path $xmlPath -Raw
$xml = $xml.Replace("__WORKER_ARGS__", $workerArgsXml)
$xml = $xml.Replace("__TRIGGER_BLOCK__", $triggerBlock.TrimEnd())
$xml = $xml.Replace("__PRINCIPAL_BLOCK__", $principalBlock.TrimEnd())

$tempXml = Join-Path $env:TEMP "laravel-queue-task.xml"
Set-Content -Path $tempXml -Value $xml -Encoding Unicode

Write-Host "Registering Scheduled Task: $TaskName"
schtasks /Create /TN $TaskName /XML $tempXml /F | Out-Null

Write-Host "Done. You can start it with:"
Write-Host "schtasks /Run /TN $TaskName"
