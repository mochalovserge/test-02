<?php

namespace AppBundle\Game;

class Game
{
    const WIDTH_SIZE = 5;
    const HEIGHT_SIZE = 5;

    /**
     * @var int
     */
    protected $clickCount = 0;

    /**
     * @var array
     */
    protected $matrix = [];

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $gamerName;

    /**
     * @return void
     */
    public function initMatrix()
    {
        for ($i = 0; $i < self::WIDTH_SIZE; ++$i) {
            for ($j = 0; $j < self::HEIGHT_SIZE; ++$j) {
                $this->matrix[$i][$j] = rand(0, 1);
            }
        }
    }

    /**
     * @param int $i
     * @param int $j
     */
    public function setClick($i, $j)
    {
        if (isset($this->matrix[$i][$j]) && $this->matrix[$i][$j] == 0) {
            $this->matrix[$i][$j] = 1;
            // меняем состояние в окрестностях клетки
            if (isset($this->matrix[$i - 1][$j - 1])) {
                $this->matrix[$i - 1][$j - 1] = (int)(!$this->matrix[$i - 1][$j - 1]);
            }
            if (isset($this->matrix[$i][$j - 1])) {
                $this->matrix[$i][$j - 1] = (int)(!$this->matrix[$i][$j - 1]);
            }
            if (isset($this->matrix[$i + 1][$j - 1])) {
                $this->matrix[$i + 1][$j - 1] = (int)(!$this->matrix[$i + 1][$j - 1]);
            }
            if (isset($this->matrix[$i - 1][$j])) {
                $this->matrix[$i - 1][$j] = (int)(!$this->matrix[$i - 1][$j]);
            }
            if (isset($this->matrix[$i + 1][$j])) {
                $this->matrix[$i + 1][$j] = (int)(!$this->matrix[$i + 1][$j]);
            }
            if (isset($this->matrix[$i - 1][$j + 1])) {
                $this->matrix[$i - 1][$j + 1] = (int)(!$this->matrix[$i - 1][$j + 1]);
            }
            if (isset($this->matrix[$i][$j + 1])) {
                $this->matrix[$i][$j + 1] = (int)(!$this->matrix[$i][$j + 1]);
            }
            if (isset($this->matrix[$i + 1][$j + 1])) {
                $this->matrix[$i + 1][$j + 1] = (int)(!$this->matrix[$i + 1][$j + 1]);
            }

            $this->setClickCount($this->getClickCount() + 1);
        }
    }

    /**
     * @return array
     */
    public function getMatrix()
    {
        return $this->matrix;
    }

    /**
     * @return int
     */
    public function getClickCount()
    {
        return $this->clickCount;
    }

    /**
     * @param int $clickCount
     */
    public function setClickCount($clickCount)
    {
        $this->clickCount = $clickCount;
    }

    public function saveGameState()
    {
    }

    public function saveGameResult(array $data)
    {
    }

    public function getWinners()
    {
        return [];
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }
}
