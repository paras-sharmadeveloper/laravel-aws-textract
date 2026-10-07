<?php

namespace App\Services;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

/**
 * Turns an application's raw files into the stored S3 layout and the
 * documents/statements payload the OCR pipeline consumes. This is the
 * processing that used to run inside UploadController::upload, unchanged.
 */
class IntakeService
{
    public function __construct(
        protected S3Service $s3Service,
        protected PdfService $pdfService
    ) {}

    /**
     * @param  array<string, \Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]>  $files
     * @return array{documents: array, statements: array, lead_documents: array}
     */
    public function store(array $files, string $dealFolder): array
    {
        $result = ['documents' => [], 'statements' => []];
        $leadDocuments = [];

        $singleFields = [
            'driving_license' => 'ID',
            'bank_doc' => 'VC',
            'tax_doc' => 'TAX_ID',
            'projections' => 'Projections',
        ];

        foreach ($singleFields as $field => $name) {

            if (empty($files[$field])) {
                continue;
            }

            $file = $files[$field];

            $ext = $file->getClientOriginalExtension();

            if (in_array($ext, ['heic', 'heif', 'HEIC', 'HEIF'])) {
                $filePath = $this->convertHeicToJpg($file);
                $ext = 'jpg';
            } else {
                $filePath = $file->getRealPath();
            }
            $finalName = $name . '.' . $ext;

            $s3Key = $this->s3Service->uploadFile(
                $filePath,
                "uploads/$dealFolder/$finalName"
            );

            $result['documents'][$finalName] = [
                's3_keys' => [$s3Key]
            ];
            $leadDocuments[] = ['category' => $name, 'file_name' => $finalName, 's3_key' => $s3Key];
        }

        /*
        |--------------------------------------------------------------------------
        | STATEMENTS (BANK / CCP / POS) - uploaded individually, never merged
        |--------------------------------------------------------------------------
        */

        $statementFields = [
            'statement_bank' => 'bank',
            'statement_ccp' => 'ccp',
            'statement_pos' => 'pos',
        ];

        foreach ($statementFields as $field => $category) {

            foreach ($files[$field] ?? [] as $file) {

                $ext = strtolower($file->getClientOriginalExtension());
                $originalName = $file->getClientOriginalName();

                if (in_array($ext, ['heic', 'heif'])) {
                    $filePath = $this->convertHeicToJpg($file);
                    $ext = 'jpg';
                    $originalName = pathinfo($originalName, PATHINFO_FILENAME) . '.jpg';
                } else {
                    $filePath = $file->getRealPath();
                }

                $s3Key = $this->s3Service->uploadFile(
                    $filePath,
                    "uploads/$dealFolder/statements/$category/" . Str::random(8) . '.' . $ext
                );

                $result['statements'][] = [
                    'category' => $category,
                    's3_key' => $s3Key,
                    'original_name' => $originalName,
                    'ext' => $ext,
                ];
                $leadDocuments[] = ['category' => $category, 'file_name' => $originalName, 's3_key' => $s3Key];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | MULTIPLE PICTURES / OTHER DOCUMENTS - each merged into one PDF
        |--------------------------------------------------------------------------
        */

        $mergedFields = [
            'pictures' => ['Pics.pdf', 'Pics', 'Merged pictures PDF invalid'],
            'other_doc' => ['supportingdoc.pdf', 'supportingdoc', 'Merged other documents PDF invalid'],
        ];

        foreach ($mergedFields as $field => [$fileName, $category, $error]) {

            if (empty($files[$field])) {
                continue;
            }

            $merged = $this->pdfService->mergeMixedFiles($files[$field]);

            // PDFs that couldn't be flattened are stored untouched next to the
            // merged file: supportingdoc.pdf, supportingdoc-2.pdf, ...
            $paths = array_values(array_filter([$merged['merged'], ...$merged['separate']]));

            if (empty($paths)) {
                throw new \Exception($error);
            }

            $s3Keys = [];

            foreach ($paths as $index => $path) {

                if (!file_exists($path) || filesize($path) == 0) {
                    throw new \Exception($error);
                }

                $name = $index === 0
                    ? $fileName
                    : pathinfo($fileName, PATHINFO_FILENAME) . '-' . ($index + 1) . '.pdf';

                $s3Key = $this->s3Service->uploadFile($path, "uploads/$dealFolder/$name");

                $s3Keys[] = $s3Key;
                $leadDocuments[] = ['category' => $category, 'file_name' => $name, 's3_key' => $s3Key];
            }

            $result['documents'][$fileName] = [
                's3_keys' => $s3Keys
            ];
        }

        $result['lead_documents'] = $leadDocuments;

        return $result;
    }

    public function convertHeicToJpg($file)
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'heic' || $ext === 'heif') {

            $manager = new ImageManager(new Driver());

            $image = $manager->read($file->getRealPath());

            if (!is_dir(storage_path('app/tmp'))) {
                mkdir(storage_path('app/tmp'), 0755, true);
            }

            $newPath = storage_path('app/tmp/' . uniqid() . '.jpg');

            $image->toJpeg()->save($newPath);

            return $newPath;
        }

        return $file->getRealPath();
    }
}
