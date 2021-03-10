<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\Email\Container;

class Template
{
    /**
     * @var array
     */
    protected $vars;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    protected $templateId;

    /**
     * @var int
     */
    protected $id;

    /**
     * @param array $vars
     */
    public function setTemplateVars(array $vars): void
    {
        $this->vars = $vars;
    }

    /**
     * @param array $options
     */
    public function setTemplateOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getTemplateVars()
    {
        return $this->vars;
    }

    /**
     * @return array
     */
    public function getTemplateOptions()
    {
        return $this->options;
    }

    public function setTemplateId(int $id): void
    {
        $this->id = $id;
    }

    public function getTemplateId(): int
    {
        return $this->id;
    }
}
