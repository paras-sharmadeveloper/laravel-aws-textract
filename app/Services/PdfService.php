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

        $width  = $image->width();
        $height = $image->height();

        $tempImage = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        $image->toJpeg(90)->save($tempImage);

        $pdf = new Fpdi();

        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        // A4 size mm
        $pageWidth = 210;
        $pageHeight = 297;

        // auto orientation
        if ($width > $height) {
            $orientation = 'L';
            $pageWidth = 297;
            $pageHeight = 210;
        } else {
            $orientation = 'P';
        }

        $pdf->AddPage($orientation, [$pageWidth, $pageHeight]);

        // calculate scale
        $ratio = min($pageWidth / $width, $pageHeight / $height);

        $newWidth  = $width * $ratio;
        $newHeight = $height * $ratio;

        // center image
        $x = ($pageWidth - $newWidth) / 2;
        $y = ($pageHeight - $newHeight) / 2;

        $pdf->Image($tempImage, $x, $y, $newWidth, $newHeight);

        $output = tempnam(sys_get_temp_dir(), 'pdf') . '.pdf';

        $pdf->Output($output, 'F');

        unlink($tempImage);

        return $output;
    }
    public function OldimageToPdf($imagePath)
    {
        $image = $this->imageManager->read($imagePath);
        // $image->scaleDown(2000);

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
        // \Log::info('PDF PATH 1', [
        //     'path' => $output,
        //     'size' => filesize($output)
        // ]);
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
        // \Log::info('PDF PATH 2', [
        //     'path' => $pdfPaths,
        // ]);

        return $this->mergePdfs($pdfPaths);
    }
}
