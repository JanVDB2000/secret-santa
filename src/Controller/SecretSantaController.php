<?php

namespace App\Controller;

use App\Form\SecretSantaFormType;
use Exception;

use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecretSantaController extends AbstractController
{

    /**
     * @throws Exception
     */
    #[Route('/secret-santa')]
    public function defaultAction(Request $request): Response
    {
        $form = $this->createForm(SecretSantaFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->render('secret-santa/results.html.twig', [
                'matchedParticipants' => $this->createGiftCircle($form->getData()['participants']),
            ]);
        }
        return $this->render('secret-santa/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function createGiftCircle(array $deelnemers): array
    {
        // Controleer of elke deelnemer een 'name' key heeft
        foreach ($deelnemers as $deelnemer) {
            if (!isset($deelnemer['name'])) {
                throw new InvalidArgumentException("Elke deelnemer moet een ' name ' key hebben.");
            }
        }

        $geschuddeDeelnemers = $deelnemers;
        shuffle($geschuddeDeelnemers);

        $aantal = count($deelnemers);
        $gematchteDeelnemers = [];

        // Bepaal of een ontvanger al is toegewezen
        $toegewezenOntvangers = [];

        for ($i = 0; $i < $aantal; $i++) {
            $huidigeDeelnemer = $deelnemers[$i];
            $volgendeDeelnemerIndex = ($i + 1) % $aantal;

            // Zorg ervoor dat de laatste deelnemer niet wordt gematcht met de eerste deelnemer
            if ($volgendeDeelnemerIndex === 0 && $i === $aantal - 1) {
                $volgendeDeelnemerIndex = ($volgendeDeelnemerIndex + 1) % $aantal;
            }

            $volgendeDeelnemer = $geschuddeDeelnemers[$volgendeDeelnemerIndex];

            // Controleer of de huidige deelnemer niet dezelfde is als de volgende deelnemer
            if ($huidigeDeelnemer['name'] !== $volgendeDeelnemer['name']) {
                // Controleer of de ontvanger al is toegewezen aan een andere gever
                while (
                    in_array($volgendeDeelnemer['name'], $toegewezenOntvangers) ||
                    $volgendeDeelnemer['name'] === $huidigeDeelnemer['name']
                ) {
                    $volgendeDeelnemerIndex = ($volgendeDeelnemerIndex + 1) % $aantal;
                    $volgendeDeelnemer = $geschuddeDeelnemers[$volgendeDeelnemerIndex];
                }

                $gematchteDeelnemers[$huidigeDeelnemer['name']] = $volgendeDeelnemer['name'];
                $toegewezenOntvangers[] = $volgendeDeelnemer['name'];
            } else {
                // Als de huidige deelnemer hetzelfde is als de volgende, zoek dan een andere match
                for ($j = 0; $j < $aantal; $j++) {
                    $volgendeDeelnemerIndex = ($volgendeDeelnemerIndex + 1) % $aantal;
                    $volgendeDeelnemer = $geschuddeDeelnemers[$volgendeDeelnemerIndex];

                    if (
                        $huidigeDeelnemer['name'] !== $volgendeDeelnemer['name'] &&
                        !in_array($volgendeDeelnemer['name'], $toegewezenOntvangers) &&
                        $volgendeDeelnemer['name'] !== $huidigeDeelnemer['name']
                    ) {
                        $gematchteDeelnemers[$huidigeDeelnemer['name']] = $volgendeDeelnemer['name'];
                        $toegewezenOntvangers[] = $volgendeDeelnemer['name'];
                        break;
                    }
                }
            }
        }

        return $gematchteDeelnemers;
    }
}