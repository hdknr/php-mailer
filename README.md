# php-mailer

## Docker

Base image: 

- `postfix-trap_postfix:latest` (https://github.com/hdknr/postfix-trap)

Run:

~~~bash
mkdir mails
env $(cat .secrets/.env|xargs) docker-compose up -d
~~~

