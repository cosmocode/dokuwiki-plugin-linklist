<?php

use dokuwiki\Extension\SyntaxPlugin;
use dokuwiki\Utf8\Sort;

/**
 * DokuWiki Plugin linklist (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Andreas Gohr <dokuwiki@cosmocode.de>
 */
class syntax_plugin_linklist extends SyntaxPlugin
{
    /** @inheritDoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritDoc */
    public function getPType()
    {
        return 'block';
    }

    /** @inheritDoc */
    public function getSort()
    {
        return 155;
    }

    /** @inheritDoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('\{\{linklist[^\}]*?\}\}', $mode, 'plugin_linklist');
    }


    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $match = substr($match, 11, -2);
        $params = explode(' ', $match);
        $id = array_shift($params);
        $params = array_map('trim', $params);

        $type = 'internal';
        if (in_array('backlinks', $params)) {
            $type = 'backlinks';
        } elseif (in_array('external', $params)) {
            $type = 'external';
        } elseif (in_array('interwiki', $params)) {
            $type = 'interwiki';
        }


        $data = [
            'id' => $id,
            'type' => $type,
        ];

        return $data;
    }

    /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode == 'linklist') return false;
        if ($mode == 'metadata') return false;

        global $INFO;

        if (!$data['id']) $data['id'] = $INFO['id'];
        if (!page_exists($data['id'])) return true;
        switch ($data['type']) {
            case 'backlinks':
                // backlinks from the index
                $links = ft_backlinks($data['id'], true);
                $links = array_map(fn($link) => [$link, helper_plugin_linklist::getTitle($link)], $links);
                break;
            case 'internal':
            case 'external':
            case 'interwiki':
                // all other links from our own renderer
                $results = p_cached_output(wikiFN($data['id']), 'linklist');
                $results = json_decode($results, true, JSON_THROW_ON_ERROR);
                $links = $results[$data['type']];
        }


        $links = array_filter($links, fn($item) => !isHiddenPage($item));
        if (!$links) return true;
        usort($links, [$this, 'sortByTitle']);

        $renderer->listu_open();
        foreach ($links as $params) {
            $renderer->listitem_open(1);
            $renderer->listcontent_open();
            switch ($data['type']) {
                case 'backlinks':
                case 'internal':
                    $renderer->internallink($params[0], $params[1]);
                    break;
                case 'external':
                    $renderer->externallink($params[0], $params[1]);
                    break;
                case 'interwiki':
                    $renderer->interwikilink($params[0], $params[1], $params[2], $params[3]);
                    break;
            }
            $renderer->listcontent_close();
            $renderer->listitem_close();
        }
        $renderer->listu_close();


        return true;
    }

    /**
     * Sort the links by title
     *
     * Titles are the second parameter in the array
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sortByTitle($a, $b)
    {
        return Sort::strcmp($a[1], $b[1]);
    }
}
