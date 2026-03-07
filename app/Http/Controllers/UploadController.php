<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessDocumentsJob;
use App\Services\S3Service;
use App\Services\PdfService;

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
                'driving_license' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'bank_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'tax_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'bank_statement' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'pictures.*' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
                'other_doc.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
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
            //     'driving_license' => 'ID.pdf',
            //     'bank_doc' => 'VC.pdf',
            //     'tax_doc' => 'TaxID.pdf',
            //     'bank_statement' => 'Statement.pdf'
            // ];

              $singleFields = [
                'driving_license' => 'ID',
                'bank_doc' => 'VC',
                'tax_doc' => 'TaxID',
                'bank_statement' => 'Statement'
            ];

             foreach ($singleFields as $field => $name) {

            if (!$request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);

            $ext = $file->getClientOriginalExtension();

            $finalName = $name . '.' . $ext;

            $s3Key = $this->s3Service->uploadFile(
                $file->getRealPath(),
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

        // if ($request->hasFile('pictures')) {

        //     $mergedPdfPath = $this->pdfService->mergeMixedFiles(
        //         $request->file('pictures')
        //     );

        //     if (!file_exists($mergedPdfPath)) {
        //         throw new \Exception("Merged PDF not found");
        //     }

        //     // LOCAL STORAGE SAVE
        //     $localPath = storage_path('app/debug/Pics.pdf');

        //     if (!file_exists(dirname($localPath))) {
        //         mkdir(dirname($localPath), 0777, true);
        //     }

        //     copy($mergedPdfPath, $localPath);

        //     \Log::info('LOCAL PDF SAVED', [
        //         'temp_path' => $mergedPdfPath,
        //         'local_path' => $localPath,
        //         'size' => filesize($localPath)
        //     ]);

        //     return response()->json([
        //         'message' => 'PDF saved locally',
        //         'path' => $localPath
        //     ]);
        // }




            if ($request->hasFile('pictures')) {

                $mergedPdfPath = $this->pdfService->mergeMixedFiles(
                    $request->file('pictures')
                );

                  $localPath = storage_path('app/debug/Pics.pdf');

                if (!file_exists($mergedPdfPath) || filesize($mergedPdfPath) == 0) {
                    throw new \Exception("Merged pictures PDF invalid");
                }

                \Log::info('Merged Pics PDF', [
                    'path' => $mergedPdfPath,
                    'size' => filesize($mergedPdfPath)
                ]);


                // copy($mergedPdfPath, $localPath);

                $s3Key = $this->s3Service->uploadFile(
                    $mergedPdfPath,
                    "uploads/$dealFolder/Pics.pdf"
                );

                $result['documents']['Pics.pdf'] = [
                    's3_keys' => [$s3Key]
                ];

                unlink($mergedPdfPath); // cleanup
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

                \Log::info('Merged Other Docs PDF', [
                    'path' => $mergedPdfPath,
                    'size' => filesize($mergedPdfPath)
                ]);

                $s3Key = $this->s3Service->uploadFile(
                    $mergedPdfPath,
                    "uploads/$dealFolder/SupportingDoc.pdf"
                );

                $result['documents']['SupportingDoc.pdf'] = [
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

            \Log::error('Upload Error', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Something went wrong.');
        }
    }
}
