<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData\Templates;

use Virtua\FreshMail\Api\RequestData\Templates\TemplateInterface;
use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;

class Template extends AbstractRequestData implements TemplateInterface
{
    public function __construct(string $hash) {
        $this->setHash($hash);
    }

    public function setHash(string $hash): void
    {
        $this->data['hash'] = $hash;
    }
}
