<?php
/**
 * Created by Nomad
 * Date: 10/14/19
 * Time: 6:44 PM
 */

namespace SM\Payment\Plugin\View\Element;

class Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    public function beforeGetTemplateFile(\Magento\Framework\View\Element\Template $subject)
    {
        if ($this->registry->registry('print_magento_invoice_from_cpos')) {
            $subject->setData('area', 'adminhtml');
            $this->registry->unregister('print_magento_invoice_from_cpos');
        }
    }
}
