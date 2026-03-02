param(
    [Parameter(Mandatory = $true)]
    [string]$InputFile,

    [int]$TopChars = 4000,

    [string]$OutputDir = ""
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Get-TextByBestEncoding {
    param(
        [byte[]]$Bytes
    )

    $utf8 = [System.Text.Encoding]::UTF8.GetString($Bytes)
    $gbk = [System.Text.Encoding]::GetEncoding(936).GetString($Bytes)

    $cjkPattern = "[\u3400-\u4DBF\u4E00-\u9FFF\uF900-\uFAFF]"
    $commonHan = "的是了我不在人有这中大来上个们到说和你地出道时年得就那要下以会可也后能子里所然文于着起看学"

    $utf8CjkCount = [regex]::Matches($utf8, $cjkPattern).Count
    $gbkCjkCount = [regex]::Matches($gbk, $cjkPattern).Count

    $utf8CommonHit = 0
    $gbkCommonHit = 0
    foreach ($ch in $commonHan.ToCharArray()) {
        $p = [regex]::Escape([string]$ch)
        $utf8CommonHit += [regex]::Matches($utf8, $p).Count
        $gbkCommonHit += [regex]::Matches($gbk, $p).Count
    }

    # Typical UTF-8 mojibake markers when decoded as GBK.
    $mojibakeMarkers = @("锛", "涓", "鐨", "銆", "鍙", "鎴", "浠", "璇", "鏄", "鍚")
    $utf8Mojibake = 0
    $gbkMojibake = 0
    foreach ($m in $mojibakeMarkers) {
        $p = [regex]::Escape($m)
        $utf8Mojibake += [regex]::Matches($utf8, $p).Count
        $gbkMojibake += [regex]::Matches($gbk, $p).Count
    }

    $utf8Replacement = [regex]::Matches($utf8, "�").Count
    $gbkReplacement = [regex]::Matches($gbk, "�").Count

    $utf8Score = ($utf8CommonHit * 1000) + $utf8CjkCount - ($utf8Mojibake * 200) - ($utf8Replacement * 10)
    $gbkScore = ($gbkCommonHit * 1000) + $gbkCjkCount - ($gbkMojibake * 200) - ($gbkReplacement * 10)

    if ($gbkScore -gt $utf8Score) {
        return @{
            Text = $gbk
            Encoding = "GBK(936)"
            CjkCount = $gbkCjkCount
        }
    }

    return @{
        Text = $utf8
        Encoding = "UTF-8"
        CjkCount = $utf8CjkCount
    }
}

function Add-CharsToSet {
    param(
        [System.Collections.Generic.HashSet[string]]$Set,
        [System.Collections.Generic.List[string]]$Ordered,
        [string]$Text
    )

    foreach ($ch in $Text.ToCharArray()) {
        $s = [string]$ch
        if ($Set.Add($s)) {
            $Ordered.Add($s)
        }
    }
}

if (-not (Test-Path -LiteralPath $InputFile)) {
    throw "Input file not found: $InputFile"
}

$inputFullPath = (Resolve-Path -LiteralPath $InputFile).Path
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

if ([string]::IsNullOrWhiteSpace($OutputDir)) {
    $OutputDir = Join-Path $scriptDir "..\assets\fonts\SourceHanSerifCN\subset"
}

$OutputDir = [System.IO.Path]::GetFullPath($OutputDir)
New-Item -Path $OutputDir -ItemType Directory -Force | Out-Null

$bytes = [System.IO.File]::ReadAllBytes($inputFullPath)
$decoded = Get-TextByBestEncoding -Bytes $bytes
$text = [string]$decoded.Text

$cjkPattern = "[\u3400-\u4DBF\u4E00-\u9FFF\uF900-\uFAFF]"
$matches = [regex]::Matches($text, $cjkPattern)

$freq = @{}
foreach ($m in $matches) {
    $ch = $m.Value
    if ($freq.ContainsKey($ch)) {
        $freq[$ch] += 1
    } else {
        $freq[$ch] = 1
    }
}

$sorted = $freq.GetEnumerator() | Sort-Object -Property @{ Expression = "Value"; Descending = $true }, @{ Expression = "Name"; Descending = $false }
$uniqueCjk = $sorted.Count

if ($TopChars -lt 1) {
    $TopChars = 1
}
if ($TopChars -gt $uniqueCjk -and $uniqueCjk -gt 0) {
    $TopChars = $uniqueCjk
}

$top = @()
if ($uniqueCjk -gt 0) {
    $top = $sorted | Select-Object -First $TopChars
}

# Base ASCII set + commonly used Chinese punctuation.
$ascii = " !`"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_abcdefghijklmnopqrstuvwxyz{|}~"
$hanPunctCodePoints = @(
    0x3001, 0x3002, 0xFF0C, 0xFF1A, 0xFF1B, 0xFF01, 0xFF1F,
    0x300A, 0x300B, 0x300C, 0x300D, 0x300E, 0x300F,
    0x201C, 0x201D, 0x2018, 0x2019, 0xFF08, 0xFF09,
    0x3010, 0x3011, 0x2014, 0x2026, 0x00B7, 0x3008, 0x3009
)

$hanPunct = ""
foreach ($cp in $hanPunctCodePoints) {
    $hanPunct += [char]$cp
}

$set = New-Object "System.Collections.Generic.HashSet[string]"
$ordered = New-Object "System.Collections.Generic.List[string]"

Add-CharsToSet -Set $set -Ordered $ordered -Text $ascii
Add-CharsToSet -Set $set -Ordered $ordered -Text $hanPunct

foreach ($item in $top) {
    $ch = [string]$item.Name
    if ($set.Add($ch)) {
        $ordered.Add($ch)
    }
}

$charsetCore = ($ordered -join "")
$charsetCorePath = Join-Path $OutputDir "charset-core.txt"
[System.IO.File]::WriteAllText($charsetCorePath, $charsetCore, [System.Text.Encoding]::UTF8)

$allCjkChars = ""
if ($uniqueCjk -gt 0) {
    $allCjkChars = (($sorted | ForEach-Object { $_.Name }) -join "")
}
$charsetAllPath = Join-Path $OutputDir "charset-all-cjk.txt"
[System.IO.File]::WriteAllText($charsetAllPath, $allCjkChars, [System.Text.Encoding]::UTF8)

$totalCjkCount = 0
foreach ($item in $sorted) {
    $totalCjkCount += [int]$item.Value
}

$csvPath = Join-Path $OutputDir "char-frequency.csv"
"rank,char,count,ratio,cumulative_ratio" | Out-File -FilePath $csvPath -Encoding UTF8
$acc = 0
$rank = 0
foreach ($item in $sorted) {
    $rank += 1
    $count = [int]$item.Value
    $acc += $count
    $ratio = if ($totalCjkCount -gt 0) { [math]::Round(($count / $totalCjkCount), 8) } else { 0 }
    $cumRatio = if ($totalCjkCount -gt 0) { [math]::Round(($acc / $totalCjkCount), 8) } else { 0 }
    "$rank,$($item.Name),$count,$ratio,$cumRatio" | Out-File -FilePath $csvPath -Encoding UTF8 -Append
}

$topCoverage = 0
if ($totalCjkCount -gt 0 -and $TopChars -gt 0) {
    $topCount = ($sorted | Select-Object -First $TopChars | Measure-Object -Property Value -Sum).Sum
    $topCoverage = [math]::Round(($topCount / $totalCjkCount) * 100, 4)
}

$reportPath = Join-Path $OutputDir "charset-report.txt"
$report = @(
    "InputFile=$inputFullPath",
    "DetectedEncoding=$($decoded.Encoding)",
    "FileBytes=$($bytes.Length)",
    "TotalCjkChars=$totalCjkCount",
    "UniqueCjkChars=$uniqueCjk",
    "TopChars=$TopChars",
    "TopCoveragePercent=$topCoverage",
    "OutputCharsetCore=$charsetCorePath",
    "OutputCharsetAll=$charsetAllPath",
    "OutputFrequencyCsv=$csvPath"
)
$report | Out-File -FilePath $reportPath -Encoding UTF8

Write-Output ("OK")
Write-Output ("Detected encoding: {0}" -f $decoded.Encoding)
Write-Output ("Total CJK chars: {0}" -f $totalCjkCount)
Write-Output ("Unique CJK chars: {0}" -f $uniqueCjk)
Write-Output ("Top {0} coverage: {1}%" -f $TopChars, $topCoverage)
Write-Output ("charset-core: {0}" -f $charsetCorePath)
Write-Output ("report: {0}" -f $reportPath)
