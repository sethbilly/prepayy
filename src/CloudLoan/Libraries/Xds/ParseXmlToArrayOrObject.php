<?php
namespace CloudLoan\Libraries\Xds;


trait ParseXmlToArrayOrObject
{
    /**
     * @param string $xml
     * @return \stdClass
     */
    public function parseXmlToObject(string $xml)
    {
        $xmlString = simplexml_load_string($xml);
        $json = json_encode($xmlString);

        return json_decode($json);
    }

    /**
     * @param string $xml
     * @return array
     */
    public function parseXmlToArray(string $xml)
    {
        $xmlString = simplexml_load_string($xml);
        $json = json_encode($xmlString);

        return json_decode($json, true);
    }
}