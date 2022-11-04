ARG IMAGE=dev

FROM nginx:1-alpine as dev

# This part only gets called if you call `docker build` with --build-arg IMAGE=deploy.
# In development mode (using docker-copmose), we don't use that build-arg but mount the file instead,
# rather than copying it into the image, so we don't have to rebuild the image every time we make a change.
FROM dev as deploy
WORKDIR /
COPY .docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

FROM ${IMAGE}
