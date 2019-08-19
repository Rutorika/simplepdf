<?php
declare(strict_types=1);

namespace Rutorika\Simplepdf;

use Rutorika\Simplepdf\Simplepdf;
use setasign\Fpdi\Tcpdf\Fpdi;

class SimplepdfBuilder
{
    /**
     * Single page builder
     *
     * @param array $options
     * @param array $options[unit]             Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]. Default mm
     * @param array $options[page_orientation] Page orientation (P=portrait, L=landscape). Default P
     * @param array $options[page_format]      Page format (A4, A3). Default A4
     * @param array $options[page_template]    Pdf template file
     * @return Simplepdf
     */
    public static function page(array $options = []) : Simplepdf
    {
        $options += [
            'page_orientation' => 'P',
            'unit'             => 'mm',
            'page_format'      => 'A4',
        ];

        $fpdi = new Fpdi(
            $options['page_orientation'],
            $options['unit'],
            $options['page_format'],
            true,
            'UTF-8',
            false
        );

        $fpdi->SetCreator(PDF_CREATOR);
        $fpdi->setPrintHeader(false);
        $fpdi->setPrintFooter(false);
        $fpdi->SetMargins(0, 0, 0);
        $fpdi->AddPage();

        $spdf = new Simplepdf($fpdi);

        if (!empty($options['page_template'])) {
            $spdf->addTemplate($options['page_template']);
        }

        return $spdf;
    }
}