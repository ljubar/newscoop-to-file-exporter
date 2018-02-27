<?php

namespace App\Entity;

/**
 * Class Article.
 */
class Rendition
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $caption;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var ArticleInterface
     */
    protected $article;

    /**
     * @var array
     */
    protected $details;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param mixed $caption
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return urldecode($this->link);
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param mixed $article
     */
    public function setArticle($article)
    {
        $this->article = $article;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }
}
