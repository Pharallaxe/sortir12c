<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorController extends AbstractController
{
    public function show(Request $request, Throwable $exception): Response
    {
        $connecte = $this->getUser() ? true : false;
        $statusCode = $exception instanceof HttpExceptionInterface ?
            $exception->getStatusCode() :
            Response::HTTP_INTERNAL_SERVER_ERROR;

        $path = "bundles/TwigBundle/Exception/error.html.twig";
        $message = $exception->getMessage();

//        if ($statusCode === 404) {
//            $path = 'bundles/TwigBundle/Exception/error404.html.twig';
//        }

        return $this->render($path, [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode],
            'message' => $message,
            'connecte' => $connecte
        ]);
    }
}
