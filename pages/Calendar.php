<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 21/03/2016
 * Time: 18:05
 */
class Calendar implements PublicSection
{
    public function __construct($seasonLink) {
        $this->season = Season::getByLink($seasonLink);
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
        return "Calendario";
    }

    /**
     * @return void
     */
    public function show()
    {
        $weeksCount = $this->season->getWeeksCount();
        $start = null;
        $end = null;
        $events = [];

        $matches = Model::groupBy(
            Match::find('seasonid = ?', [$this->season->seasonid]),
            'week'
        );

        for ($week=1; $week<=$weeksCount; $week++) {
            $weekTime = $this->season->getPublishTimeForWeek($week);
            if ($weekTime > 1000 && date('Y', $weekTime)*1 != 2099) {
                if ($start === null || $weekTime < $start) {
                    $start = $weekTime;
                }
                if ($end === null || $weekTime > $end) {
                    $end = $weekTime;
                }

                $link = isset($matches[$week])
                    ? ('/' . $this->season->getLink() . "/jornadas/{$week}/")
                    : '';
                $events[date('Y-m-d', $weekTime)][] = [$this->season->getWeekName($week), $link];
            }
        }

        $seasonEvents = $this->season->getEvents();
        foreach($seasonEvents as $event) {
            $events[$event[1]][] = [$event[0], $event[2]];
            $eventTime = Season::dateToTime($event[1]);

            if ($eventTime > 1000 && date('Y', $eventTime)*1 != 2099) {
                if ($start === null || $eventTime < $start) {
                    $start = $eventTime;
                }
                if ($end === null || $eventTime > $end) {
                    $end = $eventTime;
                }
            }
        }

        if ($start && $end) {
            $year = date('Y', $start)*1;
            $month = date('m', $start)*1;

            // max 5y, por si acaso
            $end = min($end, $start + 365*5*86400);

            $shortDays = explode(',', 'Dom,Lun,Mar,Mié,Jue,Vie,Sáb,Dom');
            $days = explode(',', 'Domingo,Lunes,Martes,Miércoles,Jueves,Viernes,Sábado');
            $months = explode(',', 'enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre');

            while (($time = mktime(0, 0, 0, $month, 1, $year)) <= $end) {
                ?>
                <div class="inblock" style="margin: 8px">
                    <table class="vborders">
                        <thead>
                        <tr>
                            <td colspan="7">
                                <?= ucfirst($months[date('m', $time)*1-1]) ?> de <?=date('Y', $time)?>
                            </td>
                        </tr>
                        </thead>
                        <tr>
                            <?

                            $monthDays = date('t', $time);
                            $columns = (date('w', $time) + 6) % 7;

                            if ($columns) {
                                ?><td style="" colspan="<?= $columns ?>"></td><?
                            }

                            for ($d=1; $d<=$monthDays; $d++) {
                            $dayTime = mktime(0, 0, 0, $month, $d, $year);

                            $weekDay = date('w', $dayTime);
                            if ($d != 1 && $weekDay == 1) {
                            ?>
                        </tr><tr>
                            <?
                            }

                            $dayEvents = [];
                            if (isset($events[date('Y-m-d', $dayTime)])) {
                                $dayEvents = $events[date('Y-m-d', $dayTime)];
                            }

                            $style = '';
                            if (date('Y-m-d') != date('Y-m-d', $dayTime) && time() > $dayTime) {
                                $style = 'text-decoration: line-through;';
                            }

                            ?>
                            <td style="<?= $style ?>">
                                <div style="<?= $dayEvents ? 'font-weight:bold;' : 'color: #666;' ?>;<?= $style ?>">
                                    <?= $shortDays[$weekDay] ?>
                                    <?= $d ?>
                                </div>
                                <div class="inblock middle">
                                    <div style="text-align: center; min-width: 48px; margin: 6px 0px">
                                        <?
                                        if (!$dayEvents) echo "&nbsp;";
                                        foreach($dayEvents as $event) { ?>
                                            <? if (!$event[1]) { ?>
                                                <?= $event[0] ?><br>
                                            <? } else { ?>
                                                <a href="<?=$event[1]?>">
                                                    <?= $event[0] ?><br>
                                                </a>
                                            <? } ?>
                                        <? } ?>
                                    </div>
                                </div>
                            </td>
                            <?
                            }

                            $columns = 6 - ((date('w', $dayTime) + 6) % 7);
                            if ($columns) {
                                ?><td style="" colspan="<?= $columns ?>"></td><?
                            }
                            ?>
                        </tr>
                    </table>
                </div>
                <?

                if (++$month == 13) {
                    $month = 1;
                    $year++;
                }
            }
        }
    }
}