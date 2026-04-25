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

namespace Orangecat\CompanyMethods\Plugin\Shipping;

use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Orangecat\Company\Api\CompanyManagementInterface;
use Orangecat\CompanyMethods\Api\CompanyMethodsRepositoryInterface;
use Orangecat\CompanyMethods\Api\Data\CompanyMethodsInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to filter shipping methods based on company configuration.
 */
class ShippingMethodManagementPlugin
{
    /**
     * ShippingMethodManagementPlugin constructor.
     *
     * @param CompanyManagementInterface $companyManagement
     * @param CompanyMethodsRepositoryInterface $methodsRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private CompanyManagementInterface $companyManagement,
        private CompanyMethodsRepositoryInterface $methodsRepository,
        private CartRepositoryInterface $quoteRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * After estimateByExtendedAddress — filter shipping methods.
     *
     * @param ShippingMethodManagement $subject
     * @param array $result
     * @param int $cartId
     * @return array
     */
    public function afterEstimateByExtendedAddress(
        ShippingMethodManagement $subject,
        array $result,
        $cartId
    ): array {
        return $this->filterShippingMethods($result, (int)$cartId);
    }

    /**
     * After estimateByAddressId — filter shipping methods.
     *
     * @param ShippingMethodManagement $subject
     * @param array $result
     * @param int $cartId
     * @return array
     */
    public function afterEstimateByAddressId(
        ShippingMethodManagement $subject,
        array $result,
        $cartId
    ): array {
        return $this->filterShippingMethods($result, (int)$cartId);
    }

    /**
     * Filter shipping methods based on company configuration.
     *
     * @param array $methods
     * @param int $cartId
     * @return array
     */
    private function filterShippingMethods(array $methods, int $cartId): array
    {
        if (empty($methods)) {
            return $methods;
        }

        try {
            $quote = $this->quoteRepository->getActive($cartId);
            $customerId = $quote->getCustomer()->getId();

            if (!$customerId) {
                return $methods;
            }

            $companyId = $this->companyManagement->getCompanyIdByCustomerId($customerId);
            if (!$companyId) {
                return $methods;
            }

            $allowedMethods = $this->methodsRepository->getMethodsByCompanyAndType(
                (int)$companyId,
                CompanyMethodsInterface::TYPE_SHIPPING
            );

            // If no methods configured, allow all
            if (empty($allowedMethods)) {
                return $methods;
            }

            // Filter methods
            $filtered = [];
            foreach ($methods as $method) {
                /** @var ShippingMethodInterface $method */
                $methodCode = $method->getCarrierCode() . '_' . $method->getMethodCode();
                if (in_array($methodCode, $allowedMethods)) {
                    $filtered[] = $method;
                }
            }

            return $filtered;
        } catch (\Exception $e) {
            $this->logger->error(
                'CompanyMethods: Error filtering shipping methods: ' . $e->getMessage()
            );
            return $methods;
        }
    }
}
