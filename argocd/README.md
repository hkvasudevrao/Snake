# Argo CD GitOps (Phase 9)

1. Install Argo CD in your cluster.
2. Update `repoURL` in `application.yaml` to your git repository.
3. Apply:
```powershell
kubectl apply -n argocd -f argocd/application.yaml
```
4. Argo CD will auto-sync the Helm chart from `helm/snake-stack`.
