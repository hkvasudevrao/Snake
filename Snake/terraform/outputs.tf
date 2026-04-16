output "cluster_name" {
  value       = var.cluster_name
  description = "Created k3d cluster name"
}

output "kubeconfig_path" {
  value       = var.kubeconfig_path
  description = "Path to kubeconfig file"
}

output "kubectl_nodes_command" {
  value       = "kubectl --kubeconfig \"${var.kubeconfig_path}\" get nodes"
  description = "Quick verification command"
}
