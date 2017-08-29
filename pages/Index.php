<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 09/03/2016
 * Time: 21:06
 */
class Index implements Section
{

    /**
     * @return String
     */
    public function getTitle()
    {
        return 'LCE Pokémon';
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        return 'Temporada 3';
    }

    /**
     * @return void
     */
    public function show()
    {
        ?>
        <a href="/temporada-3-liga-luna/"><img style='width: 440px' src="/img/liga-luna.png"></a>
        <a href="/temporada-3-liga-sol/"><img style='width: 440px' src="/img/liga-sol.png"></a>
        <?php
    }
}