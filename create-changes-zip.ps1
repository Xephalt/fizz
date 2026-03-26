# Script pour créer un ZIP avec les fichiers modifiés/ajoutés
# Usage: .\create-changes-zip.ps1 [output-zip-name]

param(
    [string]$OutputZip = "changes_$(Get-Date -Format 'yyyyMMdd_HHmmss').zip"
)

# Vérifier qu'on est dans un repo git
if (-not (Test-Path .git)) {
    Write-Error "❌ Pas dans un repo git!"
    exit 1
}

# Récupérer les fichiers modifiés et ajoutés
$modifiedFiles = @()

# Fichiers staged
$stagedFiles = git diff --cached --name-only
if ($stagedFiles) {
    $modifiedFiles += $stagedFiles
}

# Fichiers unstaged
$unstagedFiles = git diff --name-only
if ($unstagedFiles) {
    $modifiedFiles += $unstagedFiles
}

# Fichiers untracked
$untrackedFiles = git ls-files --others --exclude-standard
if ($untrackedFiles) {
    $modifiedFiles += $untrackedFiles
}

# Supprimer les doublons
$modifiedFiles = $modifiedFiles | Select-Object -Unique | Where-Object { $_ }

if ($modifiedFiles.Count -eq 0) {
    Write-Warning "⚠️ Aucun fichier modifié, ajouté ou untracked détecté."
    exit 0
}

Write-Host "📦 Fichiers détectés: $($modifiedFiles.Count)" -ForegroundColor Green

# Créer un dossier temporaire pour préparer la structure
$tempDir = Join-Path ([System.IO.Path]::GetTempPath()) "git-changes-$(Get-Random)"
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null

try {
    # Copier les fichiers en preservant la structure
    foreach ($file in $modifiedFiles) {
        $destPath = Join-Path $tempDir $file
        $destDir = Split-Path $destPath
        
        # Créer les répertoires nécessaires
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        # Copier le fichier
        if (Test-Path $file) {
            Copy-Item -Path $file -Destination $destPath -Force
            Write-Host "  ✓ $file"
        } else {
            Write-Host "  ⚠️ $file (fichier not found, peut être supprimé)"
        }
    }
    
    # Créer le ZIP à partir du dossier temporaire
    if (Test-Path $OutputZip) {
        Remove-Item $OutputZip -Force
    }
    
    # Utiliser Compress-Archive
    Compress-Archive -Path "$tempDir\*" -DestinationPath $OutputZip -Force
    
    Write-Host "`n✅ ZIP créé avec succès!" -ForegroundColor Green
    Write-Host "📁 Fichier: $(Get-Item $OutputZip | Select-Object -ExpandProperty FullName)" -ForegroundColor Cyan
    Write-Host "📊 Taille: $('{0:N2}' -f ((Get-Item $OutputZip).Length / 1KB)) KB" -ForegroundColor Cyan
    Write-Host "📝 Fichiers inclus: $($modifiedFiles.Count)" -ForegroundColor Cyan
    
} finally {
    # Nettoyer le dossier temporaire
    Remove-Item -Path $tempDir -Recurse -Force -ErrorAction SilentlyContinue
}
