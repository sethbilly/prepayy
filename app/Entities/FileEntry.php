<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class FileEntry extends Model
{
    /**
     * Name of the database table
     * @var string
     */
    protected $table = 'file_entries';

    /**
     * Mass assignable fields of this class
     * @var array
     */
    protected $fillable = [
        'filename', 'mime', 'original_filename', 'bucket', 'fileable_id', 'fileable_type', 'description'
    ];

    /**
     * Upload directory for profile photos of users
     */
    const AVATAR_UPLOAD_DIRECTORY = 'avatars';
    /**
     * Upload directory for loan documents
     */
    const LOAN_DOCUMENTS_DIRECTORY = 'documents';

    /**
     * Returns the list of owner models
     * @return MorphTo
     */
    public function fileable()
    {
        return $this->morphTo();
    }

    /**
     * Get the cache key
     * @param FileEntry $fileEntry
     * @return string
     */
    private static function getCacheKey(FileEntry $fileEntry)
    {
        return strtolower(class_basename(self::class) . ':' . $fileEntry->id);
    }

    /**
     * Delete the cached url
     * @param FileEntry $fileEntry
     * @return mixed
     * @throws \Exception
     */
    public static function deleteCachedUrl(FileEntry $fileEntry)
    {
        return cache()->forget(self::getCacheKey($fileEntry));
    }

    /**
     * Stores the file url in the cache, if not cached, and returns the file url
     * @param FileEntry $fileEntry
     * @return string the file url
     * @throws \Exception
     */
    public static function getCachedUrl(FileEntry $fileEntry) {
        $cacheKey = self::getCacheKey($fileEntry);

        return cache()->remember($cacheKey, 60, function() use ($fileEntry) {
            return $fileEntry->getFileUrl($fileEntry->getPathRelativeToBucket());
        });
    }

    /**
     * Set the url attribute
     * @return string
     */
    public function getUrlAttribute()
    {
        // NB: Files, specifically in cloud storage are not publicly accessible
        // Pre-signed urls are generated which make the files accessible to authenticated users
        // The urls are cached while valid
        $key = 'url';
        $cacheKey = self::getCacheKey($this);

        // Regenerate a new file url if the cached url has expired
        if (!cache()->has($cacheKey)) {
            unset($this->attributes[$key]);
        }

        if (!array_key_exists($key, $this->attributes)) {
            // Cache file urls for 1 hour
            $this->attributes[$key] = self::getCachedUrl($this);
        }

        return $this->attributes[$key];
    }

    /**
     * @param $filePath
     * @return string
     */
    private function getFileUrl($filePath): string
    {
        // Append created at timestamps to allow re-use of filenames
        switch (self::getStorageDriver()) {
            case 's3':
                return $this->getS3SignedUrl($filePath, self::getStorageDriver());
            default:
                // A shortcut exists in the public directory linking to files uploaded to the storage directory
                return asset('storage/' . $filePath) . '?v=' . $this->created_at->getTimestamp();
        }
    }

    /**
     * @param $filePath
     * @param $storageDriver
     * @return string
     */
    private function getS3SignedUrl($filePath, $storageDriver = 's3'): string
    {
        $client = Storage::disk($storageDriver)->getDriver()->getAdapter()->getClient();
        $command = $client->getCommand('GetObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $filePath
        ]);
        $expiry = "+60" . " minutes";

        return (string) $client->createPresignedRequest($command, $expiry)->getUri();
    }

    /**
     * Returns the file path relative to the bucket/directory in which it is stored.
     * For example: if bucket = providers and filename is kofi.jpg, then path is providers/kofi.jpg
     * if bucket is null, path is kofi.jpg
     * @return string
     */
    public function getPathRelativeToBucket(): string
    {
        return $this->bucket ? $this->bucket . '/' . $this->filename : $this->filename;
    }

    /**
     * Get the storage driver
     * @return string
     */
    public static function getStorageDriver(): string
    {
        return app()->environment('production') ? config('filesystems.cloud') : config('filesystems.default');
    }

    /**
     * Returns true if the file path exists
     * @return bool
     */
    public function filePathExists(): bool
    {
        return Storage::disk($this->getStorageDriver())->exists($this->getPathRelativeToBucket());
    }

    /**
     * @return bool
     * @override
     */
    public function delete(): bool
    {
        if ($this->filePathExists()) {
            Storage::disk($this->getStorageDriver())->delete($this->getPathRelativeToBucket());
        }
        return parent::delete();
    }
}
