<?php
/**
 * Represents as a novel chapter in `laikanshuba.com'.
 *
 * This file is part of NOVEL.READER.
 *
 * NOVEL.READER is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NOVEL.READER is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NOVEL.READER.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   novel.reader
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace CCNR\Model\Laikanshuba_com;

use Exception;
use CCNR\Model;

class Chapter extends Model\Chapter
{
    /**
     * Defines the matched URL pattern.
     *
     * INHERITED from {@link Model\Page::PATTERN}.
     *
     * @var string
     */
    const PATTERN = '~^http://www\.laikanshuba\.com/files/article/html/\d+/\d+/\d+\.html$~';

    /**
     * Parses retrieved content into meta-data.
     *
     * OVERRIDEN FROM {@link Model\Page::parse()}.
     *
     * @param  string  $content
     * @return Chapter
     */
    protected function parse($content)
    {
        settype($content, 'string');
        $content = iconv('gbk', 'utf-8//ignore', $content);
        $this->tocLink = 'index.html';
        $s_ret = $this->crop('@var preview_page = "@', '@";@', $content);
        if (false === $s_ret)
            return $this;
        $this->prevLink = $s_ret;
        if ('index.html' == $this->prevLink)
            $this->prevLink = '';
        $s_ret = $this->crop('@var next_page = "@', '@";@', $content);
        if (false === $s_ret)
            return $this;
        $this->nextLink = $s_ret;
        if ('index.html' == $this->nextLink)
            $this->nextLink = '';
        $s_ret = $this->crop('@-&gt;<a href="index.html">@', '@目录</a>@', $content);
        if (false === $s_ret)
            return $this;
        $this->novelTitle = $s_ret;
        $s_ret = $this->crop('@<div id="title">@', '@</div>@', $content);
        if (false === $s_ret)
            return $this;
        $this->title = $s_ret;
        $s_ret = $this->crop('@<div id="content">(&nbsp;)*@', '@<center>@', $content);
        if (false === $s_ret)
            return $this;
        $this->paragraphs = array();
        if (!strpos($s_ret, '<img src="'))
        {
            $a_tmp = preg_split('@(<br />\s*)+(&nbsp;)*(</p>)?@', $s_ret);
            for ($ii = 0, $jj = count($a_tmp); $ii < $jj; $ii++)
            {
                $a_tmp[$ii] = preg_replace('@^[　]+@u', '', $a_tmp[$ii]);
                if (strlen($a_tmp[$ii]))
                    $this->paragraphs[] = $a_tmp[$ii];
            }
        }
        else if (preg_match_all('@<img src="([^\s"]+)" border="0" class="imagecontent">@', $s_ret, $a_tmp))
        {
            for ($ii = 0, $jj = count($a_tmp[0]); $ii < $jj; $ii++)
                $this->paragraphs[] = '![IMAGE](' . $a_tmp[1][$ii] . ')';
        }
        return $this;
    }
}

# vim:se ft=php ff=unix fenc=utf-8 tw=120:
