<?php

namespace Noorfarooqy\Salaamch\Controllers;

use OpenApi\Attributes as OA;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    #[OA\Get(path: '/data.json')]
    #[OA\Response(response: '200', description: 'The data')]
    public function getResource()
    {
        // ...
    }
}
