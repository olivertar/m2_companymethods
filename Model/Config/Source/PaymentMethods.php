<?php

declare(strict_types=1);

/**
 * This file is part of the Orangecat CompanyMethods package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orangecat\CompanyMethods\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Source model for active payment methods.
 */
class PaymentMethods implements OptionSourceInterface
{
    /**
     * PaymentMethods constructor.
     *
     * @param PaymentMethodListInterface $paymentMethodList
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private PaymentMethodListInterface $paymentMethodList,
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Return array of options as value-label pairs.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $storeId = (int)$this->storeManager->getStore()->getId();
        $methods = $this->paymentMethodList->getActiveList($storeId);

        foreach ($methods as $method) {
            $options[] = [
                'value' => $method->getCode(),
                'label' => $method->getTitle()
            ];
        }

        return $options;
    }
}
