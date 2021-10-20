<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData;

abstract class AbstractRequestData
{
    /**
     * @var string[]
     */
    protected $data;

    public function getDataArray(): array
    {
        return $this->data;
    }
}
