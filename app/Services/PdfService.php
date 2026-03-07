<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PdfService
{
    protected $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /*
    |-------------------------------------------------------
    | Convert Image to PDF
    |-------------------------------------------------------
    */

    public function imageToPdf($imagePath)
    {
        $image = $this->imageManager->read($imagePath);
          $image->scaleDown(2000);

        $width  = $image->width();
        $height = $image->height();

        $tempImage = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        $image->toJpeg(90)->save($tempImage);

        $pdf = new Fpdi();

        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        // pixel → mm
        $pdfWidth  = $width * 0.264583;
        $pdfHeight = $height * 0.264583;

        // auto orientation
        $orientation = $pdfWidth > $pdfHeight ? 'L' : 'P';

        $pdf->AddPage($orientation, [$pdfWidth, $pdfHeight]);

        $pdf->Image($tempImage, 0, 0, $pdfWidth, $pdfHeight);

        $output = tempnam(sys_get_temp_dir(), 'pdf') . '.pdf';

        $pdf->Output($output, 'F');

        unlink($tempImage);

        return $output;
    }

    // public function imageToPdf($imagePath)
    // {
    //     $image = $this->imageManager->read($imagePath);

    //     $width  = $image->width();
    //     $height = $image->height();

    //     $tempImage = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
    //     $image->toJpeg(90)->save($tempImage);

    //     $pdf = new Fpdi();

    //     $pdf->SetPrintHeader(false);
    //     $pdf->SetPrintFooter(false);


    //     $pdf->AddPage('P', 'A4');

    //     $pageWidth  = 210;
    //     $pageHeight = 297;

    //     $imgRatio = $width / $height;

    //     $imgWidth = $pageWidth - 20; // margin
    //     $imgHeight = $imgWidth / $imgRatio;

    //     if ($imgHeight > ($pageHeight - 20)) {
    //         $imgHeight = $pageHeight - 20;
    //         $imgWidth = $imgHeight * $imgRatio;
    //     }

    //     $x = ($pageWidth - $imgWidth) / 2;
    //     $y = ($pageHeight - $imgHeight) / 2;

    //     $pdf->Image($tempImage, $x, $y, $imgWidth, $imgHeight);

    //     $output = tempnam(sys_get_temp_dir(), 'pdf') . '.pdf';

    //     $pdf->Output($output, 'F');

    //     unlink($tempImage);

    //     return $output;
    // }

    /*
    |-------------------------------------------------------
    | Merge PDFs
    |-------------------------------------------------------
    */

    public function mergePdfs(array $pdfPaths)
    {
        $pdf = new Fpdi();

        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        foreach ($pdfPaths as $file) {

            if (!file_exists($file)) {
                continue;
            }

            $pageCount = $pdf->setSourceFile($file);

            for ($i = 1; $i <= $pageCount; $i++) {

                $template = $pdf->importPage($i);

                $size = $pdf->getTemplateSize($template);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

                $pdf->useTemplate($template);
            }
        }

        $output = tempnam(sys_get_temp_dir(), 'merged') . '.pdf';



        $pdf->Output($output, 'F');
        \Log::info('PDF PATH 1', [
            'path' => $output,
            'size' => filesize($output)
        ]);
        return $output;
    }

    /*
    |-------------------------------------------------------
    | Merge Mixed Files
    |-------------------------------------------------------
    */

    public function mergeMixedFiles(array $files)
    {
        $pdfPaths = [];

        foreach ($files as $file) {

            $ext = strtolower($file->getClientOriginalExtension());

            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                $pdfPaths[] = $this->imageToPdf($file->getRealPath());
            } elseif ($ext === 'pdf') {

                $pdfPaths[] = $file->getRealPath();
            }
        }
        \Log::info('PDF PATH 2', [
            'path' => $pdfPaths,
        ]);

        return $this->mergePdfs($pdfPaths);
    }
}
