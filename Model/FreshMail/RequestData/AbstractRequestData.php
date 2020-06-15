<?php
// todo check if it is needed
declare(strict_types=1);

namespace Virtua\FreshMail\Model\FreshMail\RequestData;

abstract class AbstractRequestData
{
    /**
     * @var string[]
     */
    protected $data;

    //todo check if this func is used anywhere
    public function setDataFromJson(string $jsonParams)
    {
        $params = json_decode($jsonParams);

        foreach ($params as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function getDataArray(): array
    {
        return $this->data;
    }
}