<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 16/02/2016
 * Time: 20:55
 */
class ViewPoll implements PublicSection
{
    public function __construct($pollId) {
        $this->poll = Poll::get($pollId);
        if (!$this->poll || !Team::isMember()) {
            HTMLResponse::exitWithRoute('/votaciones/');
        }
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
        return "Votaciones LCE";
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        return $this->poll->title;
    }

    /**
     * @return void
     */
    public function show()
    {
        if (!TwitterAuth::isLogged()) {
            ?>
            Sólo los miembros pueden ver esta página.
            <a href="<?=HTMLResponse::getRoute()?>?authenticate=1">
                Inicia sesión.
            </a><br>
            <?
            return;
        }
        else {
            if (!Team::isMember()) {
                HTMLResponse::exitWithRoute('/votaciones/');
            }

            $answer = PollVote::findOne('userid = ? and pollid = ?',
                [TwitterAuth::getUserId(), $this->poll->pollid]);

            $options = PollOption::find('pollid = ? order by polloptionid asc', [$this->poll->pollid]);


            if (!$answer && strlen($hash = HTMLResponse::fromGET('hash', ''))) {
                $optionid = HTMLResponse::fromGET('vote');

                foreach($options as $index => $option) {
                    if ($option->polloptionid == $optionid && $option->getHash() == $hash) {
                        $answer = PollVote::create();
                        $answer->userid = TwitterAuth::getUserId();
                        $answer->dateline = time();
                        $answer->avatar = TwitterAuth::getAvatar();
                        $answer->pollid = $this->poll->pollid;
                        $answer->polloptionid = $option->polloptionid;
                        $answer->username = TwitterAuth::getUserName();
                        $answer->save();
                    }
                }
            }

            $answers = Model::groupBy(PollVote::find('pollid = ?', [$this->poll->pollid]), 'polloptionid');

            $hasAnswered = !!$answer;

            ?><div style="text-align:left; margin: 0 auto" class="inblock">
            <table style="width:640px">
                <thead>
                <tr>
                    <td>Lista de opciones</td>
                </tr>
                </thead>
                <?

                foreach($options as $index => $option) {
                    ?>
                    <tr><td class="row" style="text-align: left">
                            <div style="height: 6px"></div>
                            <div class="inblock middle" style="width:320px">
                                <b>Opción <?=$index+1?></b>: <?= htmlentities($option->title) ?>
                            </div>
                            <div class="inblock middle">
                                <div class="moreless inblock middle" style="width: 150px">
                                    <a href="javascript:void(0)" onclick="$(this).closest('.row').find('.moreless').toggle(); $(this).closest('.row').find('.onmore').slideDown(500);">+ Mostrar más</a>
                                </div>
                                <div class="moreless inblock middle" style="width: 150px; display: none">
                                    <a href="javascript:void(0)" onclick="$(this).closest('.row').find('.moreless').toggle(); $(this).closest('.row').find('.onmore').slideUp(400);">- Mostrar menos</a>
                                </div>
                            </div>
                            <div class="inblock middle">
                                <? if (!$hasAnswered) { ?>
                                    <a href="<?=HTMLResponse::getRoute()?>?vote=<?=$option->polloptionid?>&hash=<?=$option->getHash()?>" onclick="return confirm('¿Votas <?=htmlentities($option->title)?>?')">
                                        Votar esta opción
                                    </a>
                                <? } else if ($answer->polloptionid == $option->polloptionid) { ?>
                                    <i>Votaste esta opción</i>
                                <? } ?>
                            </div>
                            <div class="onmore" style="display: none; padding: 12px">
                                <?= $option->description ?>
                            </div>
                            <div style="height: 6px"></div>
                            <?
                            if (!$hasAnswered) {
                                ?><i>Vota primero para ver los resultados</i><?
                            }
                            else {
                                $optionAnswers = $answers[$option->polloptionid];
                                ?>
                                Votado por: <?= $optionAnswers
                                    ? '<b>'.implode(', ', Model::pluck($optionAnswers, 'username')).'</b> ('.count($optionAnswers).' votos)'
                                    : '<i>Nadie</i>'; ?>
                                <?
                            }
                            ?>
                            <div style="height: 6px"></div>
                        </td></tr>
                    <?
                }

                ?></table></div><br><br><?
        }
    }
}