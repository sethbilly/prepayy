<?php
namespace CloudLoan\Exceptions;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 13/01/2017
 * Time: 16:16
 */
class ConflictWithExistingRecord extends \InvalidArgumentException
{
    /**
     * @param Model $model
     * @param int $code
     * @param \Exception|null $exception
     * @return ConflictWithExistingRecord
     */
    public static function fromModel(
        Model $model,
        $message = null,
        $code = 422,
        \Exception $exception = null
    ): ConflictWithExistingRecord {
        if (empty($message)) {
            $message = sprintf('Conflict with an existing %s record', \strtolower(class_basename($model)));
        }

        // Use static to ensure that this class is returned from this method even if extended by another class
        return new static($message, $code, $exception);
    }
}