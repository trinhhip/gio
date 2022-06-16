<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article\View;

use OmnyfyCustomzation\CmsBlog\Block\Article\AbstractArticle;

/**
 * Cms article view opengraph
 */
class Opengraph extends AbstractArticle
{
    /**
     * Retrieve page type
     *
     * @return string
     */
    public function getType()
    {
        return $this->stripTags(
            $this->getArticle()->getOgType()
        );
    }

    /**
     * Retrieve page title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->stripTags(
            $this->getArticle()->getOgTitle()
        );
    }

    /**
     * Retrieve page short description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->stripTags(
            $this->getArticle()->getOgDescription()
        );
    }

    /**
     * Retrieve page url
     *
     * @return string
     */
    public function getPageUrl()
    {
        return $this->stripTags(
            $this->getArticle()->getArticleUrl()
        );
    }

    /**
     * Retrieve page main image
     *
     * @return string | null
     */
    public function getImage()
    {
        $image = $this->getArticle()->getOgImage();
        if (!$image) {
            $image = $this->getArticle()->getFeaturedImage();
        }
        if (!$image) {
            $content = $this->getContent();
            $match = null;
            preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $match);
            if (!empty($match['src'])) {
                $image = $match['src'];
            }
        }

        if ($image) {
            return $this->stripTags($image);
        }

    }

}
