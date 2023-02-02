<?php

namespace App\Http\Controllers;

abstract class JsonResponse extends \Illuminate\Http\JsonResponse
{
    public function __construct($data = null, $status = 200)
    {
        parent::__construct(
            data: $data,
            status: $status,
            options: JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | self::DEFAULT_ENCODING_OPTIONS
        );
    }
}
