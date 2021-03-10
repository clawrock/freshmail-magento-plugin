<?php
declare(strict_types=1);

namespace Virtua\FreshMail\Rewrite\Block\Email\Adminhtml\Template\Edit;

use Magento\Email\Block\Adminhtml\Template\Edit\Form as CoreForm;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Variable\Model\VariableFactory;
use Magento\Variable\Model\Source\Variables;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Virtua\FreshMail\Api\TemplateServiceInterface;

class Form extends CoreForm
{
    /**
     * @var Json|mixed|null
     */
    private $serializer;

    /**
     * @var TemplateServiceInterface
     */
    private $templateService;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        VariableFactory $variableFactory,
        Variables $variables,
        TemplateServiceInterface $templateService,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct($context, $registry, $formFactory, $variableFactory, $variables, $data, $serializer);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->templateService = $templateService;
    }

    /**
     * Add fields to form and create template info form
     *
     * @return \Magento\Backend\Block\Widget\Form
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Template Information'), 'class' => 'fieldset-wide']
        );

        $templateId = $this->getEmailTemplate()->getId();
        $fieldset->addField(
            'currently_used_for',
            'label',
            [
                'label' => __('Currently Used For'),
                'container_id' => 'currently_used_for',
                'after_element_html' => '<script>require(["prototype"], function () {' .
                    (!$this->getEmailTemplate()->getSystemConfigPathsWhereCurrentlyUsed() ? '$(\'' .
                        'currently_used_for' .
                        '\').hide(); ' : '') .
                    '});</script>'
            ]
        );

        $fieldset->addField(
            'template_code',
            'text',
            ['name' => 'template_code', 'label' => __('Template Name'), 'required' => true]
        );
        $fieldset->addField(
            'template_subject',
            'text',
            ['name' => 'template_subject', 'label' => __('Template Subject'), 'required' => true]
        );
        $fieldset->addField('orig_template_variables', 'hidden', ['name' => 'orig_template_variables']);
        $fieldset->addField(
            'variables',
            'hidden',
            ['name' => 'variables', 'value' => $this->serializer->serialize($this->getVariables())]
        );
        $fieldset->addField('template_variables', 'hidden', ['name' => 'template_variables']);

        $insertVariableButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class,
            '',
            [
                'data' => [
                    'type' => 'button',
                    'label' => __('Insert Variable...'),
                    'onclick' => 'templateControl.openVariableChooser();return false;',
                ]
            ]
        );

        $fieldset->addField('insert_variable', 'note', ['text' => $insertVariableButton->toHtml(), 'label' => '']);

        $textAreaConfig = [
            'name' => 'template_text',
            'label' => __('Template Content'),
            'title' => __('Template Content'),
            'required' => true,
            'style' => 'height:24em;'
        ];

        if ($this->isFreshMailTemplate()) {
            $textAreaConfig['disabled'] = 'disabled';
        }

        $fieldset->addField(
            'template_text',
            'textarea',
            $textAreaConfig
        );

        if ($this->isFreshMailTemplate()) {
            $fieldset->addField(
                'freshmail_template_edit_link',
                'link',
                [
                    'value' => __('Edit template in FreshMail'),
                    'href' => $this->getEditTemplateInFreshMailLink($this->getEmailTemplate()
                        ->getData('freshmail_id_hash')),
                    'title' => __('Edit template in FreshMail'),
                    'target' => '_blank',
                    'label' => '',
                    'after_element_html' => $this->getEditTemplateInFreshMailCommentHtml()
                ],
                'template_text'
            );
        }

        if (!$this->getEmailTemplate()->isPlain()) {
            $fieldset->addField(
                'template_styles',
                'textarea',
                [
                    'name' => 'template_styles',
                    'label' => __('Template Styles'),
                    'container_id' => 'field_template_styles'
                ]
            );
        }

        if ($templateId) {
            $form->addValues($this->getEmailTemplate()->getData());
        }

        $values = $this->_backendSession->getData('email_template_form_data', true);
        if ($values) {
            $form->setValues($values);
        }

        $this->setForm($form);

        return $this;
    }

    private function isFreshMailTemplate(): bool
    {
        return $this->getEmailTemplate()->getData('freshmail_id_hash') ? true : false;
    }

    private function getEditTemplateInFreshMailLink(string $hash): string
    {
        return $this->templateService->getFreshMailTemplateEditLinkByHashId($hash);
    }

    private function getEditTemplateInFreshMailCommentHtml(): string
    {
        return '<p style="font-size:10px">'
            . __("Template will open in FreshMail editor. After making changes and saving template in FreshMail, go to Magento and click on 'Synchronize templates' to see the changes.")
            . '</p>';
    }
}
