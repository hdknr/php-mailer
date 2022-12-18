<?php

$domain = "mysite.com";
$recaptcha_secretkey = getenv('RECAPTCHA_SECRETKEY');

$meta = array(
    # POST parameter name mapping
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

# configuration for each form ID
$conf = array(
    "default" =>  array(
        "email_from" => "no-reply@{$domain}",
        "email_to" => "contact@{$domain}",
    ),
    "form-id-1" =>  array(
        "email_from" => "no-reply-1@{$domain}",
        "email_to" => "contact-1@{$domain}",
    ),
);
