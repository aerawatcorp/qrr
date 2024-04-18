<?php

/**
 * @author acpmasquerade
 * @license MIT
 * @codeowners aerawatcorp, acpmasquerade
 */

ini_set("display_errors", 0);
ini_set("error_reporting", 0);

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRGdImagePNG;

require_once __DIR__.'/../vendor/autoload.php';

if (!key_exists('chl', $_GET)){
    http_response_code(400);
    exit;
}

$data = $_GET['chl'] . "";

if(!$data){
    http_response_code(400);
    exit;
}

$hash = md5($data);
$time = time();
$date = date("Ymd", $time);

# Use cache directory to prevent re-rendering the same QR code
$cache_dir = __DIR__."/../cache/{$date}/";

# Make Directory if the expected cache directory does not exist
if(!file_exists($cache_dir) && !is_dir($cache_dir)){
    mkdir($cache_dir);
}

$cache_file = "${cache_dir}{$date}_{$hash}.png";
header("X-QRR-Hash: ${hash}");

if(file_exists($cache_file)){
    header('Content-type: image/png');
    header("X-QRR-Cache: HIT");
    echo file_get_contents($cache_file);
    exit;
}

# Set the version to auto to allow QR greater than 1248bit
# Refer : https://www.qrcode.com/en/about/version.html
$options = new QROptions;
$options->version             = QRCode::VERSION_AUTO;

$options->outputInterface     = QRGdImagePNG::class;
$options->outputBase64        = false;

$options->bgColor             = [255, 255, 255]; # White

$options->imageTransparent    = true;
$options->drawCircularModules = true;
$options->drawLightModules    = true;

$options->scale               = 10;
$options->circleRadius        = 0.4;

# Coloring options if any (uncomment to use)
# $options->fgColor             = [255, 255, 255];
# $options->transparencyColor   = [222, 222, 222];

$out = (new QRCode($options))->render($data);

list($type, $out) = explode(';', $out);
list(, $out)      = explode(',', $out);

$out = base64_decode($out);

header('Content-type: image/png');
header("X-QRR-Cache: MISS");
echo $out;

file_put_contents($cache_file, $out);

exit;

