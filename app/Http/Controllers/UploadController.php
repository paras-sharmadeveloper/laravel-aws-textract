<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessDocumentsJob;
use App\Services\S3Service;
use App\Services\PdfService;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class UploadController extends Controller
{
    protected $s3Service;
    protected $pdfService;

    public function __construct(S3Service $s3Service, PdfService $pdfService)
    {
        $this->s3Service = $s3Service;
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
                'phone' => 'required',
                'driving_license' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
                'bank_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
                'tax_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
                'bank_statement' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
                'pictures.*' => 'nullable|file|mimes:jpg,jpeg,png,heic,heif|max:10240',
                'other_doc.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
            ]);

            $dealFolder = $request->phone . '_' . Str::random(4);

            $result = [
                'email' => $request->email,
                'phone' => $request->phone,
                'documents' => []
            ];

            /*
        |--------------------------------------------------------------------------
        | SINGLE FILES
        |--------------------------------------------------------------------------
        */

            // $singleFields = [
            //     'driving_license' => 'ID.pdf',`
            //     'bank_doc' => 'VC.pdf',
            //     'tax_doc' => 'TaxID.pdf',
            //     'bank_statement' => 'Statement.pdf'
            // ];

            $singleFields = [
                'driving_license' => 'ID',
                'bank_doc' => 'VC',
                'tax_doc' => 'TAX_ID',
                'bank_statement' => 'Statement'
            ];

            foreach ($singleFields as $field => $name) {

                if (!$request->hasFile($field)) {
                    continue;
                }

                $file = $request->file($field);

                $ext = $file->getClientOriginalExtension();

                if (in_array($ext, ['heic', 'heif'])) {
                    $filePath = $this->convertHeicToJpg($file);
                    $ext = 'jpg';
                } else {
                    $filePath = $file->getRealPath();
                }
                // $finalName = $name . '_' . Str::random(5) . '.' . $ext;
                $finalName = $name . '.' . $ext;

                $s3Key = $this->s3Service->uploadFile(
                    $filePath,
                    "uploads/$dealFolder/$finalName"
                );

                $result['documents'][$finalName] = [
                    's3_keys' => [$s3Key]
                ];
            }

            /*
        |--------------------------------------------------------------------------
        | MULTIPLE PICTURES
        |--------------------------------------------------------------------------
        */


            if ($request->hasFile('pictures')) {

                $mergedPdfPath = $this->pdfService->mergeMixedFiles(
                    $request->file('pictures')
                );

                // $picnames = "Pics" . '_' . Str::random(5) . '.' . 'pdf';
                $picnames = "Pics.pdf";
                if (!file_exists($mergedPdfPath) || filesize($mergedPdfPath) == 0) {
                    throw new \Exception("Merged pictures PDF invalid");
                }

                // \Log::info('Merged Pics PDF', [
                //     'path' => $mergedPdfPath,
                //     'size' => filesize($mergedPdfPath)
                // ]);


                // copy($mergedPdfPath, $localPath);

                $s3Key = $this->s3Service->uploadFile(
                    $mergedPdfPath,
                    "uploads/$dealFolder/$picnames"
                );

                $result['documents'][$picnames] = [
                    's3_keys' => [$s3Key]
                ];

                // cleanup
            }

            /*
        |--------------------------------------------------------------------------
        | OTHER DOCUMENTS
        |--------------------------------------------------------------------------
        */

            if ($request->hasFile('other_doc')) {

                $mergedPdfPath = $this->pdfService->mergeMixedFiles(
                    $request->file('other_doc')
                );

                if (!file_exists($mergedPdfPath) || filesize($mergedPdfPath) == 0) {
                    throw new \Exception("Merged other documents PDF invalid");
                }


                $supportingname = "SupportingDoc.pdf";
                $s3Key = $this->s3Service->uploadFile(
                    $mergedPdfPath,
                    "uploads/$dealFolder/$supportingname"
                );

                $result['documents'][$supportingname] = [
                    's3_keys' => [$s3Key]
                ];

                unlink($mergedPdfPath); // cleanup
            }

            /*
        |--------------------------------------------------------------------------
        | DISPATCH JOB
        |--------------------------------------------------------------------------
        */

            ProcessDocumentsJob::dispatch($result);

            return back()->with('success', 'Your documents have been uploaded successfully. Processing has started and the lead will be created within approximately 2 minutes.');
        } catch (\Exception $e) {

            // \Log::error('Upload Error', [
            //     'error' => $e->getMessage()
            // ]);

            return back()->with('error',  $e->getMessage());
        }
    }

    function convertHeicToJpg($file)
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'heic' || $ext === 'heif') {

            $manager = new ImageManager(new Driver());

            $image = $manager->read($file->getRealPath());

            $newPath = storage_path('app/tmp/' . uniqid() . '.jpg');

            $image->toJpeg()->save($newPath);

            return $newPath;
        }

        return $file->getRealPath();
    }
}
