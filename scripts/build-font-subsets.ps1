param(
    [Parameter(Mandatory = $true)]
    [string]$SourceFont,

    [Parameter(Mandatory = $true)]
    [string]$CoreCharsetFile,

    [Parameter(Mandatory = $true)]
    [string]$AllCharsetFile,

    [string]$OutputDir = "",
    [string]$PythonCommand = "python",
    [int]$InstanceWeight = 400,
    [string]$CoreFontFamily = "Source Han Serif Core",
    [string]$FallbackFontFamily = "Source Han Serif Fallback",
    [string]$StaticFontWeight = "400"
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

function Is-CjkCodePoint {
    param(
        [int]$CodePoint
    )

    return (
        ($CodePoint -ge 0x3400 -and $CodePoint -le 0x4DBF) -or
        ($CodePoint -ge 0x4E00 -and $CodePoint -le 0x9FFF) -or
        ($CodePoint -ge 0xF900 -and $CodePoint -le 0xFAFF)
    )
}

function Get-UniqueChars {
    param(
        [string]$Text
    )

    $set = New-Object "System.Collections.Generic.HashSet[string]"
    $ordered = New-Object "System.Collections.Generic.List[string]"

    foreach ($ch in $Text.ToCharArray()) {
        $s = [string]$ch
        if ($set.Add($s)) {
            $ordered.Add($s)
        }
    }

    return ,$ordered.ToArray()
}

function Convert-CharsToUnicodeRange {
    param(
        [string[]]$Chars
    )

    if (-not $Chars -or $Chars.Count -eq 0) {
        return ""
    }

    $codeSet = New-Object "System.Collections.Generic.HashSet[int]"
    foreach ($ch in $Chars) {
        if ([string]::IsNullOrEmpty($ch)) {
            continue
        }
        [void]$codeSet.Add([int][char]$ch)
    }

    if ($codeSet.Count -eq 0) {
        return ""
    }

    $codes = @($codeSet) | Sort-Object
    $ranges = New-Object "System.Collections.Generic.List[string]"

    $start = $codes[0]
    $prev = $codes[0]

    for ($i = 1; $i -lt $codes.Count; $i++) {
        $curr = [int]$codes[$i]
        if ($curr -eq ($prev + 1)) {
            $prev = $curr
            continue
        }

        if ($start -eq $prev) {
            $ranges.Add(("U+{0}" -f $start.ToString("X")))
        } else {
            $ranges.Add(("U+{0}-{1}" -f @($start.ToString("X"), $prev.ToString("X"))))
        }

        $start = $curr
        $prev = $curr
    }

    if ($start -eq $prev) {
        $ranges.Add(("U+{0}" -f $start.ToString("X")))
    } else {
        $ranges.Add(("U+{0}-{1}" -f @($start.ToString("X"), $prev.ToString("X"))))
    }

    return ($ranges -join ",")
}

function Invoke-FontSubset {
    param(
        [string]$PythonCommand,
        [string]$SourceFontPath,
        [string]$OutputFilePath,
        [string]$TextFilePath,
        [string[]]$CommonArgs
    )

    $args = @(
        "-m",
        "fontTools.subset",
        $SourceFontPath,
        "--output-file=$OutputFilePath",
        "--text-file=$TextFilePath"
    ) + $CommonArgs

    & $PythonCommand @args
    if ($LASTEXITCODE -ne 0) {
        throw "Subset build failed ($OutputFilePath) with exit code $LASTEXITCODE"
    }
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

$subsetSourceFontPath = $sourceFontPath
$fontWeightDescriptor = $StaticFontWeight
$instanceFontPath = ""
if ($InstanceWeight -gt 0) {
    $instanceFontPath = Join-Path $outputDirFull ("source-wght-{0}.ttf" -f $InstanceWeight)
    $instanceArgs = @(
        "-m",
        "fontTools.varLib.instancer",
        $sourceFontPath,
        ("wght={0}" -f $InstanceWeight),
        "--output",
        $instanceFontPath
    )
    & $PythonCommand @instanceArgs
    if ($LASTEXITCODE -ne 0) {
        throw "Font instancer failed with exit code $LASTEXITCODE"
    }
    if (-not (Test-Path -LiteralPath $instanceFontPath)) {
        throw "Instanced font not found: $instanceFontPath"
    }
    $subsetSourceFontPath = $instanceFontPath
    $fontWeightDescriptor = [string]$InstanceWeight
}

$coreUniqueChars = Get-UniqueChars -Text $coreText
$coreCjkChars = New-Object "System.Collections.Generic.List[string]"
$coreMiscChars = New-Object "System.Collections.Generic.List[string]"
foreach ($ch in $coreUniqueChars) {
    if ([string]::IsNullOrEmpty($ch)) {
        continue
    }
    $cp = [int][char]$ch
    if (Is-CjkCodePoint -CodePoint $cp) {
        $coreCjkChars.Add($ch)
    } else {
        $coreMiscChars.Add($ch)
    }
}

$coreCjkText = $coreCjkChars -join ""
$coreMiscText = $coreMiscChars -join ""

$coreCjkCharsetPath = Join-Path $outputDirFull "charset-core-cjk.txt"
$coreMiscCharsetPath = Join-Path $outputDirFull "charset-core-misc.txt"
[System.IO.File]::WriteAllText($coreCjkCharsetPath, $coreCjkText, $utf8NoBom)
[System.IO.File]::WriteAllText($coreMiscCharsetPath, $coreMiscText, $utf8NoBom)

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

$coreCjkWoff2Path = Join-Path $outputDirFull "core-cjk.woff2"
$coreMiscWoff2Path = Join-Path $outputDirFull "core-misc.woff2"
$fallbackWoff2Path = Join-Path $outputDirFull "fallback.woff2"

if ($coreCjkChars.Count -gt 0) {
    Invoke-FontSubset `
        -PythonCommand $PythonCommand `
        -SourceFontPath $subsetSourceFontPath `
        -OutputFilePath $coreCjkWoff2Path `
        -TextFilePath $coreCjkCharsetPath `
        -CommonArgs $commonSubsetArgs
}

if ($coreMiscChars.Count -gt 0) {
    Invoke-FontSubset `
        -PythonCommand $PythonCommand `
        -SourceFontPath $subsetSourceFontPath `
        -OutputFilePath $coreMiscWoff2Path `
        -TextFilePath $coreMiscCharsetPath `
        -CommonArgs $commonSubsetArgs
}

if ($fallbackText.Length -gt 0) {
    Invoke-FontSubset `
        -PythonCommand $PythonCommand `
        -SourceFontPath $subsetSourceFontPath `
        -OutputFilePath $fallbackWoff2Path `
        -TextFilePath $fallbackCharsetPath `
        -CommonArgs $commonSubsetArgs
}

$subsetCssPath = Join-Path $outputDirFull "subset-font.css"
[System.Collections.Generic.List[string]]$cssLines = @()
if ($coreCjkChars.Count -gt 0) {
    $cssLines.Add("@font-face {")
    $cssLines.Add(("    font-family: `"{0}`";" -f $CoreFontFamily))
    $cssLines.Add("    src: url(`"./core-cjk.woff2`") format(`"woff2`");")
    $cssLines.Add("    font-style: normal;")
    $cssLines.Add("    font-weight: $fontWeightDescriptor;")
    $cssLines.Add("    font-display: swap;")
    $cssLines.Add("    unicode-range: $(Convert-CharsToUnicodeRange -Chars $coreCjkChars.ToArray());")
    $cssLines.Add("}")
    $cssLines.Add("")
}

if ($coreMiscChars.Count -gt 0) {
    $cssLines.Add("@font-face {")
    $cssLines.Add(("    font-family: `"{0}`";" -f $CoreFontFamily))
    $cssLines.Add("    src: url(`"./core-misc.woff2`") format(`"woff2`");")
    $cssLines.Add("    font-style: normal;")
    $cssLines.Add("    font-weight: $fontWeightDescriptor;")
    $cssLines.Add("    font-display: swap;")
    $cssLines.Add("    unicode-range: $(Convert-CharsToUnicodeRange -Chars $coreMiscChars.ToArray());")
    $cssLines.Add("}")
    $cssLines.Add("")
}

$fallbackUniqueChars = Get-UniqueChars -Text $fallbackText
if ($fallbackUniqueChars.Count -gt 0) {
    $cssLines.Add("@font-face {")
    $cssLines.Add(("    font-family: `"{0}`";" -f $FallbackFontFamily))
    $cssLines.Add("    src: url(`"./fallback.woff2`") format(`"woff2`");")
    $cssLines.Add("    font-style: normal;")
    $cssLines.Add("    font-weight: $fontWeightDescriptor;")
    $cssLines.Add("    font-display: swap;")
    $cssLines.Add("    unicode-range: $(Convert-CharsToUnicodeRange -Chars $fallbackUniqueChars);")
    $cssLines.Add("}")
}

$subsetCss = ($cssLines -join [Environment]::NewLine)
[System.IO.File]::WriteAllText($subsetCssPath, $subsetCss, $utf8NoBom)

$coreCjkBytes = if (Test-Path -LiteralPath $coreCjkWoff2Path) { (Get-Item -LiteralPath $coreCjkWoff2Path).Length } else { 0 }
$coreMiscBytes = if (Test-Path -LiteralPath $coreMiscWoff2Path) { (Get-Item -LiteralPath $coreMiscWoff2Path).Length } else { 0 }
$fallbackBytes = if (Test-Path -LiteralPath $fallbackWoff2Path) { (Get-Item -LiteralPath $fallbackWoff2Path).Length } else { 0 }

$reportPath = Join-Path $outputDirFull "subset-build-report.txt"
$reportLines = @(
    "SourceFont=$sourceFontPath",
    "SubsetSourceFont=$subsetSourceFontPath",
    "InstanceWeight=$InstanceWeight",
    "CoreCharset=$coreCharsetPath",
    "CoreCjkCharset=$coreCjkCharsetPath",
    "CoreMiscCharset=$coreMiscCharsetPath",
    "AllCharset=$allCharsetPath",
    "FallbackCharset=$fallbackCharsetPath",
    "CoreChars=$($coreText.Length)",
    "CoreCjkChars=$($coreCjkText.Length)",
    "CoreMiscChars=$($coreMiscText.Length)",
    "FallbackChars=$($fallbackText.Length)",
    "CoreCjkWoff2=$coreCjkWoff2Path",
    "CoreCjkWoff2Bytes=$coreCjkBytes",
    "CoreMiscWoff2=$coreMiscWoff2Path",
    "CoreMiscWoff2Bytes=$coreMiscBytes",
    "FallbackWoff2=$fallbackWoff2Path",
    "FallbackWoff2Bytes=$fallbackBytes",
    "TotalSubsetBytes=$($coreCjkBytes + $coreMiscBytes + $fallbackBytes)",
    "SubsetCss=$subsetCssPath"
)
[System.IO.File]::WriteAllLines($reportPath, $reportLines, $utf8NoBom)

if (-not [string]::IsNullOrWhiteSpace($instanceFontPath) -and (Test-Path -LiteralPath $instanceFontPath)) {
    try {
        Remove-Item -LiteralPath $instanceFontPath -Force -ErrorAction Stop
    } catch {
        # Keep build successful even if temp instance cleanup fails.
    }
}

Write-Output "OK"
Write-Output "Core CJK woff2: $coreCjkWoff2Path ($coreCjkBytes bytes)"
Write-Output "Core Misc woff2: $coreMiscWoff2Path ($coreMiscBytes bytes)"
Write-Output "Fallback woff2: $fallbackWoff2Path ($fallbackBytes bytes)"
Write-Output "Subset css: $subsetCssPath"
Write-Output "Report: $reportPath"
