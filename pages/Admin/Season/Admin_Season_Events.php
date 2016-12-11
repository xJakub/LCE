<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 22/03/2016
 * Time: 13:53
 */
class Admin_Season_Events implements PublicSection
{
    public function __construct($seasonId) {
        $this->season = Season::get($seasonId);
    }

    public function setDesign(PublicDesign $response)
    {
        $response->setSeason($this->season);
    }

    /**
     * @return String
     */
    public function getTitle()
    {
        return "LCE Pokémon";
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        return "Eventos de {$this->season->name}";
    }

    /**
     * @return void
     */
    public function show()
    {
        if (!Team::isSuperAdmin()) {
            HTMLResponse::exitWithRoute('/');
        }

        if (!($csrf = $_SESSION['csrf'])) {
            $_SESSION['csrf'] = $csrf = rand(1, 1000000);
        }
        $postCsrf = HTMLResponse::fromPOST('csrf', '');


        $events = $this->season->getEvents();;

        if ($postCsrf == $csrf) {
            $oldEvents = $events;
            $oldEvents[] = ["", "", ""];
            $events = [];

            foreach($oldEvents as $index => $event) {
                $event[0] = HTMLResponse::fromPOST("name{$index}", $event[0]);
                $event[1] = HTMLResponse::fromPOST("date{$index}", $event[0]);
                $event[2] = HTMLResponse::fromPOST("link{$index}", $event[0]);

                if (strlen($event[0])) {
                    $events[] = $event;
                }
            }

            $this->season->setEvents($events);
            $this->season->save();
        }

        $events[] = ["Nuevo evento", "2099-12-31", "http://example.com"];


        ?><div class="inblock middle">
        <form enctype="multipart/form-data" action="<?=HTMLResponse::getRoute()?>" method="post">

            <table style="width: 640px">
                <thead>
                <tr>
                    <td>Nombre</td>
                    <td>Fecha</td>
                    <td>Enlace</td>
                </tr>
                </thead>
                <?php foreach($events as $index => $event) {
                    $key = ($index == count($events)-1) ? 'placeholder' : 'value';
                    ?>
                    <tr>
                        <td>
                            <input style="width: 150px" name="name<?=$index?>" <?=$key?>="<?=htmlentities($event[0])?>">
                        </td>
                        <td>
                            <input style="width: 100px" name="date<?=$index?>" type="date" <?=$key?>="<?=htmlentities($event[1])?>">
                        </td>
                        <td>
                            <input style="width: 250px" name="link<?=$index?>" <?=$key?>="<?=htmlentities($event[2])?>">
                        </td>
                    </tr>
                    <?php
                } ?>
            </table><br>

            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <button type="submit">Guardar cambios</button><br><br>

        </form>
        </div><?php
    }
}