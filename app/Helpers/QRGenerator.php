<?php
namespace App\Helpers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class QRGenerator {
    /**
     * Output QR code as PNG (directly to browser or download).
     * @param string $content  Data to encode
     * @param bool   $download If true, force download as PNG
     */
    public static function output($content, $download = false) {
        $options = new QROptions([
            'version'      => 5,
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => QRCode::ECC_L,
            'scale'        => 8,
            'imageBase64'  => false,
        ]);

        $qr = new QRCode($options);
        $pngData = $qr->render($content);

        if ($download) {
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="qr-code.png"');
        } else {
            header('Content-Type: image/png');
        }
        echo $pngData;
        exit;
    }
}