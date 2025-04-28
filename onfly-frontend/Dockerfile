# Usa imagem pronta do nginx
FROM nginx:alpine

# Limpa arquivos padrão do nginx
RUN rm -rf /usr/share/nginx/html/*

# Copia a build gerada para o nginx
COPY dist/ /usr/share/nginx/html/

# (Opcional) Se quiser SPA: copie seu nginx.conf também
# COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]