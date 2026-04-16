# Snake Platform Project

This repository includes a full end-to-end implementation of your requested phased architecture:

1. Terraform infra + local K3s cluster (via k3d)
2. Browser Snake game frontend (HTML/CSS/JavaScript)
3. PHP APIs for auth and score handling
4. MySQL persistent schema
5. Docker + Docker Compose packaging
6. GitHub Actions CI
7. Kubernetes deployment manifests
8. Helm chart packaging
9. Argo CD GitOps application
10. Ingress routing (`/` and `/api`)
11. Production simulation settings (probes, limits, PVC, ConfigMap, Secret)
12. Optional monitoring setup (Prometheus + Grafana)

## Project Structure

- `terraform/` - Phase 0
- `frontend/` - Phase 1
- `backend/` - Phase 2
- `db/` - Phase 3
- `docker/` + `docker-compose.yml` - Phase 5
- `.github/workflows/ci.yml` - Phase 6
- `k8s/` - Phase 7, 10, 11
- `helm/snake-stack/` - Phase 8
- `argocd/` - Phase 9
- `monitoring/` - Phase 12

## Quick Start (Local)

### 1) Start with Docker Compose
```powershell
docker compose up --build -d
```

Open: `http://localhost:8080`

### 2) Provision local K3s with Terraform
```powershell
cd terraform
terraform init
terraform apply -auto-approve
cd ..
```

Expected kubeconfig: `.kube/config`

### 3) Deploy Kubernetes manifests
```powershell
kubectl --kubeconfig .kube/config apply -k k8s
kubectl --kubeconfig .kube/config -n snake get pods,svc,ingress
```

Map host locally:
- `127.0.0.1 snake.local`

Open: `http://snake.local`

### 4) Deploy via Helm
```powershell
helm upgrade --install snake helm/snake-stack --namespace snake --create-namespace
```

### 5) Enable Argo CD sync
- Update `argocd/application.yaml` with your repository URL.
- Apply into Argo CD namespace.

## API Endpoints

- `POST /api/register.php`
- `POST /api/login.php`
- `POST /api/submit_score.php`
- `GET /api/leaderboard.php?limit=10`

Utility:
- `GET /api/healthz.php`
- `GET /api/metrics.php`

## Security Notes

- Passwords are hashed with `password_hash`.
- Token auth uses HMAC signed JWT-style tokens.
- Replace default secrets before production use.

## CI/CD Notes

GitHub Actions pipeline:
- Lints PHP and JavaScript syntax
- Validates Terraform formatting
- Builds and pushes frontend/backend images to GHCR on `main`
