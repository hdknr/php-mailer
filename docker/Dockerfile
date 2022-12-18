ARG BASE_IMAGE=postfix-trap_postfix:latest

FROM ${BASE_IMAGE}

ENV DEPLOY=devel \
    APP_BASE=/usr/src/php-mailer  \
    SRC=./docker

COPY ${SRC}/bin  ${APP_BASE}/bin

RUN apt-get update && apt-get upgrade -y && apt-get autoremove -y 
RUN ${APP_BASE}/bin/setup_php.sh
RUN apt-get install gettext-base -y
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* 

EXPOSE 25 80


# Entry
CMD ["/usr/src/php-mailer/bin/entrypoint.sh"]