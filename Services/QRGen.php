<?php

namespace app\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\SvgWriter;

class QRGen
{
    /**
     * Renders a QR code based on the given data and returns it as an SVG string.
     *
     * @param string $data The data to encode in the QR code.
     * @throws \Exception If there is an error creating the QR code.
     * @return string The SVG representation of the QR code.
     */
    public static function renderQR(string $data): string
    {
        $writer = new SvgWriter();

        // Create QR code
        $qrCode = QrCode::create($data)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $result = $writer->write($qrCode);

        $svg = $result->getString();
        return $svg;
    }
}
