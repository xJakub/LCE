<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 20/01/2016
 * Time: 22:42
 */
class JoinUs implements PublicSection
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
        return "Únete a la LCE";
    }

    /**
     * @return void
     */
    public function show()
    {
        if (Team::getUsersTeam()) {

            ?>
            <div style="color: blue; margin: 6px">
                Ya estás en la LCE.
            </div>

            <table style="margin: 8px; width: 750px; margin: 0 auto">
                <thead>
                <tr>
                    <td>Lista de solicitudes</td>
                </tr>
                </thead>
                <?
                foreach(Application::find('1=1 order by dateline desc') as $application) {
                    ?>
                    <tr>
                        <td style="text-align: left">
                            <div class="inblock middle" style="width: 160px">
                                <a href="http://twitter.com/<?=htmlentities($application->username)?>" target="_blank">
                                    <img class="middle" src="<?= htmlentities($application->avatar) ?>" style="width:40px; height:40px; border-radius: 20px">
                                </a>
                                <div class="inblock middle">
                                    <a href="http://twitter.com/<?=htmlentities($application->username)?>" target="_blank">
                                        <?= htmlentities($application->username) ?>
                                    </a>
                                </div>
                            </div>
                            <div class="inblock middle" style="width: 100px">
                                <a href="<?=htmlentities($application->url)?>" target="_blank">
                                    Ver canal
                                </a>
                            </div>
                            <div class="inblock middle" style="width: 150px">
                                <?= $application->captureboard
                                    ? '<div class="success-icon">&#x2714;</div>'
                                    : '<div class="fail-icon">&#x2718</div>' ?>
                                <?= $application->captureboard
                                    ? 'Con capturadora'
                                    : 'Sin capturadora' ?>
                            </div>

                            <div class="inblock middle" style="width: 150px">
                                <i><?= date("Y/m/d H:i:s", $application->dateline) ?></i>
                            </div>

                            <div class="moreless inblock middle" style="width: 150px">
                                <a href="javascript:void(0)" onclick="$(this).closest('td').find('.moreless').toggle(); $(this).closest('td').find('.onmore').slideDown(500);">+ Mostrar más</a>
                            </div>
                            <div class="moreless inblock middle" style="width: 150px; display: none">
                                <a href="javascript:void(0)" onclick="$(this).closest('td').find('.moreless').toggle(); $(this).closest('td').find('.onmore').slideUp(400);">- Mostrar menos</a>
                            </div>

                            <div class="onmore" style="display: none; margin-top: 9px">

                                <div class="inblock middle">
                                    <b>¿Con qué frecuencia subes vídeos?</b>
                                </div>
                                <div>
                                    <?= htmlentities($application->frequency) ?>
                                </div>
                                <div style="height: 9px"></div>

                                <div class="inblock middle">
                                    <b>¿Por qué quieres unirte a la LCE?</b>
                                </div>
                                <div>
                                    <?= htmlentities($application->reason) ?>
                                </div>
                                <div style="height: 9px"></div>

                                <div class="inblock middle">
                                    <b>¿Qué puedes aportar a la LCE?</b>
                                </div>
                                <div>
                                    <?= htmlentities($application->contributions) ?>
                                </div>
                                <div style="height: 9px"></div>

                            </div>
                        </td>
                    </tr>
                    <?
                }
                ?>
            </table><br>
            <?
        }
        else {

            $disabled = TwitterAuth::isLogged() ? '' : 'disabled';

            $application = Application::create();
            $application->captureboard = HTMLResponse::fromGETorPOST('captureboard', '');
            $application->url = HTMLResponse::fromGETorPOST('url', '');
            $application->frequency = HTMLResponse::fromGETorPOST('frequency', '');
            $application->reason = HTMLResponse::fromGETorPOST('reason', '');
            $application->contributions = HTMLResponse::fromGETorPOST('contributions', '');
            $agrees = HTMLResponse::fromGETorPOST('agrees', '');
            $submit = !!HTMLResponse::fromPOST('submit', '');

            if ($submit && TwitterAuth::isLogged()) {
                if ($agrees
                    && strlen($application->captureboard)
                    && strlen($application->url)
                    && strlen($application->frequency)
                    && strlen($application->reason)
                    && strlen($application->contributions)
                    && !Application::exists()) {

                    $application->userid = TwitterAuth::getUserId();
                    $application->username = TwitterAuth::getUserName();
                    $application->avatar = TwitterAuth::getAvatar();
                    $application->dateline = time();
                    $application->save();
                }
                else {
                    ?>
                    <div style="color: red; margin: 6px">
                        Error: debes rellenar todos los campos y aceptar las normas para poder mandar tu solicitud.
                    </div>
                    <?
                }
            }

            if ($application = Application::exists()) {
                ?>
                <div style="margin: 6px; text-align: left; display: inline-block">
                    Ya has mandado tu solicitud (<?= date("Y/m/d H:i:s", $application->dateline) ?>).<br>
                    Contactaremos contigo si eres aceptado.
                </div>
                <?
            } else {
                ?>
            <form action="<?= HTMLResponse::getRoute() ?>" method="post" class="inblock">

                <div class="inblock middle right" style="width:240px; padding-right: 8px;">
                    Cuenta de Twitter
                </div>
                <div class="inblock middle" style="width: 240px; text-align: left">
                    <? if ($disabled) { ?>
                        <a href="<?= HTMLResponse::getRoute() ?>?authenticate=1">
                            Inicia sesión antes de rellenar
                        </a>
                    <? } else { ?>
                        <a target="_blank" href="http://twitter.com/<?= TwitterAuth::getUserName() ?>">
                            <?= TwitterAuth::getUserName() ?>
                        </a>
                    <? } ?>
                </div>
                <div style="height: 4px"></div>


                <div class="inblock middle right" style="width:240px; padding-right: 8px;">
                    URL del Canal
                </div>
                <div class="inblock middle">
                    <input style="width:240px" name="url" placeholder="http://youtube.com/canal"
                           value="<?= htmlentities($application->url) ?>" <?= $disabled ?>>
                </div>
                <div style="height: 4px"></div>

                <div class="inblock middle right" style="width:240px; padding-right: 8px;">
                    ¿Tienes capturadora?
                </div>
                <div class="inblock middle">
                    <div style="width:240px; text-align: left">
                        <input type="radio" name="captureboard"
                               value="1" <?= $disabled ?> <?= $application->captureboard == '1' ? 'checked' : '' ?>> Sí
                        <input type="radio" name="captureboard"
                               value="0" <?= $disabled ?> <?= $application->captureboard == '0' ? 'checked' : '' ?>> No
                    </div>
                </div>
                <div style="height: 4px"></div>

                <div class="inblock middle right" style="width:240px; padding-right: 8px;">
                    ¿Con qué frecuencia subes vídeos?
                </div>
                <div class="inblock middle">
                    <input style="width:240px" name="frequency" placeholder=""
                           value="<?= htmlentities($application->frequency) ?>" <?= $disabled ?>>
                </div>
                <div style="height: 4px"></div>

                <div class="inblock middle right" style="width:240px; padding-right: 8px;">
                    ¿Por qué quieres unirte a la LCE?
                </div>
                <div class="inblock middle">
                <textarea style="width:240px; height: 120px;"
                          name="reason" <?= $disabled ?>><?= htmlentities($application->reason) ?></textarea>
                </div>
                <div style="height: 4px"></div>

                <div class="inblock middle right" style="width:240px; padding-right: 8px;">
                    ¿Qué puedes aportar a la LCE?
                </div>
                <div class="inblock middle">
                <textarea style="width:240px; height: 120px;"
                          name="contributions" <?= $disabled ?>><?= htmlentities($application->contributions) ?></textarea>
                </div>
                <div style="height: 4px"></div>

                <div class="inblock middle right" style="width:240px; padding-right: 8px;">

                </div>
                <div class="inblock middle" style="font-size: 80%; text-align: left; width:240px">
                    Estás solicitando acceso a la Little Cup de la LCE. Para más información, lee las normas.
                </div>

                <div style="height: 12px"></div>

                <div class="inblock middle right" style="width:240px; padding-right: 8px;">

                </div>
                <div class="inblock middle" style="text-align: left; width:240px">
                    <input type="checkbox" name="agrees" <?= $disabled ?> <?= $agrees ? 'checked' : '' ?>>
                    He leído y acepto las
                    <a href="/normas/" target="_blank">
                        <b style="text-decoration: underline;">NORMAS</b>
                    </a>
                </div>

                <div style="height: 12px"></div>


                <div class="inblock middle right" style="width:240px; padding-right: 8px;">

                </div>
                <div class="inblock middle" style="text-align: left; width:240px">
                    <input type="submit" name="submit" value="Enviar" <?= $disabled ?>>
                </div>
                <div style="height: 4px"></div>



                </form><?
            }
        }
    }
}