<?php

namespace Omnyfy\Vendor\Plugin\Block\Adminhtml\Role\Tab;

class Info
{
    /**
     * Get form HTML
     *
     * @return string
     */
    public function aroundGetFormHtml(
        \Magento\User\Block\Role\Tab\Info $subject,
        \Closure $proceed
    )
    {
        $form = $subject->getForm();
        if (is_object($form)) {

            $omnyfyFieldset = $form->addFieldset(
                'subvendor_setting_field',
                ['legend' => __('Vendor Settings')]
            );

            $omnyfyFieldset->addField(
                'is_subvendor',
                'select',
                [
                    'name' => 'is_subvendor',
                    'label' => __('Subvendor role?'),
                    'id' => 'is_subvendor',
                    'title' => __('Subvendor role?'),
                    'class' => 'input-select',
                    'options' => ['0' => __('No'), '1' => __('Yes')]
                ]
            );

            // Set data again as we have added a fieldset after the load
            $data =  ['in_role_user_old' => $subject->getOldUsers()];
            if ($subject->getRole() && is_array($subject->getRole()->getData())) {

                $data = array_merge($subject->getRole()->getData(), $data);
            }

            $form->setValues($data);
            $subject->setForm($form);

        }

        return $proceed();
    }
}