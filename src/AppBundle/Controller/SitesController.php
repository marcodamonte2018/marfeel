<?php

namespace AppBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp\Exception\ConnectException;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

use AppBundle\Entity\Site;

class SitesController
{
    private $repository;
    private $entityManager;
    private $goutteClient;
    private $keywords;

    public function __construct(ContainerInterface $container)
    {
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->repository    = $this->entityManager->getRepository("AppBundle\Entity\Site");

        // setea web crawler
        $this->goutteClient = new Client();
        $guzzleClient = new GuzzleClient([ 'verify' => false, 'exceptions' => false, 'timeout' => 20 ]);

        $this->goutteClient->setClient($guzzleClient);
    }

    public function getSitesAction()
    {
        // keywords a buscar
        $this->keywords = array("news", "noticias"); // @TODO: mover al config de la aplicación

        $json_file = file_get_contents("sites.json");
        $sites = json_decode($json_file, true);

        foreach($sites as $jsonSite) {
            $isMarfeeable = $this->isMarfeeable($jsonSite['url']);

            // persisto el sitio
            // @todo agregar funcion al repositorio
            $site = new Site();
            $site->setUrl($jsonSite['url']);
            $site->setIsmarfeeable($isMarfeeable);
            $this->entityManager->persist($site);
            $this->entityManager->flush();
        }
    }

    /**
     * @param $url
     * @return bool
     *
     * Devuelve true si el URL es Marfeeable
     *
     */
    private function isMarfeeable($url) {
        try {
            $crawler = $this->goutteClient->request('GET', $this->addHttp($url));
        } catch (ConnectException $e) {
            return false;
        }

        // parsea todos los tags "title" de la respuesta y busca las keywords
        // @TODO: es redundante porque title debería aparecer solo una vez...
        $keywordsFound = 0;
        foreach($crawler->filter('title') as $title) {
            $keywordsFound += $this->countKeywords($title->nodeValue, $this->keywords);
        }

        // se encontraron las keywords?
        if($keywordsFound > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $string
     * @param $keywords
     * @return bool
     *
     * Devuelve la cuenta de apariciones de keywords dentro de un string.
     */
    public function countKeywords($string, $keywords){ // @TODO: mover a un helper
        $keywordsFound = 0;
        foreach($keywords as $keyword) {
            if (strpos(strtolower($string), strtolower($keyword)) !== false) {
                $keywordsFound++;
            }
        }
        return $keywordsFound;
    }

    private function addhttp($url) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }

}
