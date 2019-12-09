<?php

namespace SM\Payment\Block\Form;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Block\Form;

class PayOnAccount extends Form
{
    /**
     * PayOnAccount constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        $this->_template = 'SM_Payment::form/payonaccount.phtml';
        parent::__construct($context, $data);
    }
}
