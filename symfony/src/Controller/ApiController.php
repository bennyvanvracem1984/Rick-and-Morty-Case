<?php

namespace App\Controller;

use App\Service\RickAndMortyService;
use NickBeen\RickAndMortyPhpApi\Character;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function dd;

class ApiController extends AbstractController
{
    public function __construct(
        private RickAndMortyService $rickAndMortyService
    )
    {
    }

    #[Route('/test')]
    public function test(): Response
    {
        dd($this->rickAndMortyService->getAllCharactersByDimension('unknown'));

        foreach ($characters->get()->results as $character) {
            echo $character->name;
        }
        dd('banana');
    }

    #[Route('/lucky/number')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }
}
