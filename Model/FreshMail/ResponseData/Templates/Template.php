<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\ResponseData\Templates;

use Virtua\FreshMail\Api\ResponseData\Templates\TemplateInterface;

class Template implements TemplateInterface
{
    /**
     * @var string
     */
    private $idHash;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $createdOn;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $productContent;

    /**
     * @var string
     */
    private $thumb;

    /**
     * @var string
     */
    private $description;

    public function __construct(
        string $id_hash,
        string $name,
        string $description,
        string $thumb,
        string $created_on = '',
        string $content_reb = '',
        string $product = ''
    ) {
        $this->setIdHash($id_hash);
        $this->setName($name);
        $this->setDescription($description);
        $this->setThumb($thumb);
        $this->setCreatedOn($created_on);
        $this->setContent($content_reb);
        $this->setProductContent($product);
    }

    public function getIdHash(): string
    {
        return $this->idHash;
    }

    public function setIdHash(string $idHash): void
    {
        $this->idHash = $idHash;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCreatedOn(): string
    {
        return $this->createdOn;
    }

    public function setCreatedOn(string $createdOn): void
    {
        $this->createdOn = $createdOn;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getProductContent(): string
    {
        return $this->productContent;
    }

    public function setProductContent(string $productContent): void
    {
        $this->productContent = $productContent;
    }

    public function getThumb(): string
    {
        return $this->thumb;
    }

    public function setThumb(string $thumb): void
    {
        $this->thumb = $thumb;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
