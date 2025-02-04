FROM nginx:latest

# Remove default config and copy custom one
RUN rm /etc/nginx/conf.d/default.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/

# Expose port 80
EXPOSE 80