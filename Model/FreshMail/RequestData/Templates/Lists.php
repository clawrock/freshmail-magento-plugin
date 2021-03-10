<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData\Templates;

use Virtua\FreshMail\Api\RequestData\Templates\ListsInterface;
use Virtua\FreshMail\Model\FreshMail\RequestData\AbstractRequestData;

class Lists extends AbstractRequestData implements ListsInterface
{
    public function __construct(
        ?int $limit = null,
        ?int $offset = null,
        string $directoryName = 'Magento'
    ) {
        if ($limit) {
            $this->setLimit($limit);
        }

        if ($offset) {
            $this->setOffset($offset);
        }

        $this->setDirectoryName($directoryName);
    }

    public function setLimit(int $limit): void
    {
        $this->data['limit'] = $limit;
    }

    public function setOffset(int $offset): void
    {
        $this->data['offset'] = $offset;
    }

    public function setDirectoryName(string $directoryName): void
    {
        $this->data['directory_name'] = $directoryName;
    }
}
