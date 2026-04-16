param(
  [Parameter(Mandatory = $true)][string]$ClusterName,
  [Parameter(Mandatory = $true)][string]$NetworkName
)

$ErrorActionPreference = "Continue"

$clusterExists = $false
$clusterList = k3d cluster list 2>$null
if ($clusterList) {
  $clusterExists = ($clusterList | Select-String -Pattern ("^\s*" + [Regex]::Escape($ClusterName) + "\s")) -ne $null
}

if ($clusterExists) {
  Write-Host "Deleting k3d cluster $ClusterName"
  k3d cluster delete $ClusterName | Out-Host
} else {
  Write-Host "Cluster $ClusterName does not exist."
}

$networkExists = docker network ls --format "{{.Name}}" | Where-Object { $_ -eq $NetworkName }
if ($networkExists) {
  try {
    docker network rm $NetworkName | Out-Null
    Write-Host "Removed docker network $NetworkName"
  } catch {
    Write-Host "Could not remove docker network $NetworkName (likely still in use)."
  }
}
