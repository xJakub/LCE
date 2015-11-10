<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 10/03/2015
 * Time: 23:33
 */

interface Section {
    /**
     * @return String
     */
    public function getTitle();

    /**
     * @return String
     */
    public function getSubtitle();

    /**
     * @return void
     */
    public function show();
}