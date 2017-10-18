<?php

namespace App\Entity;

/**
 * Class Article.
 */
class Article
{
    protected $id;

    protected $number;

    protected $createdAt;

    protected $publishedAt;

    protected $authors;

    protected $keywords;

    protected $title;

    protected $webcode;

    protected $fields;

    protected $url;

    protected $renditions;

    protected $language;

    protected $issue;

    protected $section;

    protected $body;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * @param mixed $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * @return mixed
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * @param mixed $authors
     */
    public function setAuthors(array $authors)
    {
        $this->authors = $authors;
    }

    /**
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getWebcode()
    {
        return $this->webcode;
    }

    /**
     * @param mixed $webcode
     */
    public function setWebcode($webcode)
    {
        $this->webcode = $webcode;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        if (array_key_exists('tekst', $fields)) {
            $this->setBody($fields['tekst']);
        }

        if (array_key_exists('full_text', $fields)) {
            $this->setBody($fields['full_text']);
        }

        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return  $this->fields;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getRenditions()
    {
        return $this->renditions;
    }

    /**
     * @param mixed $renditions
     */
    public function setRenditions($renditions)
    {
        $this->renditions = $renditions;
    }

    /**
     * @param $caption
     *
     * @return mixed
     */
    public function getRendition($caption)
    {
        foreach ($this->getRenditions() as $rendition) {
            if ($rendition->getCaption() === $caption) {
                return $rendition;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return mixed
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * @param mixed $issue
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;
    }

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param mixed $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
}
