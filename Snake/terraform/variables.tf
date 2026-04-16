variable "cluster_name" {
  type        = string
  description = "k3d cluster name"
  default     = "snake-k3s"
}

variable "kubeconfig_path" {
  type        = string
  description = "Where kubeconfig should be written"
  default     = "${path.root}/../.kube/config"
}

variable "k3s_image" {
  type        = string
  description = "k3s image used by k3d"
  default     = "rancher/k3s:v1.30.6-k3s1"
}

variable "network_name" {
  type        = string
  description = "Docker network name used by k3d"
  default     = "snake-k3d-net"
}

variable "install_ingress_nginx" {
  type        = bool
  description = "Install NGINX ingress controller after cluster creation"
  default     = true
}

variable "ingress_nginx_manifest" {
  type        = string
  description = "Ingress NGINX manifest URL"
  default     = "https://raw.githubusercontent.com/kubernetes/ingress-nginx/controller-v1.11.3/deploy/static/provider/cloud/deploy.yaml"
}
