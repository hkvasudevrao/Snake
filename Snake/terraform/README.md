# Terraform Infrastructure (Phase 0)

This folder provisions a local Kubernetes environment using Terraform + k3d (K3s).

## What it creates
- Docker network for cluster networking.
- k3d-based K3s cluster.
- Optional NGINX Ingress Controller installation.
- kubeconfig file at a configurable location.

## Prerequisites
- Terraform >= 1.6
- Docker
- k3d
- kubectl

## Usage
```powershell
cd terraform
terraform init
terraform apply -auto-approve
```

After apply, test the cluster:
```powershell
kubectl --kubeconfig ..\.kube\config get nodes
```

To tear down:
```powershell
terraform destroy -auto-approve
```

## Variables
- `cluster_name`: k3d cluster name.
- `kubeconfig_path`: kubeconfig output path.
- `k3s_image`: K3s image version.
- `network_name`: Docker network used by k3d.
- `install_ingress_nginx`: Whether to install ingress-nginx.
- `ingress_nginx_manifest`: ingress-nginx manifest URL.
