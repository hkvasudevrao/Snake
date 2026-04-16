param(
  [Parameter(Mandatory = $true)][string]$ClusterName,
  [Parameter(Mandatory = $true)][string]$KubeconfigPath,
  [Parameter(Mandatory = $true)][string]$K3sImage,
  [Parameter(Mandatory = $true)][string]$NetworkName,
  [Parameter(Mandatory = $true)][string]$InstallIngressNginx,
  [Parameter(Mandatory = $true)][string]$IngressManifest
)

$ErrorActionPreference = "Stop"

function Assert-Command {
  param([string]$Name)

  if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
    throw "Missing required command: $Name"
  }
}

Assert-Command -Name "docker"
Assert-Command -Name "k3d"
Assert-Command -Name "kubectl"

$existingNetwork = docker network ls --format "{{.Name}}" | Where-Object { $_ -eq $NetworkName }
if (-not $existingNetwork) {
  Write-Host "Creating docker network $NetworkName"
  docker network create $NetworkName | Out-Null
}

$clusterExists = $false
$clusterList = k3d cluster list 2>$null
if ($clusterList) {
  $clusterExists = ($clusterList | Select-String -Pattern ("^\s*" + [Regex]::Escape($ClusterName) + "\s")) -ne $null
}

if (-not $clusterExists) {
  Write-Host "Creating k3d cluster $ClusterName"
  k3d cluster create $ClusterName `
    --image $K3sImage `
    --servers 1 `
    --agents 2 `
    --k3s-arg "--disable=traefik@server:0" `
    --port "80:80@loadbalancer" `
    --port "443:443@loadbalancer" `
    --network $NetworkName `
    --wait
} else {
  Write-Host "k3d cluster $ClusterName already exists."
}

$kubeconfigDirectory = Split-Path -Parent $KubeconfigPath
if ($kubeconfigDirectory -and -not (Test-Path $kubeconfigDirectory)) {
  New-Item -ItemType Directory -Force -Path $kubeconfigDirectory | Out-Null
}

k3d kubeconfig get $ClusterName | Set-Content -Path $KubeconfigPath -Encoding ascii
$env:KUBECONFIG = $KubeconfigPath

if ($InstallIngressNginx -eq "true") {
  Write-Host "Installing or updating ingress-nginx from $IngressManifest"
  kubectl apply -f $IngressManifest | Out-Host
  kubectl wait --namespace ingress-nginx --for=condition=Available deployment/ingress-nginx-controller --timeout=300s | Out-Host
}

Write-Host "Cluster is ready. kubeconfig: $KubeconfigPath"
