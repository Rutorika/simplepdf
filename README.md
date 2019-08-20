# Rutorika Simplepdf

Requirements:

* **pdftk**

Install **pdftk** on the Alpine

```
apk add pdftk
```

Install **pdftk** on the Ubuntu

```
sudo add-apt-repository ppa:malteworld/ppa
sudo apt update
sudo apt install pdftk
```

Usage

```
<?php

use Rutorika\Simplepdf\Simplepdf;
use Rutorika\Simplepdf\SimplepdfBuilder;

$template   = \resource_path('pdf/form.pdf');
$fontFile   = \resource_path('pdf/font.ttf');
$fontSize   = 20;
$fontColor  = [255, 0, 0];
$lineHeight = 1.05;
$maxChars   = 20;

$pdf = SimplepdfBuilder::page([
    'unit'             => 'mm',
    'page_orientation' => 'P',
    'page_format'      => 'A4',
    'page_template'    => $template,
]);

$font = $pdf->addFontTtf($fontFile);

// Define default font, font size, font color and line height

$pdf->setDefaultFont($font)
    ->setDefaultFontSize($fontSize)
    ->setDefaultTextColor($fontColor)
    ->setDefaultLineHeight($lineHeight);

// Text block 1

$txt = "Текст, который будет выведен по 20 символов в строку";

$pdf->addTextBlock($txt, [
    'left'       => 14.3,  
    'top'        => 10.5, 
    'line_chars' => $maxChars,
]);

// Text block 2

$txt = "Текст\nдля которого нужно оставить перенос";

$pdf->addTextBlock($txt, [
    'left'       => 14.3,  
    'top'        => 57.5, 
    'line_chars' => $maxChars,
]);

// Barcode

$txt = "Данные для barcode";

$pdf->addBarcode2D($txt, [
    'left'       => 54.3,  
    'top'        => 100.5, 
    'size'       => 24.5, 
    'color'      => [0, 0, 255], // Custom color barcode
]);

$pdf->result->browse();
```