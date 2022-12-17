<?php
session_start();
if (!isset($_SESSION['key'])) {
    $_SESSION['key'] = bin2hex(random_bytes(20));
}
?>

<form action="./" method="post">
    <input name="csrftoken" type="hidden" value="<?= $_SESSION['key'] ?>" />
    <input name="email" type="email" />
    <textarea name="subject"></textarea>
    <textarea name="body"></textarea>

    <input type="submit" />
</form>