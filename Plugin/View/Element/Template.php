<?php
/**
 * Created by Nomad
 * Date: 10/14/19
 * Time: 6:44 PM
 */

namespace SM\Payment\Plugin\View\Element;


class Template
{
    public function beforeGetTemplateFile(\Magento\Framework\View\Element\Template $subject)
    {
        if (!$subject->getData('area')) {
            $subject->setData('area', 'adminhtml');
        }
    }
}
