param(
    [Parameter(Mandatory = $true)]
    [string]$Version
)

# Script para criar o ZIP de distribuição do Zimny Admin
# Uso: .\build-release.ps1 -Version 3.1.1
#
# Pré-requisitos:
#   - git + gh (GitHub CLI) instalados e autenticados
#   - Executar na pasta raiz do plugin (wordpress/plugins/zimny-admin/)

$ErrorActionPreference = "Stop"
$Root = Resolve-Path "."
$MainFile = Join-Path $Root "zimny-admin.php"

# 1. Valida a versão
if ($Version -notmatch '^\d+\.\d+\.\d+$') {
    Write-Error "Versão inválida. Use o formato X.Y.Z (ex: 3.1.1)"
    exit 1
}

$Tag = "v$Version"

# 2. Atualiza a versão no header do plugin
Write-Host "==> Atualizando versão para $Version no header do plugin..." -ForegroundColor Cyan
(Get-Content $MainFile) -replace '(?<=Version:\s+)\d+\.\d+\.\d+', $Version | Set-Content $MainFile

# 3. Commit da alteração de versão
Write-Host "==> Commit da alteração de versão..." -ForegroundColor Cyan
git add $MainFile
git commit -m "chore: bump version to $Version"

# 4. Cria a tag
Write-Host "==> Criando tag $Tag..." -ForegroundColor Cyan
git tag $Tag

# 5. Push do commit e da tag
Write-Host "==> Fazendo push..." -ForegroundColor Cyan
git push origin main
git push origin $Tag

# 6. Cria o ZIP de distribuição (apenas a pasta do plugin, sem .git)
Write-Host "==> Criando ZIP de distribuição..." -ForegroundColor Cyan
$ZipName = "zimny-admin-$Version.zip"
$ZipPath = Join-Path $Env:TEMP $ZipName

# Remove o ZIP anterior se existir
if (Test-Path $ZipPath) { Remove-Item $ZipPath -Force }

# Cria o ZIP com o conteúdo da pasta (excluindo .git)
$stage = Join-Path $Env:TEMP "zimny-admin-stage"
if (Test-Path $stage) { Remove-Item $stage -Recurse -Force }
New-Item -ItemType Directory -Path $stage | Out-Null

# Copia tudo exceto .git
Get-ChildItem -Path $Root -Exclude '.git' | ForEach-Object {
    Copy-Item $_.FullName $stage -Recurse
}

Compress-Archive -Path "$stage\*" -DestinationPath $ZipPath -Force
Remove-Item $stage -Recurse -Force

# 7. Cria a Release no GitHub
Write-Host "==> Criando Release $Tag no GitHub..." -ForegroundColor Cyan
gh release create $Tag $ZipPath --repo chr-z/zimny-admin --title "Zimny Admin v$Version" --notes ""

# 8. Limpa o ZIP temporário
Remove-Item $ZipPath -Force

Write-Host "==> Release $Tag publicada com sucesso!" -ForegroundColor Green
Write-Host "URL: https://github.com/chr-z/zimny-admin/releases/tag/$Tag" -ForegroundColor Green