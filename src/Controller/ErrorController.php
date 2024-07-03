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
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;


        //En cas d'erreur 404, on affiche une page dédiée
        if ($statusCode === 404) {
            return $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
                'message' => $exception->getMessage(),
            ]);
        }

        //Pour les autres erreurs, on affiche une page générique
        return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode],
            'message' => $exception->getMessage(),
        ]);
    }
}
