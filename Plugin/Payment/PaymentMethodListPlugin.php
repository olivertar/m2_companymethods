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

namespace Orangecat\CompanyMethods\Plugin\Payment;

use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\Data\CartInterface;
use Orangecat\Company\Api\CompanyManagementInterface;
use Orangecat\CompanyMethods\Api\CompanyMethodsRepositoryInterface;
use Orangecat\CompanyMethods\Api\Data\CompanyMethodsInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin to filter payment methods based on company configuration.
 */
class PaymentMethodListPlugin
{
    /**
     * PaymentMethodListPlugin constructor.
     *
     * @param CompanyManagementInterface $companyManagement
     * @param CompanyMethodsRepositoryInterface $methodsRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private CompanyManagementInterface $companyManagement,
        private CompanyMethodsRepositoryInterface $methodsRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * After getAvailableMethods — filter based on company assignment.
     *
     * @param MethodList $subject
     * @param array $result
     * @param CartInterface|null $quote
     * @return array
     */
    public function afterGetAvailableMethods(
        MethodList $subject,
        array $result,
        ?CartInterface $quote = null
    ): array {
        if ($quote === null) {
            return $result;
        }

        $customerId = $quote->getCustomer()->getId();
        if (!$customerId) {
            return $result;
        }

        try {
            $companyId = $this->companyManagement->getCompanyIdByCustomerId($customerId);
            if (!$companyId) {
                return $result;
            }

            $allowedMethods = $this->methodsRepository->getMethodsByCompanyAndType(
                (int)$companyId,
                CompanyMethodsInterface::TYPE_PAYMENT
            );

            // If no methods configured, allow all
            if (empty($allowedMethods)) {
                return $result;
            }

            // Filter methods
            $filtered = [];
            foreach ($result as $method) {
                if (in_array($method->getCode(), $allowedMethods)) {
                    $filtered[] = $method;
                }
            }

            return $filtered;
        } catch (\Exception $e) {
            $this->logger->error(
                'CompanyMethods: Error filtering payment methods: ' . $e->getMessage()
            );
            return $result;
        }
    }
}
