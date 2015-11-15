<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 11:29
 */
class Team extends Model {
    public $teamid;
    public $name;
    public $username;

    /**
     * @param $link
     * @return \___PHPSTORM_HELPERS\static
     */
    public static function fromLink($link)
    {
        foreach(Team::find('1=1') as $team) {
            if ($team->getLink() == $link) {
                return $team;
            }
        }
    }

    function getLink() {
        return HTMLResponse::toLink($this->name);
    }

    function getHashtag() {
        return str_replace(" ", "", ucwords(str_replace("-", " ", $this->getLink())));
    }

    function isManager($username = null) {
        if ($username === null) {
            $username = TwitterAuth::getUserName();
        }

        return (
            strtolower($this->username) === strtolower($username)
            || strtolower($username) === 'xjakub'
        );
    }

    function getImageLink($width = null, $height = null) {
        $originalLink = 'img/'.$this->getLink().'.png';
        if (!$width && !$height) {
            return $originalLink;
        }
        else if ($width && $height) {
            @mkdir('img/thumbnails');
            $link = 'img/thumbnails/'.$this->getLink()."-{$width}x{$height}.png";
            if (!file_exists($link)) {
                $im = imagecreatefrompng($originalLink);
                $originalWidth = imagesx($im);
                $originalHeight = imagesy($im);

                $newWidth = $width;
                $newHeight = round($originalHeight/$originalWidth*$newWidth);

                if ($newHeight > $height) {
                    $newHeight = $height;
                    $newWidth = round($originalWidth/$originalHeight*$newHeight);
                }

                $x = round(($width - $newWidth) / 2);
                $y = round(($height - $newHeight) / 2);

                // $it = imagecreatetruecolor($width, $height);
                $it = imagecreatetruecolor($newWidth, $newHeight);

                imagecolortransparent($it, $transparent = imagecolorallocatealpha($it, 0, 0, 0, 127));
                imagealphablending($it, false);
                imagesavealpha($it, true);

                //imagefilledrectangle($it, 0, 0, $width, $height, $transparent);

                // imagecopyresampled($it, $im, $x, $y, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                imagecopyresampled($it, $im, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                imagepng($it, $link, 9);

                // Intentamos comprimir el .png resultante llamando a optipng
                exec('optipng -o7 '.escapeshellarg($link));
            }
            return $link;
        }
    }
}

Team::init('teams', 'teamid');