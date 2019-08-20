<?php
/**
 * Created by PhpStorm.
 * User: kwabena
 * Date: 11/20/17
 * Time: 7:53 PM
 */

namespace CloudLoan\Libraries\Xds;


class ConsumerMatchNotFoundException extends \RuntimeException
{
    public function __construct(
        ConsumerMatchRequest $request = null,
        $code = 404,
        \Exception $previous = null
    ) {
        $message = $request ?
            "Consumer ({$request->getConsumerName()}) credit report was not found" :
            'Consumer credit report was not found';

        parent::__construct($message, $code, $previous);
    }
}