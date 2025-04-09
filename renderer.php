<?php

/**
 * DokuWiki Plugin linklist (Renderer Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Andreas Gohr <dokuwiki@cosmocode.de>
 */
class renderer_plugin_linklist extends Doku_Renderer
{
    protected $data = [];


    /** @inheritDoc */
    public function getFormat()
    {
        return 'linklist';
    }

    public function document_start()
    {
        $this->data = [
            'internal' => [],
            'external' => [],
            'interwiki' => [],
        ];
    }

    public function document_end()
    {
        $this->doc = json_encode($this->data);
    }


    public function reset()
    {
        $this->document_start();
    }

    public function internallink($link, $title = null)
    {
        $title = helper_plugin_linklist::getTitle($link);

        if (isset($this->data['internal'][$link])) {
            return; // already added
        }
        $this->data['internal'][$link] = [$link, $title];
    }

    public function externallink($link, $title = null)
    {
        if(is_array($title)) $title = null;
        if($title === null) {
            $title = helper_plugin_linklist::getUrlTitle($link);
        }

        if (isset($this->data['external'][$link])) {
            return; // already added
        }
        $this->data['external'][$link] = [$link, $title];
    }

    public function interwikilink($link, $title, $wikiName, $wikiUri)
    {
        $title = helper_plugin_linklist::getTitle($link);

        if (isset($this->data['interwiki'][$link])) {
            return; // already added
        }
        $this->data['interwiki'][$link] = [$link, $title, $wikiName, $wikiUri];
    }


}
