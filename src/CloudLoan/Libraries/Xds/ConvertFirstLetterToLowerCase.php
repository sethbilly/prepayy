<?php
/**
 * Created by PhpStorm.
 * User: kwabena
 * Date: 11/20/17
 * Time: 6:44 AM
 */

namespace CloudLoan\Libraries\Xds;


trait ConvertFirstLetterToLowerCase
{
    /**
     * Returns new array with first letter of each key converted to lowercase
     * @param array $result
     * @return array
     */
    public function convertFirstLetterOfArrayKeyToLowerCase(array $result): array
    {
        $newResult = [];

        foreach ($result as $key => $value) {
            $newKey = lcfirst($key);

            $newResult[$newKey] = $value;
        }

        return $newResult;
    }

    /**
     * Returns a new object with each the first letter of each property converted to
     * lowercase
     * @param \stdClass $obj
     * @return \stdClass
     */
    public function convertFirstLetterOfObjectPropertyToLowerCase(\stdClass $obj)
    {
        $newObj = new \stdClass();

        foreach ($obj as $prop => $value) {
            $newProp = lcfirst($prop);

            $newObj->{$newProp} = $value;
        }

        return $newObj;
    }
}