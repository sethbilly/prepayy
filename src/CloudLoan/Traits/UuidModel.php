<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 13/09/2016
 * Time: 09:38
 */

namespace CloudLoan\Traits;

use Ramsey\Uuid\Uuid;

trait UuidModel
{
    /**
     * UUID4 pattern
     * @var string
     */
    static $uuid4Pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    /**
     * Name of the field used to store the uuid
     * @return string
     */
    private static function getUuidFieldName()
    {
        return 'uuid';
    }

    /**
     * Helper for creating uuid for records
     * @param $model
     */
    private static function createUuid($model)
    {
        // Get the primary key field of this model
        $uuidField = self::getUuidFieldName();

        do {
            // Ensure unique uuids
            $uuid = Uuid::uuid4()->toString();

            if (!static::where($uuidField, $uuid)->first()) {
                break;
            }
        } while (true);

        $model->attributes[$uuidField] = $uuid;
    }

    /**
     * Returns true if the model has a valid uuid4
     * NB: This method cannot be used outside its containing class. This is because attributes field of $model is
     * a protected field
     * @param $model
     * @return boolean
     */
    private static function hasValidUuid4($model)
    {
        $uuidField = self::getUuidFieldName();

        $id = array_key_exists($uuidField, $model->attributes) ? $model->attributes[$uuidField] : null;

        return (bool)($id && preg_match(self::$uuid4Pattern, $id));
    }

    /**
     * Binds creating/saving events to create UUIDs (and also prevent them from being overwritten).
     *
     * @return void
     */
    public static function bootUuidModel()
    {
        // Use late static binding to resolve the name of the class at runtime
        static::creating(function ($model) {
            // If no valid uuid has been set, add one for the record
            if (!self::hasValidUuid4($model)) {
                self::createUuid($model);
            }
        });

        static::saving(function ($model) {
            // Skip if a valid uuid has been set for the record
            if (self::hasValidUuid4($model)) {
                return;
            }

            // Get the primary key field of this model
            $uuidField = self::getUuidFieldName();

            // What's that, trying to change the UUID huh?  Nope, not gonna happen.
            $original_uuid = $model->getOriginal($uuidField);

            // Add uuid for records that don't have them
            if (empty($original_uuid)) {
                self::createUuid($model);
            } else if ($original_uuid !== $model->uuid) {
                $model->attributes[$uuidField] = $original_uuid;
            }
        });
    }

    /**
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return self::getUuidFieldName();
    }
}