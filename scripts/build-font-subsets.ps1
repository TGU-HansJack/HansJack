param(
    [Parameter(Mandatory = $true)]
    [string]$SourceFont,

    [Parameter(Mandatory = $true)]
    [string]$CoreCharsetFile,

    [Parameter(Mandatory = $true)]
    [string]$AllCharsetFile,

    [string]$OutputDir = "",
    [string]$PythonCommand = "python"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Resolve-ExistingPath {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Path
    )

    if (-not (Test-Path -LiteralPath $Path)) {
        throw "Path not found: $Path"
    }

    return (Resolve-Path -LiteralPath $Path).Path
}

$sourceFontPath = Resolve-ExistingPath -Path $SourceFont
$coreCharsetPath = Resolve-ExistingPath -Path $CoreCharsetFile
$allCharsetPath = Resolve-ExistingPath -Path $AllCharsetFile

if ([string]::IsNullOrWhiteSpace($OutputDir)) {
    $OutputDir = Split-Path -Parent $coreCharsetPath
}

$outputDirFull = [System.IO.Path]::GetFullPath($OutputDir)
New-Item -ItemType Directory -Path $outputDirFull -Force | Out-Null

$utf8 = [System.Text.Encoding]::UTF8
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

$coreText = [System.IO.File]::ReadAllText($coreCharsetPath, $utf8)
$allText = [System.IO.File]::ReadAllText($allCharsetPath, $utf8)

$coreSet = New-Object "System.Collections.Generic.HashSet[string]"
foreach ($ch in $coreText.ToCharArray()) {
    [void]$coreSet.Add([string]$ch)
}

$fallbackChars = New-Object "System.Collections.Generic.List[string]"
foreach ($ch in $allText.ToCharArray()) {
    $s = [string]$ch
    if (-not $coreSet.Contains($s)) {
        $fallbackChars.Add($s)
    }
}

$fallbackText = $fallbackChars -join ""
$fallbackCharsetPath = Join-Path $outputDirFull "charset-fallback.txt"
[System.IO.File]::WriteAllText($fallbackCharsetPath, $fallbackText, $utf8NoBom)

$commonSubsetArgs = @(
    "--flavor=woff2",
    "--layout-features=*",
    "--name-IDs=*",
    "--name-legacy",
    "--name-languages=*",
    "--notdef-glyph",
    "--notdef-outline",
    "--recommended-glyphs",
    "--symbol-cmap",
    "--legacy-cmap",
    "--no-hinting"
)

$coreWoff2Path = Join-Path $outputDirFull "core.woff2"
$fallbackWoff2Path = Join-Path $outputDirFull "fallback.woff2"

$coreArgs = @(
    "-m",
    "fontTools.subset",
    $sourceFontPath,
    "--output-file=$coreWoff2Path",
    "--text-file=$coreCharsetPath"
) + $commonSubsetArgs

& $PythonCommand @coreArgs
if ($LASTEXITCODE -ne 0) {
    throw "Core subset build failed with exit code $LASTEXITCODE"
}

$fallbackArgs = @(
    "-m",
    "fontTools.subset",
    $sourceFontPath,
    "--output-file=$fallbackWoff2Path",
    "--text-file=$fallbackCharsetPath"
) + $commonSubsetArgs

& $PythonCommand @fallbackArgs
if ($LASTEXITCODE -ne 0) {
    throw "Fallback subset build failed with exit code $LASTEXITCODE"
}

$subsetCssPath = Join-Path $outputDirFull "subset-font.css"
$subsetCss = @'
@font-face {
    font-family: "HJ Source Han Serif Core";
    src: url("./core.woff2") format("woff2");
    font-style: normal;
    font-weight: 250 900;
    font-display: swap;
}

@font-face {
    font-family: "HJ Source Han Serif Fallback";
    src: url("./fallback.woff2") format("woff2");
    font-style: normal;
    font-weight: 250 900;
    font-display: swap;
}
'@
[System.IO.File]::WriteAllText($subsetCssPath, $subsetCss, $utf8NoBom)

$coreBytes = (Get-Item -LiteralPath $coreWoff2Path).Length
$fallbackBytes = (Get-Item -LiteralPath $fallbackWoff2Path).Length

$reportPath = Join-Path $outputDirFull "subset-build-report.txt"
$reportLines = @(
    "SourceFont=$sourceFontPath",
    "CoreCharset=$coreCharsetPath",
    "AllCharset=$allCharsetPath",
    "FallbackCharset=$fallbackCharsetPath",
    "CoreChars=$($coreText.Length)",
    "FallbackChars=$($fallbackText.Length)",
    "CoreWoff2=$coreWoff2Path",
    "CoreWoff2Bytes=$coreBytes",
    "FallbackWoff2=$fallbackWoff2Path",
    "FallbackWoff2Bytes=$fallbackBytes",
    "TotalSubsetBytes=$($coreBytes + $fallbackBytes)",
    "SubsetCss=$subsetCssPath"
)
[System.IO.File]::WriteAllLines($reportPath, $reportLines, $utf8NoBom)

Write-Output "OK"
Write-Output "Core woff2: $coreWoff2Path ($coreBytes bytes)"
Write-Output "Fallback woff2: $fallbackWoff2Path ($fallbackBytes bytes)"
Write-Output "Subset css: $subsetCssPath"
Write-Output "Report: $reportPath"
