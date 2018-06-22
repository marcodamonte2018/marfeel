<?php

namespace AppBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Sunra\PhpSimple\HtmlDomParser;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

use AppBundle\Entity\Site;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SiteController extends Controller
{
    /**
     * @Route("/Submit")
     */
    public function SubmitAction()
    {
        // keywords a buscar
        $keywords = array("mundial", "rusia"); // @TODO: mover al config

        // setea web crawler
        $goutteClient = new Client();
        $guzzleClient = new GuzzleClient(array(
            'timeout' => 60,
        ));

        $goutteClient->setClient($guzzleClient);

        // hace request
        $crawler = $goutteClient->request('GET', 'https://www.infobae.com');

        // parsea todos los tags "title" de la respuesta y busca las keywords
        foreach($crawler->filter('title') as $title) {
            $result = $this->extractKeywords($title->nodeValue, $keywords);
        }

        // se encontraron las keywords?
        if(count($result) > 0) {
            $site = new Site();
            $this->entityManager->persist($site);
            $this->entityManager->flush();
        } else {

        }
    }

}
