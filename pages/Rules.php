<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 21/01/2016
 * Time: 21:34
 */
class Rules implements PublicSection
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
        return "Normas";
    }

    /**
     * @return void
     */
    public function show()
    {
        ?>
        <div style="text-align: left; padding: 24px; line-height: 1.5em">
            <b><u>Normativa de acceso a la LCE</b></u><br>
            <ol>
                <li>Aspirante, debes saber que esta solicitud es en primer lugar para jugar la Copa Little Cup que se celebrará en abril/mayo, y cuyas normas específicas están más abajo.</li>
                <li>Tras superar la prueba con la Little Cup, se accedería a la segunda temporada de la LCE. </li>
                <li>Ser admitido en la Little Cup no garantiza ser admitido en la segunda temporada de la LCE, si el jugador no demuestra compromiso ni interés por este proyecto será expulsado. </li>
                <li>Si se quiere acceder a la segunda temporada de la LCE, es obligatorio jugar la Little Cup. Salvo excepciones de bajas en el último momento. </li>
                <li>Todas las solicitudes de aspirantes serán tomadas en cuenta, y sus canales de Youtube evaluados. </li>
                <li>La decisión final sobre los aspirantes aceptados en la Little Cup será de todos los miembros de la primera temporada de la LCE, en consenso. </li>
                <li>Para participar en esta liga es necesario tener un <b>canal de Youtube activo y constante</b>, y ser una persona responsable que cumpla con los horarios. Obviamente disponer de una 3DS, un juego de Pokémon Rubí Omega y Zafiro Alfa, y una conexión a internet. </li>
                <li><b>El acceso a 1ª división está condicionado por el resultado de la Little Cup.</b></li>
            </ol>

            <u><b>Normativa general</b></u></li>
            <ol>
                <li>La LCE es una liga de Pokémon en la que se juega semanalmente.</li>
                <li>Cada participante es el entrenador de un equipo, cuyo nombre y logo es a su elección. </li>
                <li>Los equipos constan de 3 Pokémon de cada tier: OU, UU, RU y NU en la 1ª división.
                    Y 3 Pokémon de cada tier: UU, RU, UN y PU en 2ª división. </li>
                <li>Por tanto, cada equipo dispone de 12 Pokémon, elegidos a principio de temporada, y cada semana escoge 6 de esos 12 para jugar. De una jornada a otra puede varias completamente el set de un Pokémon. </li>
                <li>Los combates se juegan en 3DS con los juegos Pokémon Rubí Omega y Zafiro Alfa. </li>
            </ol>

            <u><b>Pickings y equipos</b></u><br>
            <ol>
                <li>Se pueden llevar como máximo 3 Mega evoluciones en cada equipo, es decir, Pokémon que hayan sido escogidos como mega evolución en su tier correspondiente. </li>
                <li>Un Pokémon que se elige en una tier, se escoge con o sin mega. Por ejemplo, si escoges a Medicham en la tier RU, <b> NUNCA </b> podrías usarlo mega, puesto que mega-medicham está en OU, y debe ser elegido con mega en esa tier. </li>
                <li>Las habilidades Sequía y Llovizna solo se pueden elegir en OU, es decir si se quiere usar a Ninetales con Sequía se debería elegir ese pokémon en OU. </li>
                <li>Los pickings se realizan en un streaming, 2 semanas antes del comienzo de la liga, con todos los participantes en llamada. </li>
                <li>En caso de que alguien no pueda asistir, debe dejar su lista de Pokémon deseados a alguien que sí esté en el streaming, y elija en su lugar. </li>
                <li>Para cada tier, se sacará un orden aleatorio, y los jugadores irán eligiendo 1 Pokémon en ese orden, una vez todos han elegido 1 Pokémon, el orden es a la inversa, el que eligió último elige primero. Para la última vuelta el orden vuelve a ser como la primera vuelta. En la siguiente tier se vuelve a sacar un orden aleatorio. </li>
                <li>A mitad de temporada más o menos, se realiza un mercado midseason en el que todo el que lo desee puede dejar 1 Pokémon de cada tier, y a cambio coger 1 Pokémon que antes no hubiera sido pickeado por ningún equipo. Para este pikcking el sistema sería el mismo que a principio de temporada en cuanto a órdenes y demás. </li>
                <li>Antes de comenzar a pickear en una tier, todo el que quiera dejar 1 Pokémon debe comunicarlo, si no se deja ningún Pokémn, no se puede coger 1 nuevo. Antes de saber si se deja o no 1 Pokémon, se saca el orden de selección, para saber si interesa dejar algo y arriesgar a coger el Pokémon que se desea. </li>
            </ol>

            <u><b>Intercambios</b></u></li>
            <ol>
                <li>Se puede hacer un intercambio cada semana, siempre que sean Pokémon de la misma tier. </li>
                <li>El límite para realizar un intercambio es el martes a las 23:59. En dicho caso, si un intercambio se realiza el martes, los equipos afectados porque sus rivales hayan obtenido nuevos Pokémon, disponen de 24 horas más para realizar otro intercambio. </li>
            </ol>

            <u><b>Combates y vídeos</b></u><br>
            <ol>
                <li>Todas las semanas los combates deben publicarse a las 17:00 del domingo, hora peninsular. </li>
                <li>Generalmente la disponibilidad de los jugadores es para jugar los combates entre el jueves y el sábado, <b>SI NUNCA VAS A PODER JUGAR ESOS DÍAS, NO SOLICITES ACCESO. </b></li>
            </ol>

            <u><b>Errores de conexión</u></b><br>
            <ol>
                <li>En caso de error de conexión durante un combate, se intentará repetir realizando los mismos movimientos que en el primer intento, hasta llegar al punto en el que se perdió la conexión. Una vez llegado a ese punto los jugadores podrán jugar libremente. </li>
                <li>En caso de seguir esta ruta, si hay un turno en el que ocurre algo diferente al combate inicial (golpe crítico, un ataque que falla y había acertado), el combate pasa a ser de libre juego desde ese momento. </li>
                <li>Si en el combate participa algún jugador con capturadora es conveniente que grabe mientras juega, por si se pierde la conexión, se pueda recordar los movimientos de los turnos con facilidad. </li>
                <li>En caso de que no sea posible recordar los movimientos realizados en el primer combate, el combate estuviera próximo al final, o fueran demasiados turnos los que hubiera que repetir, es posible repetir el combate con los Pokémon que quedan en el momento en el que se perdió la conexión. Siempre y cuando ambos jugadores estén de acuerdo en si dañar de alguna manera a Pokémon que hubiera perdido vida, causar problemas de estado, meter hazards, etc. Antes de jugar libremente. </li>
                <li>Si los dos jugadores están  de acuerdo, se podrá repetir el combate desde 0 en casos en los que no hubieran sido muy dañados los Pokémon, o sea <b>MUY</b> difícil llevar el combate al mismo punto antes de su caído. </li>
            </ol>

            <b><u>Distribución de divisiones</b></u><br>
            <ol>
                <li>Para la segunda temporada la LCE contará con 2 divisiones. La primera división y la segunda división. </li>
                <li>En la primera división permanecerán los 9 primeros clasificados de la primera temporada de manera segura. </li>
                <li>El último clasificado de la primera temporada descenderá directamente a segunda división.</li>
                <li>El jugador mejor clasificado de la Little Cup, <b>que no hubiera jugado en la primera temporada</b>, entra directamente en 1ª. </li>
                <li>El 11º clasificado de la primera temporada se enfrentará al 2º mejor nuevo jugador clasificado en la Little Cup. </li>
                <li>El 10º clasificado de la primera temporada se enfrentará al 3º mejor nuevo jugador clasificado en la Little Cup. </li>
                <li>En caso de 2 jugadores nuevos que fueran eliminados en la misma ronda, se enfrentarían entre sí y el ganador sería el que quedaría por delante. </li>
                <li>Los combates de lucha por los puestos de la 1ª división serían en <b> formato OU</b>.</li>
            </ol>

            <b><u>Normativa de la Copa Little Cup</b></u><br>
            <ol>
                <li>La Copa Little Cup es una copa que se celebrará en relación a la LCE entre abril y mayo, fecha exacta aún por decidir. </li>
                <li>Al ser una copa, y no una liga, será en formato de eliminatoria directa, al mejor de 1 cada combate. </li>
                <li>Cada jugador dispondrá de un equipo de 6 Pokémon, de los cuales 3 serán fijos, y los otros 3 podrán cambiar entre partidas, cogiéndose cualquier Pokémon de Little cup no pickeado por otro equipo. </li>
                <li>Esto quiere decir que se hará antes de comenzar la copa un picking con las mismas normas que la LCE, donde solo se elegirán 3 Pokémon por cada jugador. <b>TODOS LOS POKÉMON PICKEADOS, NO PUEDEN SER USADOS POR NADIE MÁS EN LA COPA, NI AUNQUE EL EQUIPO QUE LO TUVIERA HAYA SIDO ELIMINADO.</b></li>
                <li>Los otros 3 Pokémon de equipo son <b>VARIABLES</b>, y se puede coger cualquier Pokémon, aunque otro jugador también tenga el mismo Pokémon <b>variable</b>. Pero en ningún caso se podrá escoger un Pokémon pickeado por alguien más. </li>
                <li>El resto de las normas son las mismas respecto a intercambios combates, publicación de vídeos, etc. Con la diferencia de que se juega en una única tier, Little cup. </li>
                <li>Esta copa es opcional para todos los jugadores participantes en la primera temporada, y el no jugarla no hace perder la plaza en la segunda temporada de la LCE. <b>Los nuevos miembros deben jugarla, a modo de prueba de su puntualidad, compromiso y responsabilidad con el proyecto de la LCE.</b>
            </ol>
        </div>
        <?
    }
}