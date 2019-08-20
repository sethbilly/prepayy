<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 24/01/2017
 * Time: 15:56
 */

use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;

trait GetMockUploadedFileTrait
{
    /**
     * @param string $fullPathToFile
     * @param bool $isValid
     * @return \Mockery\MockInterface
     */
    public function getMockUploadedFile(string $fullPathToFile, bool $isValid = true): MockInterface
    {
        $this->assertFileExists($fullPathToFile);

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $fileInfo->file($fullPathToFile);
        $ext = pathinfo($fullPathToFile, PATHINFO_EXTENSION);

        return \Mockery::mock(
            UploadedFile::class,
            [
                'getClientOriginalName' => pathinfo($fullPathToFile, PATHINFO_BASENAME),
                'getClientOriginalExtension' => $ext,
                'guessClientExtension' => $ext,
                'getPath' => $fullPathToFile,
                'isValid' => $isValid,
                'guessExtension' => $ext,
                'getRealPath' => null,
                'getMimeType' => $mime,
                'store' => $fullPathToFile,
                'storeAs' => $fullPathToFile,
                'storePublicly' => $fullPathToFile,
                'storePubliclyAs' => $fullPathToFile
            ]
        );
    }
}