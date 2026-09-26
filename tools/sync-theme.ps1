[CmdletBinding()]
param(
    [string]$SitePath = 'C:\Users\stanm\Local Sites\swimshop-zimbabwe\app\public',
    [switch]$AllowExisting
)

$ErrorActionPreference = 'Stop'
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$source = Join-Path $repoRoot 'theme\swimshop-zimbabwe'
$destination = Join-Path $SitePath 'wp-content\themes\swimshop-zimbabwe'

if (-not (Test-Path -LiteralPath $source -PathType Container)) {
    throw "Theme source was not found: $source"
}

if (-not (Test-Path -LiteralPath $SitePath -PathType Container)) {
    throw "Local site public directory was not found: $SitePath"
}

if ((Test-Path -LiteralPath $destination) -and -not $AllowExisting) {
    throw "Destination already exists. Inspect it first, then rerun with -AllowExisting: $destination"
}

New-Item -ItemType Directory -Force -Path (Split-Path -Parent $destination) | Out-Null
Copy-Item -LiteralPath $source -Destination (Split-Path -Parent $destination) -Recurse -Force
Write-Output "Synced theme source to $destination without deleting destination-only files."

