<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Model\Config\Source\FollowUpEmail;

use Magento\Framework\Data\OptionSourceInterface;

class SendAfterTime implements OptionSourceInterface
{
    /**
     * @var array
     */
    private static $days = [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        11,
        12,
        13,
        14,
        15,
        16,
        17,
        18,
        20,
        21,
        22,
        23,
        24,
        25,
        26,
        27,
        28,
        29,
        30,
    ];

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        $result = $row = [];
        $index = 0;

        foreach (self::$days as $dayNumber) {
            $row['value'] = $dayNumber;
            if ($index > 0) {
                $row['label'] = $dayNumber . ' ' . __('Days');
            } else {
                $row['label'] = $dayNumber . ' ' . __('Day');
            }

            $result[] = $row;
            $index++;
        }

        return $result;
    }
}
