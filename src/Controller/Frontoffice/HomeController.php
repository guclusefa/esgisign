<?php

namespace App\Controller\Frontoffice;

use App\Constants\RouteConstants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct
    (
    )
    {
    }

    #[Route('', name: RouteConstants::FRONTOFFICE_HOME)]
    public function index(): Response
    {

        return $this->render('views/frontoffice/home/index.html.twig', [
        ]);
    }
}
