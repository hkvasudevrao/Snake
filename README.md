# Snake DevOps Platform

Full-stack Snake game packaged and delivered as a DevOps project with CI/CD, Kubernetes, Helm, GitOps, and monitoring.

## Overview

This repository contains a browser-based Snake game frontend, a PHP backend for authentication and leaderboard APIs, and a MySQL database. The application is containerized with Docker, deployed to Kubernetes, packaged with Helm, synchronized with Argo CD, and monitored with Prometheus and Grafana.

The project is designed to demonstrate a realistic DevOps workflow rather than just a local app run:

- Docker Compose for local development
- GitHub Actions CI for linting, building, and publishing images
- GHCR image registry integration
- Kubernetes manifests for direct deployment
- Helm chart packaging
- Argo CD GitOps deployment
- Prometheus metrics and Grafana dashboards
- Ingress-based routing with custom local domains

## Architecture

```text
Browser
  -> Ingress (NGINX)
    -> Frontend (Nginx static app)
    -> Backend (PHP + Apache)
      -> MySQL

Git push
  -> GitHub Actions
    -> Build frontend/backend images
    -> Push images to GHCR

Git repo
  -> Argo CD
    -> Sync Helm chart to Kubernetes

Backend metrics
  -> Prometheus
    -> Grafana
```

## Tech Stack

- Frontend: HTML, CSS, JavaScript
- Backend: PHP 8.3, Apache
- Database: MySQL 8.4
- Containers: Docker, Docker Compose
- CI/CD: GitHub Actions, GHCR
- Orchestration: Kubernetes, Minikube
- Packaging: Helm
- GitOps: Argo CD
- Monitoring: Prometheus, Grafana

## Features

- User registration and login
- Score submission and leaderboard API
- Health endpoint for Kubernetes probes
- Metrics endpoint for Prometheus scraping
- Dockerized frontend and backend images
- CI workflow with linting and image publishing
- Kubernetes manifests with probes, limits, config, secrets, and ingress
- Helm chart for repeatable deployment
- Argo CD application manifest for GitOps sync
- Grafana-ready monitoring stack

## Repository Structure

- `frontend/` - browser game UI
- `backend/` - PHP API and metrics endpoints
- `db/` - database bootstrap SQL
- `docker/` - frontend and backend Dockerfiles plus Nginx config
- `docker-compose.yml` - local multi-container stack
- `.github/workflows/ci.yml` - GitHub Actions pipeline
- `k8s/` - raw Kubernetes manifests
- `helm/snake-stack/` - Helm chart
- `argocd/` - Argo CD application manifest
- `monitoring/` - Prometheus and Grafana setup
- `terraform/` - optional local Terraform helpers for k3d-based experimentation

## Application Endpoints

- `POST /api/register.php`
- `POST /api/login.php`
- `POST /api/submit_score.php`
- `GET /api/leaderboard.php?limit=10`
- `GET /api/healthz.php`
- `GET /api/metrics.php`

## CI/CD Flow

On every push to `main`, GitHub Actions:

1. Validates PHP syntax
2. Validates JavaScript syntax
3. Checks Terraform formatting
4. Builds frontend and backend images
5. Pushes images to GHCR

Published images:

- `ghcr.io/hkvasudevrao/snake-frontend`
- `ghcr.io/hkvasudevrao/snake-backend`

## Local Development

Start the app locally with Docker Compose:

```powershell
docker compose up --build -d
```

Open:

- `http://localhost:8080`

## Kubernetes Deployment

This project was validated primarily with Minikube.

### Prerequisites

- Docker Desktop
- `kubectl`
- `helm`
- `minikube`

### Start Minikube

```powershell
minikube start
minikube addons enable ingress
```

In a separate terminal:

```powershell
minikube tunnel
```

### Create Namespace and Registry Secret

```powershell
kubectl create namespace snake
kubectl create secret docker-registry ghcr-creds `
  --namespace snake `
  --docker-server=ghcr.io `
  --docker-username=<github-username> `
  --docker-password="<ghcr-token>"
```

### Deploy Raw Kubernetes Manifests

```powershell
kubectl apply -k k8s
kubectl get pods -n snake
```

Add this to your hosts file:

```text
127.0.0.1 snake.local
```

Open:

- `http://snake.local`
- `http://snake.local/api`

## Helm Deployment

Install the chart:

```powershell
helm upgrade --install snake ./helm/snake-stack --namespace snake --create-namespace
```

Verify:

```powershell
helm list -n snake
kubectl get all -n snake
```

## Argo CD GitOps

Install Argo CD:

```powershell
kubectl create namespace argocd
kubectl apply -n argocd -f https://raw.githubusercontent.com/argoproj/argo-cd/stable/manifests/install.yaml
```

Apply the application manifest:

```powershell
kubectl apply -f argocd/application.yaml
kubectl get application -n argocd
```

Expected healthy state:

- `Sync Status: Synced`
- `Health Status: Healthy`

## Monitoring

Install the monitoring stack:

```powershell
helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
helm repo update
helm upgrade --install kube-prometheus-stack prometheus-community/kube-prometheus-stack `
  --namespace monitoring --create-namespace `
  -f monitoring/kube-prometheus-stack-values.yaml
kubectl apply -f monitoring/snake-servicemonitor.yaml
```

Add this to your hosts file:

```text
127.0.0.1 grafana.snake.local
```

Grafana:

- `http://grafana.snake.local`

Example metrics exposed by the backend:

- `snake_db_up`
- `snake_users_total`
- `snake_scores_total`
- `snake_top_score`

## Security Notes

- Passwords are hashed with `password_hash()`
- API auth uses HMAC-signed token logic
- Secrets in local manifests are development defaults and should be replaced for real environments
- `monitoring/kube-prometheus-stack-values.yaml` currently contains a fixed Grafana admin password for local/demo use and should be overridden outside local testing

## Important Caveats

- The current Kubernetes and Helm manifests use the `latest` image tag. This is fine for local demos, but true GitOps image promotion should use immutable tags such as the Git commit SHA.
- Running the full stack on a small Minikube node can exhaust local resources. For stable local operation with Argo CD, Prometheus, and Grafana together, allocate more CPU and memory to Minikube.
- The Terraform folder in this repo is for optional local k3d experiments and is not the primary deployment path documented here.

## Why This Project Matters

This project demonstrates the full path from application code to containerization, CI, registry publishing, Kubernetes deployment, GitOps synchronization, and observability. It is structured as a portfolio-ready DevOps project rather than a single-service demo.

## Screens You Can Show In A Demo

- GitHub Actions workflow success
- GHCR packages for frontend and backend
- `kubectl get pods -n snake`
- `helm list -n snake`
- `kubectl get application -n argocd`
- Grafana login and dashboard/Explore view
- `http://snake.local` and `http://snake.local/api/healthz.php`
