<?php

namespace App\Entity;

use AHS\Content\ArticleInterface;
use AHS\Content\Content;
use Symfony\Component\Serializer\Annotation\SerializedName;
use AHS\Content\Image;
use function Safe\strtotime;

class Article extends Content implements ArticleInterface
{
    /**
     * @var int
     */
    protected $id;

    protected $number;

    /**
     * @SerializedName("created")
     */
    protected $createdAt;

    /**
     * @SerializedName("published")
     */
    protected $publishedAt;

    /**
     * @var array
     */
    protected $authors = [];

    /**
     * @var array
     */
    protected $keywords = [];

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
    protected $renditions = [];

    /**
     * @var string
     */
    protected $language;

    /**
     * @var array
     */
    protected $issue;

    /**
     * @var array
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
     * @var string
     */
    protected $command;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $description = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getIdentifier()
    {
        return $this->getNumber();
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number = null): void
    {
        $this->number = $number;
    }

    public function getDescription(): ?string
    {
$fields = $this->getFields();
        switch ($this->getType()) {
            case 'insajder':
            case 'news':
                return (null !== $fields['lead_article']) ? $fields['lead_article'] : '';
        }

        return '';
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getImage(): ?Image
    {
/*$articleImage = new Image();
            $articleImage->setId($multimediaImage->getId());
            $articleImage->setDescription($multimediaImage->getTitle());
            $filepathParts = explode('/', $multimediaImage->getFilename());
            $articleImage->setBasename($filepathParts[count($filepathParts) - 1]);
            unset($filepathParts[count($filepathParts) - 1]);
            $articleImage->setLocation(implode('/', $filepathParts));
            $articleImage->setDomain($this->imagesLocation);
            $articleImage->setPhotographer($multimediaImage->getAuthorString());
            $articleImage->setWidth($multimediaImage->getWidth());
            $articleImage->setHeight($multimediaImage->getHeight());

            return $articleImage;
*/
return null;
        // TODO: Implement getImage() method.
    }

  public function getImages(): array
    {
/*        $images = [];
        foreach ($this->getRenditions() as $r) {
            $image = new Image();
            $image->setId(1);
            $image->setDomain('https://insajder.net');
            $image->setBasename(basename($r['link']));
            $image->setDescription($r['details']['caption']);
            $image->setPhotographer($r['details']['photographer']);
            $image->setHeight((string)$r['details']['height']);
            $image->setWidth((string)$r['details']['width']);
        $image->setLocation('');    
        $images[] =$image;
        }

        return $images;
*/
return [];
    }

    public function setImages(array $images = null)
    {
        // TODO: Implement setImages() method.
    }

    public function isPublished(): bool
    {
       return $this->getStatus() === 'Y';
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt)
    {
        if (is_string($createdAt)) {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($createdAt));
        }

        $this->createdAt = $createdAt;
    }

    public function getPublishedAt()
    {
        return $this->publishedAt->format('Y-m-d h:i:s');;
    }

    public function setPublishedAt(string $publishedAt)
    {
        if (is_string($publishedAt)) {
            $publishedAt = (new \DateTime())->setTimestamp(strtotime($publishedAt));
        }

        $this->publishedAt = $publishedAt;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function setAuthors(array $authors)
    {
        $this->authors = $authors;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getWebcode()
    {
        return $this->webcode;
    }

    public function setWebcode($webcode)
    {
        $this->webcode = $webcode;
    }

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

    public function getFields(): array
    {
        return  $this->fields;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getRenditions(): array
    {
        return $this->renditions;
    }

    public function setRenditions(array $renditions)
    {
        $this->renditions = $renditions;
    }

    public function getRendition(string $caption)
    {
        foreach ($this->getRenditions() as $rendition) {
            if ($rendition['caption'] === $caption) {
                return $rendition;
                $originalRendition = new Rendition();
                $originalRendition->setCaption($rendition['caption']);
                
    //    $originalRendition->setMimetype('');
   //     $originalRendition->setWidth((int) $rendition['width']);
  //      $originalRendition->setHeight((int) $rendition['height']);
//        $originalRendition->setMedia($rendition['basename']);
//dump($originalRendition);die;
                return $originalRendition;
            }
        }

        return null;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

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

    public function getSection()
    {
        return $this->section;
    }

    public function setSection($section)
    {
        $this->section = $section;
    }

        public function setExtra(array $extra): void
{
}

    public function getExtra(): array
{
}

    public function getCategories(): array {
}

    public function setCategories(array $categories) {
}
    public function getBody(): string
    {
        if (null === $this->body) {
            return '';
        }

        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    public function getOutputFileLocation(): string
    {
        return explode('/', $this->getUrl())[2];
    }

    public function getOutputFileName(): string
    {
        return $this->getNumber().'.json';
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
