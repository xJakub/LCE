<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 18/02/2016
 * Time: 21:17
 */
class Admin_Notices implements PublicSection
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
        return "Comunicados";
    }

    /**
     * @return void
     */
    public function show()
    {
        if (!Team::isSuperAdmin()) {
            HTMLResponse::exitWithRoute('/');
        }
        if (!TwitterAuth::getBotConfig()) {
            ?>Error: el bot no está configurado<br><?php
            return;
            }

        if (HTMLResponse::fromPOST('csrf', '') && strlen(trim(HTMLResponse::fromPOST('message','')))) {
            if ($_SESSION['csrf'] != HTMLResponse::fromPOST('csrf', '')) {
                ?>Error: código de seguridad incorrecto.<br><br><?php
            }
            else {
                $message = HTMLResponse::fromPOST('message');
                ?>
                <b>Mensaje</b>: <?=htmlentities($message)?><br><br>

<?php
                foreach(Team::find('ismember order by username asc') as $team) {
                    $lowname = strtolower($team->username);
                    if (HTMLResponse::fromPOST("check{$lowname}", '')) {
                        $ok = false;

                        $ok = !!TwitterAuth::botSendPrivateMessage($lowname, $message);

                        ?>-<?=$team->username?>:
                        <?= $ok?'Enviado correctamente':'Error en el envío' ?>
                        <br>
                        <?php
                    }
                }
                ?><br><br><?php
                return;
            }
        }

        if (!($csrf = $_SESSION['csrf'])) {
            $_SESSION['csrf'] = $csrf = rand(1, 1000000);
        }

        ?><div style="max-width: 640px; margin: 0 auto">
        <form action="<?=HTMLResponse::getRoute()?>" method="post">
            <b>Texto del comunicado (será enviado por MD en Twitter):</b><br>
            <textarea style="width: 320px" name="message"></textarea><br>
            <br>
            <b>Destinatarios del comunicado (
                <a href="javascript:void(0)" onclick="$(this).closest('div').find('input[type=checkbox]').attr('checked','checked')">
                    seleccionar todos
                </a>
                ):</b><br>
            <?php
            foreach(Team::find('ismember order by username asc') as $team) {
                $lowname = strtolower($team->username);
                ?>
                <div class="inblock" style="margin: 6px; text-align: left; width: 180px">
                    <div class="inblock middle">
                        <input id="check<?=$lowname?>"type="checkbox" name="check<?=$lowname?>">
                    </div>
                    <div class="inblock middle">
                        <label for="check<?=$lowname?>">
                            <?= $team->username ?><br>
                            <span style="font-style:italic; color: #666">
                                <?= $team->name ?>
                            </span>
                        </label>
                    </div>
                </div>
                <?php
            }
            ?></div><br>
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <button type="submit">Enviar comunicado</button>
        </form><br><br><?php
    }
}