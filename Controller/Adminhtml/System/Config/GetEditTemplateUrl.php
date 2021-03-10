<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\StripTags;
use Virtua\FreshMail\Api\TemplateRepositoryInterface;
use Virtua\FreshMail\Api\TemplateServiceInterface;

class GetEditTemplateUrl extends Action implements HttpPostActionInterface
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
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var TemplateServiceInterface
     */
    private $templateService;

    public function __construct(
        Context $context,
        Result\JsonFactory $resultJsonFactory,
        StripTags $tagFilter,
        TemplateRepositoryInterface $templateRepository,
        TemplateServiceInterface $templateService
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tagFilter = $tagFilter;
        $this->templateRepository = $templateRepository;
        $this->templateService = $templateService;
    }

    public function execute(): Result\Json
    {
        $result = [
            'success' => false,
            'message' => '',
        ];
        $options = $this->getRequest()->getParams();

        try {
            if (empty($options['template_id'])) {
                $error = __('Field template id should not be empty.');
                throw new LocalizedException($error);
            }

            $template = $this->templateRepository->getById((int) $options['template_id']);
            if ($template->getData('freshmail_id_hash')) {
                $result = [
                    'success' => true,
                    'url' => $this->templateService->getFreshMailTemplateEditLinkByHashId(
                        $template->getData('freshmail_id_hash')
                    ),
                    'new_window' => true
                ];
            } else {
                $result = [
                    'success' => true,
                    'url' => $this->getUrl('adminhtml/email_template/edit', ['id'=> $template->getId()]),
                    'new_window' => true
                ];
            }

        } catch (NoSuchEntityException $e) {
            $result['message'] = __('There is no such template in the database.');
        } catch (LocalizedException $e) {
            $result['message'] = $e->getMessage();
        } catch (\Throwable $e) {
            $message = __($e->getMessage());
            $result['message'] = $this->tagFilter->filter($message);
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }
}
