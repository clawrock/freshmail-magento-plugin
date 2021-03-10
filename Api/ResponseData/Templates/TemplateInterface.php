<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Api\ResponseData\Templates;

interface TemplateInterface
{
    public function getIdHash(): string;

    public function setIdHash(string $idHash): void;

    public function getName(): string;

    public function setName(string $name): void;

    public function getDescription(): string;

    public function setDescription(string $description): void;

    public function getCreatedOn(): string;

    public function setCreatedOn(string $createdOn): void;

    public function getContent(): string;

    public function setContent(string $content): void;

    public function getProductContent(): string;

    public function setProductContent(string $productContent): void;

    public function getThumb(): string;

    public function setThumb(string $thumb): void;
}
