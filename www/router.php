<?php

$mode = (isset($_ENV['MODE'])) ? $_ENV['MODE'] : "default";
$requested = (basename($_SERVER['REQUEST_URI']) == "") ? "index.html" : basename($_SERVER['REQUEST_URI']);
$valid_array = array("", "index.html", "packages.json");
$valid_requests = (isset($_ENV['EXTRA_FILES'])) ? $valid_array + explode(',', $_ENV['EXTRA_FILES']) : $valid_array;

$S3_BUCKET = $_ENV['S3_BUCKET'];
$SATIS_URL = $_ENV['SATIS_URL'];

if ($mode == "redirect") {
    if (in_array($requested, $valid_requests)) {
        # redirect mode, sent over to the S3 Bucket
        header("HTTP/1.1 303 See Other");
        header("Location: https://s3.amazonaws.com/{$S3_BUCKET}/{$requested}");
    } else {
        header("HTTP/1.1 404 Not Found");
    }
} elseif ($mode == "external" && isset($SATIS_URL)) {
    # full redirect mode: send everything the specified URL, if it is set
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: {$SATIS_URL}/{$requested}");
} else {
    if (in_array($requested, $valid_requests)) {
        # default mode: stream it
        readfile("https://s3.amazonaws.com/{$S3_BUCKET}/{$requested}");
    } else {
        header("HTTP/1.1 404 Not Found");
    }
}
?>