<?php

namespace App\Support;

/**
 * The file fields the application form accepts. Shared by the S3 presign
 * endpoint, the submit endpoint and the background intake job.
 */
class UploadFields
{
    public const MAX_BYTES = 10 * 1024 * 1024;

    private const DOC_EXT = ['jpg', 'jpeg', 'png', 'pdf', 'heic', 'heif'];

    public const FIELDS = [
        'driving_license' => ['multi' => false, 'max' => 1, 'ext' => self::DOC_EXT],
        'bank_doc' => ['multi' => false, 'max' => 1, 'ext' => self::DOC_EXT],
        'tax_doc' => ['multi' => false, 'max' => 1, 'ext' => self::DOC_EXT],
        'projections' => ['multi' => false, 'max' => 1, 'ext' => ['pdf', 'xlsx', 'xls', 'csv', 'jpg', 'jpeg', 'png', 'heic', 'heif']],
        'statement_bank' => ['multi' => true, 'max' => 20, 'ext' => self::DOC_EXT],
        'statement_ccp' => ['multi' => true, 'max' => 20, 'ext' => self::DOC_EXT],
        'statement_pos' => ['multi' => true, 'max' => 20, 'ext' => self::DOC_EXT],
        'pictures' => ['multi' => true, 'max' => 30, 'ext' => self::DOC_EXT],
        'other_doc' => ['multi' => true, 'max' => 30, 'ext' => self::DOC_EXT],
    ];

    /**
     * Content validation rules, applied to every file once it is on the server.
     */
    public static function rules(): array
    {
        return [
            'projections' => 'nullable|file|mimes:pdf,xlsx,xls,csv,txt,zip,jpg,jpeg,png,heic,heif|extensions:pdf,xlsx,xls,csv,jpg,jpeg,png,heic,heif|max:10240',
            'driving_license' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
            'bank_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
            'tax_doc' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
            'pictures' => 'nullable|array|max:30',
            'pictures.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
            'other_doc' => 'nullable|array|max:30',
            'other_doc.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
            'statement_bank' => 'nullable|array|max:20',
            'statement_bank.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
            'statement_ccp' => 'nullable|array|max:20',
            'statement_ccp.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
            'statement_pos' => 'nullable|array|max:20',
            'statement_pos.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic,heif|max:10240',
        ];
    }

    public static function extension(string $name): string
    {
        return strtolower(pathinfo($name, PATHINFO_EXTENSION));
    }
}
