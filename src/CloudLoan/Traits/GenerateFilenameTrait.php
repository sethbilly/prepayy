<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 24/01/2017
 * Time: 15:32
 */
namespace CloudLoan\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait GenerateFilenameTrait
{
    /**
     * @param UploadedFile $file
     * @param string $bucket
     * @return string
     */
    private function generateFilename(UploadedFile $file, string $bucket = null): string
    {
        // If no filename has been generated after 5 iterations, use the providers uuid suffixed with current timestamp
        $i = 0;
        $ext = $file->guessClientExtension();

        do {
            $filename = str_random(40) . '.' . $ext;
            $path = $bucket ? $bucket . '/' . $filename : $filename;

            if (!Storage::exists($path)) {
                break;
            } else {
                $filename = null;
            }
            ++$i;
        } while ($i < 4);

        return $filename;
    }
}