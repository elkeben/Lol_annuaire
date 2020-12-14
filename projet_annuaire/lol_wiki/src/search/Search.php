<?php


namespace App\search;


class Search
{

    private $keyword;

    /**
     * @return mixed
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param mixed $keyword
     */
    public function setKeyword($keyword): void
    {
        $this->keyword = $keyword;
    }




}
