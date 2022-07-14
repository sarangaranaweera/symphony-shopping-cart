<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MainController
{
   
    #[Route('/all')]

    public function all(): Response
    {
        return new Response('All products');
    }
}