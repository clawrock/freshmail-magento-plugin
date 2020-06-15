<?php

declare(strict_types=1);

namespace Virtua\FreshMail\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\StripTags;
use Virtua\FreshMail\Model\System\Config;
use Virtua\FreshMail\Exception\ApiException;
use Virtua\FreshMail\Api\FreshMailApiInterfaceFactory;


class TestConnection extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Virtua_FreshMail::config_freshmail';

    /**
     * @var Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var StripTags
     */
    private $tagFilter;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var FreshMailApiInterfaceFactory
     */
    private $freshMailApiFactory;

    public function __construct(
        Context $context,
        Result\JsonFactory $resultJsonFactory,
        StripTags $tagFilter,
        Config $config,
        FreshMailApiInterfaceFactory $freshMailApiFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tagFilter = $tagFilter;
        $this->config = $config;
        $this->freshMailApiFactory = $freshMailApiFactory;
    }

    public function execute(): Result\Json
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];
        $options = $this->getRequest()->getParams();

        try {
            if (empty($options['bearer_token'])) {
                $error = 'Field bearer token should not be empty!';
                throw new LocalizedException(__($error));
            }
            $bearerToken = $this->resolveBearerToken($options['bearer_token']);
            $freshMailApi = $this->freshMailApiFactory->create($bearerToken);
            $response = $freshMailApi->testConnection();

            if ($response) {
                $result['success'] = true;
            }
        } catch (LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Throwable $e) {
            $message = __($e->getMessage());
            $result['errorMessage'] = $this->tagFilter->filter($message);
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }

    private function resolveBearerToken(string $bearerToken): string
    {
        $readFromConfig = $this->checkToReadFromConfig($bearerToken);
        return $readFromConfig ? '' : $bearerToken;
    }

    private function checkToReadFromConfig(string $string): bool
    {
        if (false !== mb_strpos($string, '*')) {
            return $this->allCharsAreTheSame($string);
        }

        return false;
    }

    private function allCharsAreTheSame(string $string): bool
    {
        $chars = str_split($string);
        $lastChar = $chars[0];
        foreach ($chars as $char) {
            $current = $char;
            if ($current !== $lastChar) {
                return false;
            }

            $lastChar = $char;
        }

        return true;
    }
}
