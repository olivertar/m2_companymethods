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

namespace Orangecat\CompanyMethods\Plugin\Api;

use Magento\Framework\App\RequestInterface;
use Orangecat\Company\Api\CompanyRepositoryInterface;
use Orangecat\Company\Api\Data\CompanyInterface;
use Orangecat\CompanyMethods\Api\CompanyMethodsRepositoryInterface;
use Orangecat\CompanyMethods\Api\Data\CompanyMethodsInterface;
use Orangecat\CompanyMethods\Api\Data\CompanyMethodsInterfaceFactory;

/**
 * Plugin for company repository to save sales methods data.
 */
class CompanyRepositoryPlugin
{
    /**
     * CompanyRepositoryPlugin constructor.
     *
     * @param RequestInterface $request
     * @param CompanyMethodsRepositoryInterface $methodsRepository
     * @param CompanyMethodsInterfaceFactory $methodsFactory
     */
    public function __construct(
        private RequestInterface $request,
        private CompanyMethodsRepositoryInterface $methodsRepository,
        private CompanyMethodsInterfaceFactory $methodsFactory
    ) {
    }

    /**
     * After save plugin — persist assigned methods.
     *
     * @param CompanyRepositoryInterface $subject
     * @param CompanyInterface $result
     * @param CompanyInterface $company
     * @return CompanyInterface
     */
    public function afterSave(
        CompanyRepositoryInterface $subject,
        CompanyInterface $result,
        CompanyInterface $company
    ) {
        $postData = $this->request->getPostValue();
        if (isset($postData['sales_methods'])) {
            $salesMethodsData = $postData['sales_methods'];
            $companyId = (int)$result->getEntityId();

            if ($companyId) {
                $this->saveMethodsByType(
                    $companyId,
                    CompanyMethodsInterface::TYPE_PAYMENT,
                    $salesMethodsData['payment_methods'] ?? ''
                );
                $this->saveMethodsByType(
                    $companyId,
                    CompanyMethodsInterface::TYPE_SHIPPING,
                    $salesMethodsData['shipping_methods'] ?? ''
                );
            }
        }

        return $result;
    }

    /**
     * Delete existing methods and save new ones for a given type.
     *
     * @param int $companyId
     * @param string $type
     * @param string|array $methods
     * @return void
     */
    private function saveMethodsByType(int $companyId, string $type, $methods): void
    {
        // Delete current assignments for this type
        $this->methodsRepository->deleteByCompanyIdAndType($companyId, $type);

        // Parse methods — can be comma-separated string or array
        if (is_string($methods)) {
            $methodCodes = array_filter(explode(',', $methods));
        } elseif (is_array($methods)) {
            $methodCodes = array_filter($methods);
        } else {
            $methodCodes = [];
        }

        // Create new assignments
        foreach ($methodCodes as $code) {
            $method = $this->methodsFactory->create();
            $method->setCompanyId($companyId);
            $method->setMethodType($type);
            $method->setMethodCode(trim($code));
            $this->methodsRepository->save($method);
        }
    }
}
