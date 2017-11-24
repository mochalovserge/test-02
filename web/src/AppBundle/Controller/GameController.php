<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GameStateStore;
use AppBundle\Entity\Winner;
use AppBundle\Game\Game;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class GameController
 * @package AppBundle\Controller
 * @author Mochalov Sergey <mochalov.serge@gmail.com>
 */
class GameController extends FOSRestController
{
    /**
     * @Rest\Get("/api/game", name="game_page")
     *
     * @param Request $request
     * @return Response
     */
    public function gameAction(Request $request)
    {
        $game = $this->getGameInstance($request);

        return $this->handleView($this->view([
            'clicks' => $game->getClickCount(),
            'square' => $game->getMatrix(),
            'state' => $game->getState(),
        ], 200));
    }

    /**
     * @Rest\Get("/api/game/state", name="game_api_get_state")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function gameStateAction(Request $request)
    {
        $game = $this->getGameInstance($request);
        $name = $request->getSession()->get('name', '');
        $best_result = 0;

        /** @var Winner $winner */
        $winner = $this->getDoctrine()
            ->getRepository(Winner::class)
            ->findOneByName($name);

        if ($winner) {
            $best_result = $winner->getClickCount();
        }

        return $this->handleView($this->view([
            'best_result' => $best_result,
            'clicks' => $game->getClickCount(),
            'state' => $game->getState(),
            'name' => $name,
        ], 200));
    }

    /**
     * @Rest\Post("/api/game", name="game_api_click")
     *
     * @param Request $request
     * @return Response
     */
    public function clickAction(Request $request)
    {
        $row = $request->get('row');
        $col = $request->get('col');

        $game = $this->getGameInstance($request);
        $game->setClick($row, $col);

        return $this->handleView($this->view([
            'message' => 'Success'
        ], 200));
    }

    /**
     * @Rest\Delete("/api/game", name="game_api_refresh")
     *
     * @param Request $request
     * @return Response
     */
    public function refreshAction(Request $request)
    {
        /** @var GameStateStore $gameStateStore */
        $gameStateStore = $this->getDoctrine()
            ->getRepository(GameStateStore::class)
            ->findOneBySession($request->cookies->get('game_id'));

        if ($gameStateStore) {
            $dm = $this->getDoctrine()->getManager();
            $dm->remove($gameStateStore);
            $dm->flush();
        }

        $session = $request->getSession();
        if ($session->has('game')) {
            $session->remove('game');
        }

        return $this->handleView($this->view([
            'message' => 'The game is refreshed',
        ], 200));
    }

    /**
     * @Rest\Get("/api/game/winners", name="game_api_get_winners")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function winnersAction()
    {
        $winners = $this->getDoctrine()
            ->getRepository(Winner::class)
            ->findBy([], [
                'click_count' => 'ASC'
            ], 10);

        return $this->handleView($this->view([
            'data' => $winners
        ], 200));
    }

    /**
     * @Rest\Post("/api/game/save", name="game_api_save_game")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveGameAction(Request $request)
    {
        $game = $this->getGameInstance($request);
        $session = $request->getSession();

        $cookie = new Cookie('game_id', $session->getId(), strtotime('now + 60 minutes'));
        $response = new Response();
        $response->headers->setCookie($cookie);
        // save game state

        /** @var GameStateStore $gameStateStore */
        $gameStateStore = $this->getDoctrine()
            ->getRepository(GameStateStore::class)
            ->findOneBySession($session->getId());
        if (!$gameStateStore) {
            $gameStateStore = new GameStateStore();
        }

        $gameStateStore->setSession($session->getId());
        $gameStateStore->setInstance($game);

        $em = $this->getDoctrine()->getManager();

        if (!$gameStateStore->getId()) {
            $em->persist($gameStateStore);
        } else {
            $em->merge($gameStateStore);
        }
        $em->flush();

        return $this->handleView($this->view([
            'message' => 'Success'
        ], 200, $response->headers->all()));
    }

    /**
     * @Rest\Post("/api/game/winners", name="game_api_set_winner")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addWinnerAction(Request $request)
    {
        $game = $this->getGameInstance($request);

        if (!$game->getState()) {
            return $this->handleView($this->view([
                'error' => 'Игра не закончена'
            ], 400));
        }

        $winner = $this->getDoctrine()
            ->getRepository(Winner::class)
            ->findOneByName($request->get('name'));

        if (!$winner) {
            $winner = new Winner();
        }

        $winner->setName($request->get('name'));
        $winner->setClickCount($game->getClickCount());

        $request->getSession()->set('name', $winner->getName());

        $validator = $this->get('validator');
        $errors = $validator->validate($winner);

        if (count($errors)) {
            return $this->handleView($this->view([
                'errors' => $errors
            ], 400));
        }

        $em = $this->getDoctrine()->getManager();
        if (!$winner->getId()) {
            $em->persist($winner);
        } else {
            $em->merge($winner);
        }

        $em->flush();

        return $this->handleView($this->view([
            'message' => 'Success'
        ], 200));
    }

    /**
     * @param Request $request
     * @return Game
     */
    private function getGameInstance(Request $request)
    {
        $session = $request->getSession();
        if (!($game = $session->get('game'))) {

            /** @var GameStateStore $gameStateStore */
            $gameStateStore = $this->getDoctrine()
                ->getRepository(GameStateStore::class)
                ->findOneBySession($request->cookies->get('game_id'));

            if ($gameStateStore) {
                $game = $gameStateStore->getInstance();
            } else {

                $game = new Game();
                $game->initMatrix();
            }

            $session->set('game', $game);
        }

        return $session->get('game');
    }
}
