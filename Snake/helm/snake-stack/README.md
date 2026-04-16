# Helm Packaging (Phase 8)

Install chart:
```powershell
helm upgrade --install snake helm/snake-stack --namespace snake --create-namespace
```

Override images:
```powershell
helm upgrade --install snake helm/snake-stack \
  --namespace snake --create-namespace \
  --set frontend.image.repository=ghcr.io/<org>/snake-frontend \
  --set backend.image.repository=ghcr.io/<org>/snake-backend
```
