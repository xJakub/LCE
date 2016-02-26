<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 21/01/2016
 * Time: 21:34
 */
class Rules implements PublicSection
{

    /**
     * @var PublicDesign
     */
    private $design;

    public function setDesign(PublicDesign $response)
    {
        $this->design = $response;
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
        $editing = false;

        if (Team::isSuperAdmin()) {
            $editing = HTMLResponse::fromGET('edit', '');
            if (!$editing) {
                ?>
                <a href="<?=HTMLResponse::getRoute()?>?edit=1">
                    Editar página
                </a>
            <? } else { ?>
                <a href="<?=HTMLResponse::getRoute()?>" onclick="return confirm('Quieres descartar los cambios?')">
                    Descartar y volver a la página
                </a>
            <? } ?>
            <div style="height: 6px"></div>
            <?
        }

        $content = Setting::getKey('rules_content');

        if (!$editing) {
            ?><div class="inblock" style="margin: 0 auto; max-width: 90%; text-align: justify">
                <?=$content?>
            </div><?
        }
        else {

            if (!$csrf = $_SESSION['csrf']) {
                $_SESSION['csrf'] = $csrf = rand(1, 1000000);
            }

            if (HTMLResponse::fromGETorPOST('csrf', '') == $csrf) {
                $content = HTMLResponse::fromPOST('content');
                Setting::setKey('rules_content', $content);
                HTMLResponse::exitWithRoute(HTMLResponse::getRoute());
            }

            $this->design->addJavaScript('//cdn.ckeditor.com/4.5.7/full/ckeditor.js');
            $this->design->addJavaScript("
                CKEDITOR.replace( 'editor' )
            ", false);
            ?>
            <form action="<?=HTMLResponse::getRoute()?>?edit=1" method="post">
                <div style="width:90%; margin: 0 auto">
                    <textarea name="content" id="editor"><?=htmlentities($content)?></textarea>
                </div>
                <br>
                <input type="hidden" name="csrf" value="<?=$csrf?>">
                <button type="submit">Guardar cambios</button>
            </form>
            <?
        }

    }
}