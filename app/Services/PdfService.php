<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
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
    |--------------------------------------------------------------------------
    | Convert Image to PDF (Preserve Aspect Ratio)
    |--------------------------------------------------------------------------
    */
    public function imageToPdf($imagePath)
{
    $image = $this->imageManager->read($imagePath);

    // resize large images
    $image->scaleDown(2000);

    $width  = $image->width();
    $height = $image->height();

    $tempImage = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
    $image->toJpeg(90)->save($tempImage);

    $pdf = new Fpdi();

    $pdfWidth  = $width * 0.264583;
    $pdfHeight = $height * 0.264583;

    $pdf->AddPage('P', [$pdfWidth, $pdfHeight]);
    $pdf->Image($tempImage, 0, 0, $pdfWidth, $pdfHeight);

    $temp = tempnam(sys_get_temp_dir(), 'pdf');
    $outputPath = $temp . '.pdf';

    rename($temp, $outputPath);

    $pdf->Output('F', $outputPath);

    unlink($tempImage);

    return $outputPath;
}

    /*
    |--------------------------------------------------------------------------
    | Merge Multiple PDFs
    |--------------------------------------------------------------------------
    */
    public function mergePdfs(array $pdfPaths)
    {
        $pdf = new Fpdi();

        foreach ($pdfPaths as $file) {

            if (!file_exists($file)) {
                continue;
            }

            $pageCount = $pdf->setSourceFile($file);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }
        $temp = tempnam(sys_get_temp_dir(), 'pdf');
        $outputPath = $temp . '.pdf';
        rename($temp, $outputPath);

       // $outputPath = tempnam(sys_get_temp_dir(), 'merged') . '.pdf';
        $pdf->Output('F', $outputPath);
        return $outputPath;
    }

    /*
    |--------------------------------------------------------------------------
    | Merge Multiple Images
    |--------------------------------------------------------------------------
    */
    public function mergeImages(array $images)
    {
        $pdfPaths = [];

        foreach ($images as $image) {
            $pdfPaths[] = $this->imageToPdf($image->getRealPath());
        }

        return $this->mergePdfs($pdfPaths);
    }

    /*
    |--------------------------------------------------------------------------
    | Merge Mixed Files
    |--------------------------------------------------------------------------
    */
    public function mergeMixedFiles(array $files)
    {
        $pdfPaths = [];

        foreach ($files as $file) {

            $extension = strtolower($file->getClientOriginalExtension());

            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $pdfPaths[] = $this->imageToPdf($file->getRealPath());
            } elseif ($extension === 'pdf') {
                $pdfPaths[] = $file->getRealPath();
            }
        }

        return $this->mergePdfs($pdfPaths);
    }
}
