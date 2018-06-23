<?php

namespace AppBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Sunra\PhpSimple\HtmlDomParser;
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
        $guzzleClient = new GuzzleClient(array(
            'timeout' => 60,
        ));

        $this->goutteClient->setClient($guzzleClient);
    }

    public function getSitesAction()
    {
        // keywords a buscar
        $this->keywords = array("news", "noticias"); // @TODO: mover al config de la aplicación

        $json_file = file_get_contents("sites.json");

        $sites = json_decode($json_file, true);
        foreach($sites as $site) {
            if($this->isMarfeeable($site['url'])) {
                // agrego a la base de datos
                
            }
        }

        die;
    }

    /**
     * @param $url
     * @return bool
     *
     * Devuelve true si el URL es Marfeeable
     *
     */
    private function isMarfeeable($url) {
        $crawler = $this->goutteClient->request('GET', $url);

        $keywordsFound = 0;
        // parsea todos los tags "title" de la respuesta y busca las keywords
        // @TODO: es redundante porque title debería aparecer solo una vez...
        foreach($crawler->filter('title') as $title) {
            $keywordsFound += $this->countKeywords($title->nodeValue, $this->keywords);
        }

        // se encontraron las keywords?
        if(count($keywordsFound) > 0) {
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

}
