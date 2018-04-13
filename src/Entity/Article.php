<?php

namespace App\Entity;

/**
 * Class Article.
 */
class Article extends Content implements ArticleInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $number;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $publishedAt;

    /**
     * @var array
     */
    protected $authors;

    /**
     * @var array
     */
    protected $keywords;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $webcode;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $renditions;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var string
     */
    protected $issue;

    /**
     * @var string
     */
    protected $section;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $type;

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
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->getNumber();
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

        if (array_key_exists('text', $fields)) {
            $this->setBody($fields['text']);
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
     * @param string $caption
     *
     * @return Rendition|null
     */
    public function getRendition(string $caption): ?Rendition
    {
        foreach ($this->getRenditions() as $rendition) {
            if ($rendition->getCaption() === $caption) {
                return $rendition;
            }
        }

        return null;
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
        if (null === $this->body) {
            return '';
        }

        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputFileLocation(): string
    {
        return explode('/', $this->getUrl())[2];
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputFileName(): string
    {
        return $this->getNumber().'.json';
    }
}
