FROM alpine:3.20 AS prep
WORKDIR /app
COPY frontend/ ./
COPY docker/nginx/default.conf /tmp/default.conf

FROM nginx:1.27-alpine
COPY --from=prep /app /usr/share/nginx/html
COPY --from=prep /tmp/default.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
