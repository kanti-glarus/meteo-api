<?php

namespace App\Meteo;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HelpController
{
    public function showStartPage(Request $request, Response $response) {
        $response->getBody()->write(file_get_contents(dirname(__FILE__) . '../../../view/index.html'));
        return $response;
    }
}