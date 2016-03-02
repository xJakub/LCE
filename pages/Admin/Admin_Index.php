<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/02/2016
 * Time: 20:04
 */
class Admin_Index implements PublicSection
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
        return "Administración";
    }

    /**
     * @return void
     */
    public function show()
    {
        if (!Team::isSuperAdmin()) {
            HTMLResponse::exitWithRoute('/');
        }
        ?>
        <div class="inblock" style="text-align: left; margin: 0 auto">
            <ul>
                <li><a href="/admin/comunicados/">Enviar comunicados (a través de Twitter)</a></li>
                <li><a href="/admin/equipos/">Administrar equipos</a></li>
            </ul>
        </div>
        <?
    }
}