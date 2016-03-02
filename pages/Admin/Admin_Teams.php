<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/02/2016
 * Time: 20:33
 */
class Admin_Teams implements PublicSection
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
        return "Administrador de equipos";
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

        ?><form action="<?=HTMLResponse::getRoute()?>" method="post">
        <table style="width:640px; margin: 0 auto; text-align: left">
            <thead>
            <tr>
                <td>Logo</td>
                <td>Usuario</td>
                <td>Equipo</td>
                <td>Opciones</td>
            </tr>
            </thead>
            <?
            $teams = Team::find('1=1 order by name asc');
            foreach($teams as $team) {
                $disabled = '';
                $noname = $team->username == $team->name;

        /*
                if (Team::isSuperAdmin($team->username) && $team->username != TwitterAuth::getUserName()) {
                    $disabled = 'disabled';
                }
                else {
        */
                    if ($csrf == $postCsrf) {
                        $team->ismember = HTMLResponse::fromPOST("ismember{$team->teamid}") ? '1' : '0';
                        $team->ispublic = HTMLResponse::fromPOST("ispublic{$team->teamid}") ? '1' : '0';
                        $team->isadmin = HTMLResponse::fromPOST("isadmin{$team->teamid}") ? '1' : '0';
                        $team->save();
                     }
                /* } */
                ?>
                <tr>
                    <td style="text-align: center">
                        <img src="/<?=$team->getImageLink(64, 64)?>">
                    </td>
                    <td style="padding-bottom: 12px; padding-top: 12px">
                        <a href="http://twitter.com/<?=$team->username?>" target="_blank">
                            @<?=$team->username?>
                        </a>
                    </td>
                    <td style="<?=$noname?'font-style:italic; color: #666':''?>">
                        <?=htmlentities($team->name)?>
                        <div style="height: 3px"></div>
                        <a href="/admin/equipos/<?=$team->teamid?>/">
                            <i>Cambiar nombre / avatar</i>
                        </a>
                    </td>
                    <td>
                        <input type="checkbox" name="ispublic<?=$team->teamid?>" <?=$team->ispublic?'checked':''?> <?=$disabled?>>
                        Visible<br>

                        <input type="checkbox" name="ismember<?=$team->teamid?>" <?=$team->ismember?'checked':''?> <?=$disabled?>>
                        Miembro<br>

                        <input type="checkbox" name="isadmin<?=$team->teamid?>" <?=$team->isadmin?'checked':''?> <?=$disabled?>>
                        Admin<br>
                    </td>
                </tr>
                <?
            }
            ?></table>
        <input type="hidden" name="csrf" value="<?= $csrf ?>"><br>
        <button type="submit">Guardar cambios</button><br><br>
        </form><?
    }
}