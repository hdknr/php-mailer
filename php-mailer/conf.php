<?php

$domain = "mysite.com";
$recaptcha_secretkey = getenv('RECAPTCHA_SECRETKEY');

$meta = array(
    "fields" => array(
        "id" => "formId",
        "recaptcha" => "g-recaptcha-response",
        "csrftoken" => "csrfToken",
        "subject"  => "subject",
        "body" => "body",
        "email" => "email",
    ),
    "recaptcha" => array(
        "secret" => "{$recaptcha_secretkey}",
    )
);

$conf = array(
    "default" =>  array(
        "email_from" => "no-reply@{$domain}",
        "email_to" => "contact@{$domain}",
    ),
);
