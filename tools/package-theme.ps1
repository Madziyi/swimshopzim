[CmdletBinding()]
param(
    [string]$OutputPath = 'dist\swimshop-zimbabwe.zip'
)

$ErrorActionPreference = 'Stop'
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$themeRoot = Join-Path $repoRoot 'theme\swimshop-zimbabwe'
$resolvedOutput = Join-Path $repoRoot $OutputPath

if (-not (Test-Path -LiteralPath $themeRoot -PathType Container)) {
    throw "Theme source was not found: $themeRoot"
}

New-Item -ItemType Directory -Force -Path (Split-Path -Parent $resolvedOutput) | Out-Null
if (Test-Path -LiteralPath $resolvedOutput) {
    Remove-Item -LiteralPath $resolvedOutput -Force
}

Compress-Archive -Path $themeRoot -DestinationPath $resolvedOutput -CompressionLevel Optimal
Write-Output "Packaged $themeRoot as $resolvedOutput"

