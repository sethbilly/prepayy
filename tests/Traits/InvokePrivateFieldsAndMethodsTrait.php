<?php
/**
 * Class InvokePrivateFieldsAndMethodsTrait
 *
 * This trait is provided for use when testing other classes and should not be used otherwise.
 * When testing a class which has a few public methods and lots of private helper methods, it is useful
 * to be able to test the private methods in isolation for proper behaviour. With the private methods
 * fully tested, the tests for the public methods can then focus solely on the logic of those methods.
 * NB: This class is meant for use in application testing only.
 *
 * @package Qls\Traits
 */
trait InvokePrivateFieldsAndMethodsTrait
{
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, string $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param object $object Instantiated object from which to retrieve property
     * @param string $fieldName    Property to retrieve
     * @return mixed
     */
    public function getPrivateProperties(&$object, string $fieldName) {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($fieldName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @param object $object
     * @param string $fieldName
     * @param mixed $value
     */
    public function setPrivateProperty(&$object, string $fieldName, $value) {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($fieldName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}