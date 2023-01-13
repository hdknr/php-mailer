<?php
require "./conf.php";

function logger($message)
{
    # https://www.php.net/manual/ja/function.error-log.php
    error_log($message, 0);
}

function error_exit($code, $message)
{
    http_response_code($code);
    echo  $message;
    logger($message);
    exit();
}

function get_boundary($attachment)
{
    if (isset($attachment['name']))
        return '----=_Boundary_' . uniqid(rand(1000, 9999) . '_') . '_';
    return null;
}

function do_sendmail($envelope_from, $from, $to, $cc, $reply,  $subject, $msg, $attachment)
{
    global $meta;

    $encoding = $meta["mail"]["encoding"];
    $bits = $meta["mail"]["bits"];

    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    $opt = "-f{$envelope_from}";
    $headers = "From: {$from}\nReply-To: {$reply}\nCc: {$cc}\n";
    $headers .= "MIME-Version: 1.0\n";
    $headers .= "Content-Transfer-Encoding: {$bits}\n";

    $message = mb_convert_encoding($msg, $encoding);

    $boundary = get_boundary($attachment);

    if ($boundary) {
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\n";

        $filename = $attachment['name'];
        $filename = mb_encode_mimeheader($filename, $encoding);
        $filebody = file_get_contents($attachment['tmp_name']);
        $fileencoded = chunk_split(base64_encode($filebody));

        $body = '';
        $mime_type = "application/octet-stream";

        # file
        $body .= '--' . $boundary . "\n";
        $body .= "Content-Type: {$mime_type}; name=\"{$filename}\"\n" .
            "Content-Transfer-Encoding: base64\n" .
            "Content-Disposition: attachment; filename=\"{$filename}\"\n";
        $body .= $fileencoded . "\n";

        # message
        $body .= '--' . $boundary . "\n";
        $body .= "Content-Type: text/plain; charset={$encoding};\n" .
            "Content-Transfer-Encoding: {$bits}\n";
        $body .= "\n";
        $body .= "{$message}\n";
        $body .= "\n";


        $msg = $body;
    } else {
        $headers .= "Content-Type: text/plain; charset={$encoding}\n";
        $msg = $message;
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

function verify_recaptcha()
{
    global $meta;

    $secret = $meta["recaptcha"]["secret"];
    if (empty($secret)) {
        return true;
    }

    $token = get_value("recaptcha", "");
    $res = api_recaptch($secret, $token);
    if (!$res->success) {
        error_log("reCAPTCHA:" . var_export($res, true));
    }
    return $res->success;
}

session_start();


if ($_SERVER["REQUEST_METHOD"] == 'POST') {

    $csrftoken = get_value("csrftoken", "");

    if ($csrftoken != $_SESSION['key']) {
        error_exit(401, "CSRF Token mismatch");
    }

    if (!verify_recaptcha()) {
        error_exit(403, "reCAPTCHA failed");
        exit();
    }

    $id = get_value("id", "default");

    $envelope_from = $conf[$id]["email_from"];
    $email_to = $conf["$id"]["email_to"];
    $email_cc = $conf["$id"]["email_cc"];

    $from = get_value("email", $envelope_from);
    $body = get_value("body", "");
    $subject = get_value("subject", "");

    $attachment = null; # $_FILES['attachment'];

    if (do_sendmail($envelope_from, $from, $email_to, $email_cc, $reply,  $subject, $body, $attachment)) {
        unset($_SESSION['key']);
        echo "SEND OK";
    } else {
        error_exit(400, "sendmail failed");
        exit();
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
