<?php

namespace App\Controller;

use App\Form\SecretSantaFormType;
use Exception;

use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class defaultController extends AbstractController
{

    /**
     * @throws Exception
     */
    #[Route('/')]
    public function defaultAction(Request $request): Response
    {
        return $this->redirectToRoute('app_secretsanta_default');
    }
}