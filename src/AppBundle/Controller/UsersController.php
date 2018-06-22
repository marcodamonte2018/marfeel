<?php

namespace AppBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Sunra\PhpSimple\HtmlDomParser;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

use AppBundle\Entity\Site;

class UsersController
{
    private $repository;
    private $entityManager;

    #Region construct
    public function __construct(ContainerInterface $container)
    {
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->repository    = $this->entityManager->getRepository("AppBundle\Entity\Site");
    }

    public function getUsersAction()
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

    public function extractKeywords($string, $keywords){ // @TODO: mover a un helper
        $string = preg_replace('/\s\s+/i', '', $string); // replace whitespace
        $string = trim($string); // trim the string
        $string = preg_replace('/[^a-zA-Z0-9 -]/', '', $string); // only take alphanumerical characters, but keep the spaces and dashes too…
        $string = strtolower($string); // make it lowercase

        preg_match_all('/\b.*?\b/i', $string, $matchWords);
        $matchWords = $matchWords[0];

        foreach ( $matchWords as $key=>$item ) {
            if ( $item == '' || in_array(strtolower($item), $keywords) || strlen($item) <= 3 ) {
                unset($matchWords[$key]);
            }
        }
        $wordCountArr = array();
        if ( is_array($matchWords) ) {
            foreach ( $matchWords as $key => $val ) {
                $val = strtolower($val);
                if ( isset($wordCountArr[$val]) ) {
                    $wordCountArr[$val]++;
                } else {
                    $wordCountArr[$val] = 1;
                }
            }
        }
        arsort($wordCountArr);
        $wordCountArr = array_slice($wordCountArr, 0, 10);
        return $wordCountArr;
    }

}