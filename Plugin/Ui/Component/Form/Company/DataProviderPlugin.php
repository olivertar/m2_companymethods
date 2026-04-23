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

namespace Orangecat\CompanyMethods\Plugin\Ui\Component\Form\Company;

use Psr\Log\LoggerInterface;
use Orangecat\Company\Ui\Component\Form\Company\DataProvider;
use Orangecat\CompanyMethods\Api\CompanyMethodsRepositoryInterface;
use Orangecat\CompanyMethods\Api\Data\CompanyMethodsInterface;

/**
 * Plugin for company data provider to load sales methods data.
 */
class DataProviderPlugin
{
    /**
     * DataProviderPlugin constructor.
     *
     * @param CompanyMethodsRepositoryInterface $methodsRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private CompanyMethodsRepositoryInterface $methodsRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * After get data plugin — load assigned methods into form data.
     *
     * @param DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetData(DataProvider $subject, $result)
    {
        if (is_array($result)) {
            foreach ($result as $companyId => $companyData) {
                if ($companyId) {
                    try {
                        $paymentMethods = $this->methodsRepository->getMethodsByCompanyAndType(
                            (int)$companyId,
                            CompanyMethodsInterface::TYPE_PAYMENT
                        );
                        $shippingMethods = $this->methodsRepository->getMethodsByCompanyAndType(
                            (int)$companyId,
                            CompanyMethodsInterface::TYPE_SHIPPING
                        );
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                        $paymentMethods = [];
                        $shippingMethods = [];
                    }

                    $result[$companyId]['sales_methods'] = [
                        'payment_methods' => implode(',', $paymentMethods),
                        'shipping_methods' => implode(',', $shippingMethods)
                    ];
                }
            }
        }

        return $result;
    }
}
