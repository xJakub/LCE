<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 11/03/2016
 * Time: 17:32
 */
class Admin_Seasons implements PublicSection
{

    public function setDesign(PublicDesign $response)
    {
        // TODO: Implement setDesign() method.
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
        return "Administrador de temporadas";
    }

    /**
     * @return void
     */
    public function show()
    {
        if (!Team::isSuperAdmin()) {
            HTMLResponse::exitWithRoute('/');
        }

        if (!$csrf = $_SESSION['csrf']) {
            $_SESSION['csrf'] = $csrf = rand(1, 1000000);
        }
        $postCsrf = HTMLResponse::fromPOST('csrf', '');

        if ($postCsrf == $csrf) {
            $newSeason = Season::create();
            $newSeason->ispublic = false;
            $newSeason->isdefault = false;
            $newSeason->save();
            $newSeason->name = "Temporada {$newSeason->seasonid}";
            $newSeason->save();
        }

        ?>
        <table style="width: 512px; margin: 0 auto">
            <thead>
            <tr>
                <td>#</td>
                <td>Nombre</td>
                <td>Opciones</td>
                <td>Acciones</td>
            </tr>
            </thead>
            <?
            foreach(Season::find('1=1') as $season) {
                ?>
                <tr>
                    <td><?=$season->seasonid?></td>
                    <td><?=htmlentities($season->name)?></td>
                    <td>
                        <?= $season->ispublic ? 'Pública' : 'Oculta' ?>
                        <?= $season->isdefault ? '(por defecto)' : '' ?>
                    </td>
                    <td>
                        <a href="/admin/temporadas/<?=$season->seasonid?>/">
                            Editar
                        </a>
                    </td>
                </tr>
                <?
            }
            ?>
        </table><br>

        <form action="<?=HTMLResponse::getRoute()?>" method="post">
            <input type="hidden" name="csrf" value="<?=$csrf?>">
            <button type="submit">Añadir nueva temporada</button>
        </form><br>
        <?
    }
}