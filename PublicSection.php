<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 10/03/2015
 * Time: 23:33
 */

interface PublicSection extends Section {
    public function setDesign(PublicDesign $response);
}