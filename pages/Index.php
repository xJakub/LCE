<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 09/03/2016
 * Time: 21:06
 */
class Index implements PublicSection
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
        // TODO: Implement getTitle() method.
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        // TODO: Implement getSubtitle() method.
    }

    /**
     * @return void
     */
    public function show()
    {
        $season = Season::findOne('ispublic order by isdefault desc limit 1');
        if ($season) {
            HTMLResponse::exitWithRoute('/'.$season->getLink().'/');
        }
    }
}