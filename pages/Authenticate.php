<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 20:44
 */

class Authenticate implements PublicSection
{

    public function __construct() {
        HTMLResponse::exitWithRoute(TwitterAuth::getAuthorizeURL());
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