<?php

use App\Entities\FileEntry;
use App\Entities\FinancialInstitution;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FileEntryTest extends TestCase
{
    use DatabaseTransactions, InvokePrivateFieldsAndMethodsTrait;

    /**
     * @var FileEntry
     */
    private $file;

    public function setUp()
    {
        parent::setUp();

        $institution = factory(FinancialInstitution::class)->create();
        $this->file = factory(FileEntry::class)->create([
            'bucket' => null,
            'fileable_id' => $institution->id,
            'fileable_type' => $institution->getMorphClass()
        ]);
    }

    public function testCanGetLocalFileUrl()
    {
        $this->file->update(['storage_driver' => 'public']);

        $expectedUrl = asset('storage/' . $this->file->filename . '?v=' . $this->file->created_at->getTimestamp());
        $this->assertEquals($expectedUrl, $this->file->url);
    }

    public function testCanGetCloudFileUrl()
    {
        // Generate s3 url and assert it meets the specifications required
        $actualUrl = $this->invokeMethod($this->file, 'getS3SignedUrl', [
            'filePath' => $this->file->filename,
            'storageDriver' => 's3'
        ]);

        $this->assertNotEmpty($this->file->url, 's3 presigned url must not be empty');
        $this->assertContains($this->file->filename, $actualUrl);
        $this->assertContains('X-Amz-Expires=3600', $actualUrl);
    }

    public function testCanGetStorageDriver()
    {
        // APP_ENV is set to local in phpunit.xml configuration
        $this->assertEquals('public', FileEntry::getStorageDriver());
    }

    public function testCanGetFilePath()
    {
        // File path
        $this->assertEquals($this->file->filename, $this->file->getPathRelativeToBucket());

        // File path with bucket or directory prefix
        $this->file->update(['bucket' => 'providers']);
        $this->file = $this->file->fresh();

        $this->assertEquals('providers/' . $this->file->filename, $this->file->getPathRelativeToBucket());
    }

    public function testCanGetFileOwner()
    {
        $this->assertInstanceOf(FinancialInstitution::class, $this->file->fileable);
    }

    public function testChecksIfFilePathExists()
    {
        // The file is not physically present on disk
        $this->assertFalse($this->file->filePathExists());

        // Upload the running file to the storage directory and assert that the file path exists
        $fileInfo = pathinfo(__FILE__);

        if (!file_exists(storage_path('app/public/' . $fileInfo['basename']))) {
            copy(__FILE__, storage_path('app/public/' . $fileInfo['basename']));
        }

        $this->file->update([
            'filename' => $fileInfo['basename'],
            'bucket' => null
        ]);

        $this->file = $this->file->fresh();
        $this->assertTrue($this->file->filePathExists());

        // Delete the copied file
        unlink(storage_path('app/public/' . $fileInfo['basename']));
    }

    public function testCanDeleteFile()
    {
        // Upload the running file to the storage directory and assert that the file path exists
        $fileInfo = pathinfo(__FILE__);

        if (!file_exists(storage_path('app/public/' . $fileInfo['basename']))) {
            copy(__FILE__, storage_path('app/public/' . $fileInfo['basename']));
        }

        $this->file->update([
            'filename' => $fileInfo['basename'],
            'bucket' => null
        ]);

        $this->assertTrue($this->file->filePathExists());
        $this->assertTrue($this->file->delete());
        $this->assertFalse($this->file->filePathExists());
    }
}
