<?php
define('FPDF_VERSION', '1.86');

class FPDF {
    // Propriedades principais
    protected $page;
    protected $n;
    protected $buffer = '';
    protected $pages = array();
    protected $state = 0;
    protected $k;
    protected $wPt;
    protected $hPt;
    protected $w;
    protected $h;
    protected $x;
    protected $y;
    protected $lasth;
    protected $LineWidth;
    protected $fonts = array();
    protected $FontFamily = '';
    protected $FontSizePt = 12;
    protected $FontSize;
    protected $CurrentFont;
    protected $lMargin = 0;
    protected $tMargin = 0;
    protected $rMargin = 0;
    protected $AutoPageBreak = false;
    protected $bMargin = 0;

    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        $this->_dochecks();
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->FontFamily = '';

        $this->k = ($unit == 'pt') ? 1 :
                   (($unit == 'mm') ? 72 / 25.4 :
                   (($unit == 'cm') ? 72 / 2.54 : 72));

        if (is_string($size)) {
            $sizes = array(
                'A3'     => array(841.89, 1190.55),
                'A4'     => array(595.28, 841.89),
                'A5'     => array(420.94, 595.28),
                'Letter' => array(612, 792),
                'Legal'  => array(612, 1008)
            );
            $size = $sizes[$size];
        }

        $this->wPt = $size[0];
        $this->hPt = $size[1];
        $orientation = strtoupper($orientation);
        $this->w = $this->wPt / $this->k;
        $this->h = $this->hPt / $this->k;

        $this->SetMargins(10, 10);
        $this->SetAutoPageBreak(true, 10);
        $this->SetLineWidth(.2);
        $this->SetFont('Courier', '', 12);
    }

    function SetMargins($l, $t, $r = null) {
        $this->lMargin = $l;
        $this->tMargin = $t;
        $this->rMargin = $r === null ? $l : $r;
    }

    function SetAutoPageBreak($auto, $margin = 0) {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
    }

    function SetLineWidth($width) {
        $this->LineWidth = $width;
    }

    function SetFont($family, $style = '', $size = 0) {
        $this->FontFamily = $family;
        if ($size > 0) {
            $this->FontSizePt = $size;
        }
        $this->FontSize = $this->FontSizePt / $this->k;
        $this->CurrentFont = true;
    }

    function AddPage($orientation = '', $size = '') {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
    }

    function SetXY($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    function Line($x1, $y1, $x2, $y2) {
        $this->_out(sprintf(
            '%.2f %.2f m %.2f %.2f l S',
            $x1 * $this->k,
            ($this->h - $y1) * $this->k,
            $x2 * $this->k,
            ($this->h - $y2) * $this->k
        ));
    }

    function Text($x, $y, $txt) {
        $s = sprintf(
            'BT %.2f %.2f Td /F1 %.2f Tf (%s) Tj ET',
            $x * $this->k,
            ($this->h - $y) * $this->k,
            $this->FontSizePt,
            $this->_escape($txt)
        );
        $this->_out($s);
    }

    function Output($dest = 'I', $name = 'doc.pdf') {
        $this->_enddoc();
        if ($dest == 'I') {
            header('Content-Type: application/pdf');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo $this->buffer;
        } else {
            file_put_contents($name, $this->buffer);
        }
    }

    function _dochecks() {
        if (ini_get('mbstring.func_overload') & 2) {
            die('mbstring overloading must be disabled');
        }
    }

    function _enddoc() {
        $this->_putdoc();
    }

    function _putdoc() {
        $out = "%PDF-1.3\n";
        $nobj = 1;
        $offsets = array();
        $pages = '';

        foreach ($this->pages as $i => $p) {
            $offsets[] = strlen($out);
            $out .= "{$nobj} 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Courier >> >> >> >> /MediaBox [0 0 {$this->wPt} {$this->hPt}] /Contents " . ($nobj + 1) . " 0 R >>\nendobj\n";
            $nobj++;

            $offsets[] = strlen($out);
            $stream = "q\n" . $p . "Q\n";
            $out .= "{$nobj} 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n$stream\nendstream\nendobj\n";
            $nobj++;

            $pages .= (($i == 1) ? '' : ' ') . ($nobj - 2) . " 0 R";
        }

        $offsets[] = strlen($out);
        $out .= "2 0 obj\n<< /Type /Pages /Kids [ $pages ] /Count " . count($this->pages) . " >>\nendobj\n";

        $offsets[] = strlen($out);
        $out .= "3 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        $xrefpos = strlen($out);
        $out .= "xref\n0 " . ($nobj + 2) . "\n0 0000000000 65535 f \n";

        foreach ($offsets as $off) {
            $out .= sprintf("%d 00000 n \n", $off);
        }

        $out .= sprintf("%d 00000 n \n", strlen($out));
        $out .= sprintf("%d 00000 n \n", $xrefpos);

        $out .= "trailer\n<< /Size " . ($nobj + 2) . " /Root 3 0 R >>\nstartxref\n" . $xrefpos . "\n%%EOF";
        $this->buffer = $out;
    }

    function _out($s) {
        $this->pages[$this->page] .= $s . "\n";
    }

    function _escape($s) {
        return str_replace(
            array('\\', '(', ')', "\r", "\n"),
            array('\\\\', '\(', '\)', '\r', '\n'),
            $s
        );
    }
}
