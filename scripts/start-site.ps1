param(
    [int] $TimeoutSeconds = 120
)

$ErrorActionPreference = 'Stop'

function Test-DockerEngine {
    docker info --format '{{.ServerVersion}}' *> $null
    return $LASTEXITCODE -eq 0
}

if (-not (Test-DockerEngine)) {
    $dockerDesktop = 'C:\Program Files\Docker\Docker\Docker Desktop.exe'

    if (-not (Test-Path -LiteralPath $dockerDesktop)) {
        throw "Docker Desktop tidak ditemukan di $dockerDesktop"
    }

    Write-Host 'Docker engine belum aktif. Menyalakan Docker Desktop...'
    Start-Process -FilePath $dockerDesktop -WindowStyle Hidden

    $deadline = (Get-Date).AddSeconds($TimeoutSeconds)
    do {
        Start-Sleep -Seconds 3
        if (Test-DockerEngine) {
            break
        }
        Write-Host 'Menunggu Docker engine siap...'
    } while ((Get-Date) -lt $deadline)
}

if (-not (Test-DockerEngine)) {
    throw 'Docker engine belum siap. Buka Docker Desktop manual, tunggu sampai status Running, lalu jalankan ulang script ini.'
}

Write-Host 'Docker engine siap. Menyalakan website...'
docker compose up -d
docker compose ps

Write-Host ''
Write-Host 'Website: https://localhost:8443'
Write-Host 'HTTP redirect: http://localhost:8080'
