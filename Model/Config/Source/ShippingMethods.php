<?php
/**
 * This file is part of the Orangecat CompanyMethods package.
 *
 * (c) Oliverio Gombert <olivertar@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Orangecat\CompanyMethods\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Source model for active shipping methods.
 */
class ShippingMethods implements OptionSourceInterface
{
    /**
     * ShippingMethods constructor.
     *
     * @param ShippingConfig $shippingConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private ShippingConfig $shippingConfig,
        private ScopeConfigInterface $scopeConfig
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
        $carriers = $this->shippingConfig->getActiveCarriers();

        foreach ($carriers as $carrierCode => $carrierModel) {
            $carrierTitle = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if (!$carrierTitle) {
                $carrierTitle = $carrierCode;
            }

            $carrierMethods = $carrierModel->getAllowedMethods();
            if ($carrierMethods) {
                foreach ($carrierMethods as $methodCode => $methodTitle) {
                    $value = $carrierCode . '_' . $methodCode;
                    $label = $carrierTitle . ' - ' . ($methodTitle ?: $methodCode);
                    $options[] = [
                        'value' => $value,
                        'label' => $label
                    ];
                }
            }
        }

        return $options;
    }
}
