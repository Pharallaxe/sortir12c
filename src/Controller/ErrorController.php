<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

// Contrôleur pour les pages d'erreur
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

        //En cas d'erreur 404, on affiche une page dédiée
//        if ($statusCode === 404) {
//            $path = 'bundles/TwigBundle/Exception/error404.html.twig';
//        }

        //Pour les autres erreurs, on affiche une page générique
        return $this->render($path, [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode],
            'message' => $message,
            'connecte' => $connecte
        ]);
    }
}
