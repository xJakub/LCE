<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 30/10/2015
 * Time: 20:46
 */
class Logout implements PublicSection
{

    public function __construct() {
        session_destroy();
        HTMLResponse::exitWithRoute('/');
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
        // TODO: Implement show() method.
    }
}