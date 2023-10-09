<?php
namespace App\Controller;


use App\Entity\Game;
use App\Repository\GameRepository;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Game controller
 */

class GameController extends AbstractController
{
  /**
   * #[Route('/admin', name: 'admin')]
   *
   */
    public function index(EntityManagerInterface $entityManager)
    {
        $em = $this->getDoctrine()->getManager();

        $games =  $entityManager->getRepository(Game::class)->findAll();

        return $this->render('admin/index.html.twig', array(
            'games' => $games,
        ));
    }

    /**
     * #[Route('/new', name: 'admin_new')]
     *
     */
    public function new(Request $request)
    {
        $game = new Game();
        $form = $this->createForm('App\Form\GameType', $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($game);
            $em->flush();

            return $this->redirectToRoute('admin_show', array('id' => $game->getId()));
        }

        return $this->render('admin/new.html.twig', array(
            'game' => $game,
            'form' => $form->createView(),
        ));
    }

    public function show($id,EntityManagerInterface $entityManager)
    {
        $game =  $entityManager->getRepository(Game::class)->findOneById($id);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('admin/show.html.twig', array(
            'game' => $game,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function edit($id, EntityManagerInterface $entityManager, Request $request)
    {

        $game =  $entityManager->getRepository(Game::class)->findOneById($id);
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm('App\Form\GameType', $game);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_edit', array('id' => $game->getId()));
        }

        return $this->render('admin/edit.html.twig', array(
            'game' => $game,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function delete($id, EntityManagerInterface $entityManager, Request $request)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $game =  $entityManager->getRepository(Game::class)->findOneById($id);
            $em = $this->getDoctrine()->getManager();
            $em->remove($game);
            $em->flush();
        }

        return $this->redirectToRoute('admin_index');
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_delete', array('id' => $id)))
            ->getForm()
        ;
    }
}
