<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeService
{
    protected BarcodeGeneratorSVG $generator;

    public function __construct()
    {
        $this->generator = new BarcodeGeneratorSVG;
    }

    public function generateNumber(): string
    {
        $prefix = '20';
        $timestamp = now()->format('ymdHis');
        $random = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix . $timestamp . $random;
    }

    public function generateBarcodeImage(string $barcode, string $path): string
    {
        $filename = $barcode . '.svg';
        $fullPath = $path . '/' . $filename;

        $svg = $this->generator->getBarcode($barcode, BarcodeGeneratorSVG::TYPE_CODE_128);

        file_put_contents($fullPath, $svg);

        return 'barcodes/' . $filename;
    }

    public function getBarcodeSvg(string $barcode, int $widthFactor = 2, int $height = 40): string
    {
        return $this->generator->getBarcode($barcode, BarcodeGeneratorSVG::TYPE_CODE_128, $widthFactor, $height);
    }
}
