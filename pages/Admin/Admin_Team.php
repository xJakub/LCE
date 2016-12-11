<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 01/03/2016
 * Time: 1:51
 */
class Admin_Team implements PublicSection
{
    public function __construct($teamid) {
        $this->team = Team::get($teamid);
    }

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
        return "Equipo #{$this->team->teamid}";
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

        $disabled = '';
        /*
        if (Team::isSuperAdmin($this->team->username) && $this->team->username != TwitterAuth::getUserName()) {
            $disabled = 'disabled';
        }
        */

        if ($postCsrf == $csrf) {
            $this->team->name = HTMLResponse::fromPOST('name', $this->team->name);
            if ($_FILES['avatar']['tmp_name']) {
                $con = file_get_contents($_FILES['avatar']['tmp_name']);
                file_put_contents($this->team->getImageLink(), $con);
                $this->team->clearImageCache();
            }
            $this->team->ismember = !!HTMLResponse::fromPOST("ismember", 0);
            $this->team->ispublic = !!HTMLResponse::fromPOST("ispublic", 0);
            $this->team->isadmin = !!HTMLResponse::fromPOST("isadmin", 0);
            $this->team->save();
        }

        ?>
        <div class="inblock middle" style="margin-right: 16px">
            <a target="_blank" href="/<?=$this->team->getImageLink()?>">
                <img src="/<?=$this->team->getImageLink(300, 200)?>?<?=time()?>" alt="Logo" class="teamlogo"><br>
            </a>
            <a target="_blank" href="/equipos/<?=$this->team->getLink()?>/">
                Ver página del equipo<br>
            </a>
        </div>
        <div class="inblock middle">
            <form enctype="multipart/form-data" action="<?=HTMLResponse::getRoute()?>" method="post">
                <table style="width:512px; margin: 0 auto; text-align: left">
                    <thead>
                    <tr style="text-align: center">
                        <td>
                            Propiedad
                        </td>
                        <td>
                            Valor
                        </td>
                    </tr>
                    </thead>
                    <tr>
                        <td>
                            <b>Usuario en Twitter</b>
                        </td><td>
                            <input disabled value="<?=htmlentities($this->team->username)?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Nombre del equipo</b>
                        </td><td>
                            <input name="name" value="<?=htmlentities($this->team->name)?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Nuevo avatar</b>
                        </td><td>
                            <input name="avatar" type="file">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Opciones</b>
                        </td><td>
                            <input type="checkbox" name="ispublic" <?=$this->team->ispublic?'checked':''?> <?=$disabled?>>
                            Visible<br>

                            <input type="checkbox" name="ismember" <?=$this->team->ismember?'checked':''?> <?=$disabled?>>
                            Miembro<br>

                            <input type="checkbox" name="isadmin" <?=$this->team->isadmin?'checked':''?> <?=$disabled?>>
                            Admin<br>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="csrf" value="<?= $csrf ?>"><br>
                <button type="submit">Guardar cambios</button><br><br>
            </form>
        </div>
        <?php
    }
}