# Monitoring (Phase 12 - Optional)

Install Prometheus + Grafana:
```powershell
helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
helm repo update
helm upgrade --install kube-prometheus-stack prometheus-community/kube-prometheus-stack \
  --namespace monitoring --create-namespace \
  -f monitoring/kube-prometheus-stack-values.yaml
```

Scrape backend metrics:
```powershell
kubectl apply -f monitoring/snake-servicemonitor.yaml
```

Host mapping example:
- `127.0.0.1 grafana.snake.local`
