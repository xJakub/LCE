<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 12/03/2015
 * Time: 19:04
 */

class HTMLResponse {

    private $title;
    private $metas;
    private $content;
    private $stylesheets;
    private $jsscripts;

    static $obj;

    static function toLink($str) {
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("'[^a-zA-Z0-9/_| -]'", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("'[/_| -]+'", '-', $clean);
        return $clean;
    }

    static function toClean($str) {
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("'[^a-zA-Z0-9/_| -]'", '', $clean);
        return $clean;
    }

    static function exitWithRoute($route) {
        header('location: '.($route));
        exit;
    }

    static function getRoute() {
        list($currentRoute) = explode('?', $_SERVER['REQUEST_URI']);
        return $currentRoute;
    }

    static function fromGET($key, $default=null) {
        return isset($_GET[$key])?$_GET[$key]:$default;
    }

    static function fromPOST($key, $default=null) {
        return isset($_POST[$key])?$_POST[$key]:$default;
    }

    static function fromGETorPOST($key, $default=null) {
        return isset($_POST[$key])?$_POST[$key]:
            (isset($_GET[$key])?$_GET[$key]:$default);
    }

    static function ensureRoute($route) {
        $currentRoute = self::getRoute();
        if ($route != $currentRoute) {
            self::exitWithRoute($route);
            exit;
        }
    }

    public function __construct() {
        $this->metas = array();
        $this->jsscripts = array();
        $this->stylesheets = array();
    }

    public function addJavaScript($data, $isURL=true, $toHead=false) {
        $arr = func_get_args();
        if (count($arr) < 2) $arr[] = true;
        if (count($arr) < 3) $arr[] = false;
        $this->jsscripts[] = $arr;
    }

    public function showJavaScript($isHead) {
        foreach($this->jsscripts as $arr) {
            list($data, $isURL, $toHead) = $arr;
            if ($toHead == $isHead) {
                if ($isURL) {
                    ?><script src="<?=htmlentities($data)?>"></script><?
                }
                else {
                    ?><script><?=$data?></script><?
                }
            }
        }
    }


    public function setTitle($title) {
        $this->title = $title;
    }

    public function getTitle() {
        return $this->title;
    }

    public function addStyleSheet($data, $isURL=true, $toHead=true) {
        $arr = func_get_args();
        if (count($arr) < 2) $arr[] = true;
        if (count($arr) < 3) $arr[] = true;
        $this->stylesheets[] = $arr;
    }

    public function setMeta($key, $value) {
        $this->metas[$key] = $value;
    }

    public function showMetas() {
        foreach($this->metas as $key => $value) {
            if ($key == 'charset') {
                ?><meta charset="<?=htmlentities($value)?>" id="meta-charset">
            <?
            }
            else {
                ?><meta name="<?=htmlentities($key)?>" content="<?=htmlentities($value)?>" id="meta-<?=htmlentities($key)?>">
            <?
            }
        }
    }
    public function showStyleSheets($isHead) {
        foreach($this->stylesheets as $arr) {
            list($data, $isURL, $toHead) = $arr;
            if ($toHead == $isHead) {
                if ($isHead) {
                    if ($isURL) {
                        ?>
                        <link href='<?= htmlentities($data) ?>' rel='stylesheet' type='text/css'><?
                    } else {
                        ?>
                        <style type="text/css"><?= $data ?></style><?
                    }
                } else {
                    if ($isURL) {
                        ?><script>
                            var cb = function() {
                                var l = document.createElement('link'); l.rel = 'stylesheet';
                                l.href = "<?= htmlentities($data) ?>";
                                var h = document.getElementsByTagName('head')[0]; h.parentNode.insertBefore(l, h);
                            };
                            var raf = requestAnimationFrame || mozRequestAnimationFrame ||
                                webkitRequestAnimationFrame || msRequestAnimationFrame;
                            if (raf) raf(cb);
                            else window.addEventListener('load', cb);
                        </script><?
                    } else {
                        ?>
                        <style type="text/css"><?= $data ?></style><?
                    }
                }
            }
        }
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function showBody() {
        echo $this->content;
    }

    public function show() {
        ?>
        <!doctype html>
        <html>
            <head>
                <?=$this->showMetas()?>
                <title><?= htmlentities($this->title) ?></title>
                <?=$this->showJavaScript(true)?>
                <?=$this->showStyleSheets(true)?>
            </head>
            <body>
                <?=$this->showBody() ?>
                <?=$this->showJavaScript(false)?>
                <?=$this->showStyleSheets(false)?>
            </body>
        </html>
        <?php
    }

}