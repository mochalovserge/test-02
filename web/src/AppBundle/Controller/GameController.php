<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Winner;
use AppBundle\Game\Game;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class GameController
 * @package AppBundle\Controller
 * @author Mochalov Sergey <mochalov.serge@gmail.com>
 */
class GameController extends FOSRestController
{
    /**
     * @param Request $request
     * @return array
     *
     * @Rest\Get("/api/game", name="game_page")
     */
    public function gameAction(Request $request)
    {
        $game = $this->getGameInstance($request);

        return [
            'clicks' => $game->getClickCount(),
            'square' => $game->getMatrix(),
            'state' => $game->getState(),
        ];
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Rest\Get("/api/game/state", name="game_api_get_state")
     */
    public function gameStateAction(Request $request)
    {
        $game = $this->getGameInstance($request);
        $name = $request->getSession()->get('name', "");
        $best_result = 0;

        /** @var Winner $winner */
        $winner = $this->getDoctrine()->getRepository(Winner::class)->findOneByName($name);
        if ($winner) {
            $best_result = $winner->getClickCount();
        }

        return $this->handleView($this->view([
            'name' => $name,
            'best_result' => $best_result,
            'clicks' => $game->getClickCount(),
            'state' => $game->getState(),
        ], 200));
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Rest\Post("/api/game", name="game_api_click")
     */
    public function clickAction(Request $request)
    {
        $row = $request->get('row');
        $col = $request->get('col');

        $game = $this->getGameInstance($request);
        $game->setClick($row, $col);

        return [
            'message' => 'Success'
        ];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Rest\Delete("/api/game", name="game_api_refresh")
     */
    public function refreshAction(Request $request)
    {
        $session = $request->getSession();
        if ($session->has('game')) {
            $session->remove('game');
        }

        return [
            'message' => 'The game is refreshed',
        ];
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
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Rest\Post("/api/game/save", name="game_api_save_game");
     */
    public function saveGameAction(Request $request)
    {
        $game = $this->getGameInstance($request);

        // save game state ...

        return $this->handleView($this->view(null, 200));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Rest\Post("/api/game/winners", name="game_api_set_winner")
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
            $game = new Game();
            $game->initMatrix();

            $session->set('game', $game);
        }

        return $session->get('game');
    }
}
