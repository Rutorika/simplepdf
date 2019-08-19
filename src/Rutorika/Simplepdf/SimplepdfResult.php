<?php
declare(strict_types=1);

namespace Rutorika\Simplepdf;

use setasign\Fpdi\Tcpdf\Fpdi;

class SimplepdfResult
{
    const MODE_SAVE     = 'F'; // save to a local server file with the name given by name
    const MODE_STRING   = 'S'; // return the document as a string (name is ignored)
    const MODE_BASE64   = 'E'; // return the document as base64 mime multi-part email attachment (RFC 2045)
    const MODE_DOWNLOAD = 'D'; // send to the browser and force a file download with the name given by name
    const MODE_BROWSE   = 'I'; // send the file inline to the browser (default)

    protected $fpdi;

    public function __construct(Fpdi $fpdi)
    {
        $this->fpdi = $fpdi;
    }

    public function output(string $filename = null, string $mode) : void
    {
        $this->fpdi->Output($filename, $mode);
    }

    public function save(string $filename) : void
    {
        $dir = dirname($filename);

        if (!file_exists($dir)) {
            throw new \RuntimeException("Directory $dir for save PDF not found");
        }

        $this->output($filename, self::MODE_SAVE);
    }

    public function asString() : string
    {
        return $this->output(null, self::MODE_STRING);
    }

    public function asBase64() : string
    {
        return $this->output(null, self::MODE_BASE64);
    }

    public function download()
    {
        return $this->output(null, self::MODE_DOWNLOAD);
    }

    public function browse()
    {
        return $this->output(null, self::MODE_BROWSE);
    }
}