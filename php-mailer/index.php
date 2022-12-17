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


session_start();

$ADDRESS_FROM = "form@spin-dd.com";

if ($_SERVER["REQUEST_METHOD"] == 'POST') {

    if ($_POST['csrftoken'] != $_SESSION['key']) {
        http_response_code(403);
        $post = var_export($_POST);
        exit();
    }

    $id = isset($_POST["form_id"]) ? $_POST["form_id"] : "default";

    $envelope_from = $conf[$id]["email_from"];
    $email_to = $conf["$id"]["email_to"];
    $from = isset($_POST["email"]) ? $_POST["email"] : $envelope_from;

    $body = $_POST['body'];
    $subject = $_POST['subject'];
    $reply = "";    # $_POST['reply'];
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
