<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MobileDetectBundle\DeviceDetector\MobileDetectorInterface;

use Doctrine\ORM\EntityManagerInterface;

class VideoGameController extends AbstractController
{

    /**
     * #[Route('/', name: 'videogame')]
     *
     */
    public function index(EntityManagerInterface $entityManager): Response
     {
      $detect = new \Detection\MobileDetect;
      $isMobile = $detect->isMobile();
      $debug = $this->getParameter('debug');
      if($isMobile && !$debug){
        return $this->redirectToRoute('videogame_mobile');
      }

      $gamesRepo = $entityManager->getRepository(Game::class);
      $games = $gamesRepo->findBy(
        array(),
        array('date' => 'DESC')
      );
      return $this->render('VideoGame/index.html.twig',
              array("games" => $games));
    }

    /**
     *
     */
    public function indexMobile(Request $request) {
      $debug = $this->getParameter('debug');
       $detect = new \Detection\MobileDetect;
       $isMobile = $detect->isMobile();
      if(!$isMobile && !$debug){
          return $this->redirectToRoute('videogame');
      }
      $em = $this->getDoctrine()->getManager();
      $navigator = $request->get('navigator');
      $games = $em->getRepository(Game::class)->findBy(
        array(),
        array('date' => 'DESC')
        );

      return $this->render('VideoGame/indexMobile.html.twig', ["games" => $games,"navigator" => 0]);
    }

    /**
     *
     */
    public function aPropos() {
      $em = $this->getDoctrine()->getManager();

      $games = $em->getRepository(Game::class)->findBy(
        array(),
        array('date' => 'DESC')
        );
      return $this->render('VideoGame/aPropos.html.twig', ["games" => $games ]);
    }

    /**
     * #[Route('/game/{idName}', name: 'game')]
     *
     */
    public function game(Request $request, $idName) {
      $debug = $this->getParameter('debug');
       $detect = new \Detection\MobileDetect;
       $isMobile = $detect->isMobile();
      if($isMobile && !$debug){
          $texte = $request->query->get("texte");
          if($texte){
              return $this->redirect($this->generateUrl('mobile_game',array('idName' => $idName))."?texte=".urlencode($texte));
          }
          return $this->redirectToRoute('mobile_game',array('idName' => $idName));
      }
      $em = $this->getDoctrine()->getManager();
      $game = $em->getRepository(Game::class)->findOneByShortName($idName);
      $game_json = json_decode(file_get_contents($this->getParameter('kernel.project_dir') . '/templates/Games/'.$game->getShortName().'.json'));
      $idsVideos = array();
      $urlgame = (property_exists($game_json, 'urlgame'))? $game_json->urlgame : null;
      foreach ($game_json->scenario as $sequence) {
        $idsVideos[$sequence->id] = $sequence->id;
      }
      if(isset($game_json->gameovers))
      {
        for ($i = $game_json->gameovers->idmin; $i <= $game_json->gameovers->idmax; $i++) {
          $idsVideos[sprintf("%03d",$i)] = sprintf("%03d",$i);
        }
      }
      ksort($idsVideos);
      $this->setCookies($request,$game);
      return $this->render('VideoGame/game.html.twig', ['game' => $game,  "idsVideos" => $idsVideos, "urlgame" => $urlgame, 'game_json' => $game_json, 'cookieVal' => $this->cookieVal,'cookieVidVal' => $this->cookieVidVal]);
    }

    /**
     *
     */
    public function mobileGame(Request $request, $idName) {
      $debug = $this->getParameter('debug');

       $detect = new \Detection\MobileDetect;
       $isMobile = $detect->isMobile();
      if(!$isMobile && !$debug){
        $texte = $request->query->get("texte");
        if($texte){
            return $this->redirect($this->generateUrl('game',array('idName' => $idName))."?texte=".urlencode($texte));
        }
        return $this->redirectToRoute('game',array('idName' => $idName));
      }
      $em = $this->getDoctrine()->getManager();
      $game = $em->getRepository(Game::class)->findOneByShortName($idName);
      $game_json = json_decode(file_get_contents($this->getParameter('kernel.project_dir') . '/templates/Games/'.$game->getShortName().'.json'));
      $idsVideos = array();
      $urlgame = (property_exists($game_json, 'urlgame'))? $game_json->urlgame : null;
      foreach ($game_json->scenario as $sequence) {
        $idsVideos[$sequence->id] = $sequence->id;
      }
      if(isset($game_json->gameovers))
      {
        for ($i = $game_json->gameovers->idmin; $i <= $game_json->gameovers->idmax; $i++) {
          $idsVideos[sprintf("%03d",$i)] = sprintf("%03d",$i);
        }
      }
      ksort($idsVideos);
      $this->setCookies($request,$game);
      return $this->render('VideoGame/mobileGame.html.twig', ['game' => $game, "idsVideos" => $idsVideos,"urlgame" => $urlgame, 'game_json' => $game_json, 'cookieVal' => $this->cookieVal,'cookieVidVal' => $this->cookieVidVal]);
    }

    private function setCookies(Request $request,$game){
      $this->cookieVal = $request->cookies->get($game->getName());
      $this->cookieVidVal = ($this->cookieVal)? $request->cookies->get($cookieVal) : '';
    }

}
