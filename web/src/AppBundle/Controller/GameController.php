<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Winner;
use AppBundle\Game\Game;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

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
     * @return array
     *
     * @Rest\Get("/api/game/winners", name="game_api_get_winners")
     */
    public function winnersAction()
    {
        $winners = $this->getDoctrine()
            ->getRepository(Winner::class)
            ->findAll();

        return $winners;
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Rest\Post("/api/game/winners", name="game_api_set_winner")
     */
    public function setWinnerAction(Request $request)
    {
        return [];
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
