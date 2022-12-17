<?php
$domain = "mysite.com";

$meta = array(
    "fields" => array(
        "id" => "formId",
        "recaptcha" => "reCAPTCHA",
        "csrftoken" => "csrfToken",
        "subject"  => "subject",
        "body" => "body",
        "email" => "email",
    ),
);
$conf = array(
    "default" =>  array(
        "email_from" => "no-reply@{$domain}",
        "email_to" => "contact@{$domain}",
    ),
);
