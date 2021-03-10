<?php
//todo think if it is needed or move this to sender
namespace Virtua\FreshMail\Model\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Virtua\FreshMail\Model\Email\Container\Identity;
use Virtua\FreshMail\Model\Email\Container\Template;

/**
 * Sender Builder
 */
class SenderBuilder
{
    private const EMAIL_SENDER = 'general';

    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var Identity
     */
    protected $identityContainer;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Template $templateContainer
     * @param Identity $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param TransportBuilderByStore $transportBuilderByStore
     */
    public function __construct(
        Template $templateContainer,
        Identity $identityContainer,
        TransportBuilder $transportBuilder,
        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->transportBuilder = $transportBuilder;
    }

    public function send(): void
    {
        $this->configureEmailTemplate();

        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    protected function configureEmailTemplate(): void
    {
        $this->transportBuilder->setTemplateIdentifier($this->templateContainer->getTemplateId());
        $this->transportBuilder->setTemplateOptions($this->templateContainer->getTemplateOptions());
        $this->transportBuilder->setTemplateVars($this->templateContainer->getTemplateVars());
        $this->transportBuilder->setFromByScope(
            self::EMAIL_SENDER,
            $this->identityContainer->getStoreId()
        );
    }
}
