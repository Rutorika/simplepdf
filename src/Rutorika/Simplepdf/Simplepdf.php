<?php
declare(strict_types=1);

namespace Rutorika\Simplepdf;

use TCPDF_FONTS;
use setasign\Fpdi\Tcpdf\Fpdi;
use Rutorika\Simplepdf\SimplepdfResult;

/**
 * @property SimplepdfResult $result
 */
class Simplepdf
{
    protected $fpdi;

    protected $fonts = [];

    protected $default_font = 'times';

    protected $default_text_color = [0, 0, 0];

    protected $default_font_size = 20;

    protected $default_line_height = 1; // Aspect ratio

    public function __construct(Fpdi $fpdi)
    {
        $this->setFpdi($fpdi);
    }

    public function __get($name)
    {
        if ($name == 'result') {
            return new SimplepdfResult($this->fpdi);
        }
    }

    public function setFpdi(Fpdi $fpdi) : Simplepdf
    {
        $this->fpdi = $fpdi;

        return $this;
    }

    /**
     * @return Fpdi
     */
    public function getFpdi() : Fpdi
    {
        return $this->fpdi;
    }

    /**
     * Add TTF font
     *
     * @param string $file
     * @return string
     */
    public function addFontTtf($file) : string
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Font file $file not found");
        }

        if (!isset($this->fonts[$file])) {
            $name = TCPDF_FONTS::addTTFfont($file);
            $this->fpdi->addFont($name, '', '', '', false);
            $this->fonts[$file] = $name;
        }

        return $name;
    }

    /**
     * Add PDF template
     *
     * @param string $file
     * @param integer $page Use page from template pdf
     * @return Simplepdf
     */
    public function addTemplate(string $file, int $page = 1) : Simplepdf
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Pdf template file $file not found");
        }

        $this->fpdi->setSourceFile($file);
        $page = $this->fpdi->ImportPage($page);

        $this->fpdi->useTemplate($page, 0, 0);

        return $this;
    }

    public function setDefaultTextColor(array $color) : Simplepdf
    {
        $this->default_text_color = $color;

        return $this;
    }

    public function setDefaultFont(string $name) : Simplepdf
    {
        if (!in_array($name, $this->fonts)) {
            throw new \RuntimeException("Font $name not defined");
        }

        $this->default_font = $name;

        return $this;
    }

    public function setDefaultFontSize($value) : Simplepdf
    {
        $this->default_font_size = $value;

        return $this;
    }

    public function setDefaultLineHeight($value) : Simplepdf
    {
        $this->default_line_height = $value;

        return $this;
    }

    /**
     * Add text block
     *
     * @param [type] $text
     * @param array $options[top]
     * @param array $options[left]
     * @param array $options[line_chars]
     * @param array $options[font]
     * @param array $options[font_size]
     * @param array $options[font_color]
     * @param array $options[line_height]
     * @return Simplepdf
     */
    public function addTextBlock(string $text, array $options = []) : Simplepdf
    {
        $top        = isset($options['top'])         ? (float) $options['top'] : 0;
        $left       = isset($options['left'])        ? (float) $options['left'] : 0;
        $lineChars  = isset($options['line_chars'])  ? (int) $options['line_chars'] : 0;
        $font       = isset($options['font'])        ? $options['font'] : $this->default_font;
        $fontSize   = isset($options['font_size'])   ? $options['font_size'] : $this->default_font_size;
        $textColor  = isset($options['text_color'])  ? $options['text_color'] : $this->default_text_color;
        $lineHeight = isset($options['line_height']) ? $options['line_height'] : $this->default_line_height;

        if ($lineChars > 0) {
            if (preg_match('/[^\s]{' . ($lineChars + 1) . ',}/u', $text)) {
                $this->fpdi->SetFontSize($fontSize - 2);
            }
            $text = $this->wrappingWordsNl($text, $lineChars);
        }

        $this->fpdi->setFont($font, '', $fontSize, '', false);
        $this->fpdi->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $this->fpdi->setCellHeightRatio($lineHeight);

        $this->fpdi->MultiCell(0, 0, $text, 0, '', 0, 1, $left, $top, true);
        $this->resetFont();

        return $this;
    }

    /**
     * Add 2D bar code
     *
     * @param [type] $text
     * @param float $options[top]
     * @param float $options[left]
     * @param float $options[size]
     * @param bool  $options[border]
     * @param array $options[color]
     * @return Simplepdf
     */
    public function addBarcode2D($text, $options = []) : Simplepdf
    {
        $top    = isset($options['top'])    ? (float) $options['top']    : 0;
        $left   = isset($options['left'])   ? (float) $options['left']   : 0;
        $size   = isset($options['size'])   ? (int)   $options['size']   : 0;
        $border = isset($options['border']) ? (bool)  $options['border'] : false;
        $color  = isset($options['color'])  ?         $options['color']  : $this->default_text_color;

        $style = array(
            'border'  => $border,
            'padding' => 0,
            'fgcolor' => $color,
            'bgcolor' => false
        );

        $this->fpdi->write2DBarcode($text, 'QRCODE,H', $left, $top, $size, $size, $style, 'N');

        return $this;
    }

    protected function resetFont() : void
    {
        $this->fpdi->setFont($this->default_font, '', $this->default_font_size, '', false);
        $this->fpdi->setCellHeightRatio($this->default_line_height);
        $this->fpdi->SetTextColor(
            $this->default_text_color[0],
            $this->default_text_color[1],
            $this->default_text_color[2]
        );
    }

    /**
     * Text wrapping for text block with maximum
     * number of characters wide
     * Saves existing line text in the text
     *
     * @param string $string
     * @param integer $lineChars
     * @return string
     */
    protected function wrappingWordsNl(string $string, int $lineChars) : string
    {
        $lines = [];

        foreach (explode("\n", $string) as $line) {
            $line = $this->wrappingWords($line, $lineChars);
            if (!empty($line)) {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Text wrapping for text block with maximum
     * number of characters wide
     *
     * @param string $string
     * @param integer $lineChars
     * @return void
     */
    protected function wrappingWords(string $string, int $lineChars) : string
    {
        $string  = preg_replace('/\s+/u', ' ', trim($string));
        $words   = explode(' ', $string);
        $lines   = [];
        $curLine = '';

        $addLine = function($line) use(&$lines) {
            $line = trim($line);
            if (!empty($line)) {
                $lines[] = $line;
                $line = '';
            }
        };

        foreach ($words as $i => $word) {

            $wordLength = mb_strlen($word, 'utf-8');
            $lineLength = mb_strlen($curLine, 'utf-8');
            $isLast     = !isset($words[$i + 1]);

            // Word chars more than specified in lineChars
            // Save the current line and write to the next line

            if ($wordLength > $lineChars) {
                $addLine($curLine);
                $addLine($word);
                $curLine = '';

            // Count chars in the current line with new word than specified in lineChars
            // Save current line
            // Add word to the new line

            } elseif (($lineLength + $wordLength + ($isLast ? 0 : 1)) > $lineChars) {
                $addLine($curLine);
                $curLine = $word;

            } else {
                $curLine .= ' ' . $word;
            }

            // Save line if the last word

            if ($isLast) {
                $addLine($curLine);
            }
        }

        return implode("\n", $lines);
    }
}