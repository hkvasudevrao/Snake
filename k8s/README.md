# Kubernetes Manifests (Phase 7, 10, 11)

## Deploy
```powershell
kubectl apply -k k8s
```

## Verify
```powershell
kubectl -n snake get pods,svc,ingress
```

## Local host mapping
Add this entry to your hosts file:
- `127.0.0.1 snake.local`

Then open:
- `http://snake.local/`

API routes are exposed under:
- `http://snake.local/api/*`
