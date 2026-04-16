terraform {
  required_version = ">= 1.6.0"

  required_providers {
    local = {
      source  = "hashicorp/local"
      version = "~> 2.5"
    }
    null = {
      source  = "hashicorp/null"
      version = "~> 3.2"
    }
  }
}

resource "null_resource" "k3s_cluster" {
  triggers = {
    cluster_name           = var.cluster_name
    kubeconfig_path        = var.kubeconfig_path
    k3s_image              = var.k3s_image
    network_name           = var.network_name
    install_ingress_nginx  = tostring(var.install_ingress_nginx)
    ingress_nginx_manifest = var.ingress_nginx_manifest
  }

  provisioner "local-exec" {
    interpreter = ["PowerShell", "-NoProfile", "-ExecutionPolicy", "Bypass", "-Command"]
    command     = "${path.module}/scripts/create-k3d.ps1 -ClusterName '${var.cluster_name}' -KubeconfigPath '${var.kubeconfig_path}' -K3sImage '${var.k3s_image}' -NetworkName '${var.network_name}' -InstallIngressNginx '${var.install_ingress_nginx}' -IngressManifest '${var.ingress_nginx_manifest}'"
  }

  provisioner "local-exec" {
    when        = destroy
    interpreter = ["PowerShell", "-NoProfile", "-ExecutionPolicy", "Bypass", "-Command"]
    command     = "${path.module}/scripts/destroy-k3d.ps1 -ClusterName '${self.triggers.cluster_name}' -NetworkName '${self.triggers.network_name}'"
  }
}

resource "local_file" "kubeconfig_pointer" {
  depends_on = [null_resource.k3s_cluster]
  filename   = "${path.module}/.kubeconfig-path"
  content    = var.kubeconfig_path
}
