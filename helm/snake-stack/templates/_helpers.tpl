{{- define "snake-stack.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" -}}
{{- end -}}

{{- define "snake-stack.fullname" -}}
{{- if .Values.fullnameOverride -}}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" -}}
{{- else -}}
{{- printf "%s" (include "snake-stack.name" .) | trunc 63 | trimSuffix "-" -}}
{{- end -}}
{{- end -}}

{{- define "snake-stack.labels" -}}
app.kubernetes.io/name: {{ include "snake-stack.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end -}}
