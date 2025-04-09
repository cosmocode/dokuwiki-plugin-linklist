<?php

use dokuwiki\Extension\Plugin;

/**
 * DokuWiki Plugin linklist (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Andreas Gohr <dokuwiki@cosmocode.de>
 */
class helper_plugin_linklist extends Plugin
{


    /**
     * Get the title for this link
     *
     * @param string $link
     * @return string
     */
    public static function getTitle($link)
    {
        global $conf;
        if ($conf['useheading']) {
            $title = p_get_first_heading($link);
            if ($title) return $title;
        }

        $r = new renderer_plugin_linklist();
        return $r->_simpleTitle($link);
    }

    /**
     * Get the title for this URL
     *
     * @param $link
     * @return string
     */
    public static function getUrlTitle($link)
    {
        $url = parse_url($link);
        $title = $url['host'];
        if(str_starts_with($title, 'www.')) {
            $title = substr($title, 4);
        }

        if (isset($url['port'])) $title .= ':' . $url['port'];
        $title .= $url['path'];
        return $title;
    }
}
