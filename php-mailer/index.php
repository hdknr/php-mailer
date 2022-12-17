<?php
require "./conf.php";

function get_boundary($attachment)
{
    if (isset($attachment['name']))
        return '----=_Boundary_' . uniqid(rand(1000, 9999) . '_') . '_';
    return null;
}

function do_sendmail($envelope_from, $from, $to, $reply,  $subject, $msg, $attachment)
{

    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    $opt = "-f$envelope_from";
    $headers = "From: {$from}\nReply-To: ${reply}\n";

    $boundary = get_boundary($attachment);

    if ($boundary) {
        $mime_type = "application/octet-stream";

        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"${boundary}\"\n";
        $headers .= "Content-Transfer-Encoding: 7bit\n";


        $filename = $attachment['name'];
        $filename = mb_convert_encoding($filename, 'ISO-2022-JP');
        $filename = "=?ISO-2022-JP?B?" . base64_encode($filename) . "?=";

        $filebody = file_get_contents($attachment['tmp_name']);
        $fileencoded = chunk_split(base64_encode($filebody));

        $message = mb_convert_encoding($msg, 'ISO-2022-JP');

        $body = '';

        # file
        $body .= '--' . $boundary . "\n";
        $body .= "Content-Type: {$mime_type}; name=\"{$filename}\"\n" .
            "Content-Transfer-Encoding: base64\n" .
            "Content-Disposition: attachment; filename=\"{$filename}\"\n";
        $body .= $fileencoded . "\n";

        # message
        $body .= '--' . $boundary . "\n";
        $body .= "Content-Type: text/plain; charset=ISO-2022-JP;\n" .
            "Content-Transfer-Encoding: 7bit\n";
        $body .= "\n";
        $body .= "{$message}\n";
        $body .= "\n";


        $msg = $body;
    }

    return mb_send_mail($to, $subject, $msg, $headers, $opt);
}


function get_value($key, $default)
{
    global $meta;
    $name = $meta["fields"][$key];
    return isset($_POST[$name]) ? $_POST[$name] : $default;
}


function api_recaptch($secret, $token)
{
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => $secret,
        'response' =>  $token,
    );

    $context = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => implode("\r\n", array('Content-Type: application/x-www-form-urlencoded',)),
            'content' => http_build_query($data)
        )
    );
    $api_response = file_get_contents($url, false, stream_context_create($context));

    $result = json_decode($api_response);
    return $result;
}

function verify_recaptch()
{
    global $meta;
    $secret = $meta["recaptcha"]["secret"];
    if ($secret == null) {
        return true;
    }
    $token = get_value("recaptch", "");
    $res = api_recaptch($secret, $token);
    return $res->success;
}

session_start();


if ($_SERVER["REQUEST_METHOD"] == 'POST') {

    $csrftoken = get_value("csrftoken", "");
    if ($csrftoken != $_SESSION['key']) {
        http_response_code(403);
        $post = var_export($_POST);
        exit();
    }
    if (!verify_recaptch()) {
        http_response_code(403);
        $post = var_export($_POST);
        exit();
    }

    $id = get_value("id", "default");

    $envelope_from = $conf[$id]["email_from"];
    $email_to = $conf["$id"]["email_to"];

    $from = get_value("email", $envelope_from);
    $body = get_value("body", "");
    $subject = get_value("subject", "");

    $attachment = null; # $_FILES['attachment'];

    if (do_sendmail($envelope_from, $from, $email_to, $reply,  $subject, $body, $attachment)) {
        unset($_SESSION['key']);
        echo "SEND OK";
    } else {
        http_response_code(403);
    }
    exit();
}

if (!isset($_SESSION['key'])) {
    $_SESSION['key'] = bin2hex(random_bytes(20));
}

$data = array('key' => $_SESSION['key'], 'method' => $_SERVER['REQUEST_METHOD']);
header("Content-Type: application/json");
# header("Access-Control-Allow-Origin: application/json");
echo json_encode($data);
