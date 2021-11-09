FROM nginx:1.21.3-alpine

ARG SERVER_NAME
ARG SERVER_VERSION
ARG CONTAINER_NAME

COPY ./shell/ /tmp/scripts/
RUN chmod -R 777 /tmp/scripts/

COPY ./nginx/configurations/nginx.conf /etc/nginx/
COPY ./nginx/hosts/default.conf /etc/nginx/sites-available/default.conf

RUN /tmp/scripts/distributeConfig.sh /etc/nginx/sites-available/default.conf "{SERVER_NAME}" "${SERVER_NAME}" \
  && /tmp/scripts/distributeConfig.sh /etc/nginx/sites-available/default.conf "{SERVER_VERSION}" "${SERVER_VERSION}" \
  && /tmp/scripts/distributeConfig.sh /etc/nginx/sites-available/default.conf "{CONTAINER_NAME}" "${CONTAINER_NAME}" \
  ;

WORKDIR /etc/nginx/

CMD ["nginx"]