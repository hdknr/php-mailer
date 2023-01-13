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
    ),
    "mail" => array(
        "encoding" => "UTF-8",   # ISO-2022-JP
        "bits" => "8bit",   # 7bit
    ),
);

function addr($name, $address)
{
    global $meta;
    $enc = mb_encode_mimeheader($name, $meta["mail"]["encoding"]);
    return "{$enc} <{$address}>";
}

$address1 = addr("連絡先1", "contact-1@{$domain}");
$address2 = addr("連絡先2", "contact-2@{$domain}");


# configuration for each form ID
$conf = array(
    "default" =>  array(
        "email_from" => "no-reply@{$domain}",
        "email_to" => "{$address1}, {$address2}",
        "email_cc" => "contact-1-cc@{$domain}",
    ),
    "school-contact-default" =>  array(
        "email_from" => "no-reply-1@{$domain}",
        "email_to" => "contact-1@{$domain}",
        "email_cc" => "contact-1-cc@{$domain}",
    ),
);
