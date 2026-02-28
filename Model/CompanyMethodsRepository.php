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

namespace Orangecat\CompanyMethods\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Orangecat\CompanyMethods\Api\CompanyMethodsRepositoryInterface;
use Orangecat\CompanyMethods\Api\Data\CompanyMethodsInterface;
use Orangecat\CompanyMethods\Model\ResourceModel\CompanyMethods as CompanyMethodsResource;
use Orangecat\CompanyMethods\Model\ResourceModel\CompanyMethods\CollectionFactory;

/**
 * Repository for company methods.
 */
class CompanyMethodsRepository implements CompanyMethodsRepositoryInterface
{
    /**
     * CompanyMethodsRepository constructor.
     *
     * @param CompanyMethodsResource $resource
     * @param CompanyMethodsFactory $methodsFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private CompanyMethodsResource $resource,
        private CompanyMethodsFactory $methodsFactory,
        private CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function save(CompanyMethodsInterface $method): CompanyMethodsInterface
    {
        try {
            $this->resource->save($method);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save company method: %1', $exception->getMessage()),
                $exception
            );
        }

        return $method;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $entityId): CompanyMethodsInterface
    {
        $method = $this->methodsFactory->create();
        $this->resource->load($method, $entityId);

        if (!$method->getEntityId()) {
            throw new NoSuchEntityException(
                __('The company method with id "%1" does not exist.', $entityId)
            );
        }

        return $method;
    }

    /**
     * @inheritdoc
     */
    public function getByCompanyId(int $companyId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(CompanyMethodsInterface::COMPANY_ID, $companyId);

        return $collection->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getMethodsByCompanyAndType(int $companyId, string $type): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(CompanyMethodsInterface::COMPANY_ID, $companyId);
        $collection->addFieldToFilter(CompanyMethodsInterface::METHOD_TYPE, $type);

        $codes = [];
        foreach ($collection as $item) {
            $codes[] = $item->getMethodCode();
        }

        return $codes;
    }

    /**
     * @inheritdoc
     */
    public function deleteByCompanyId(int $companyId): bool
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(CompanyMethodsInterface::COMPANY_ID, $companyId);

            foreach ($collection as $item) {
                $this->resource->delete($item);
            }
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete company methods: %1', $exception->getMessage()),
                $exception
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteByCompanyIdAndType(int $companyId, string $type): bool
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(CompanyMethodsInterface::COMPANY_ID, $companyId);
            $collection->addFieldToFilter(CompanyMethodsInterface::METHOD_TYPE, $type);

            foreach ($collection as $item) {
                $this->resource->delete($item);
            }
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete company methods: %1', $exception->getMessage()),
                $exception
            );
        }

        return true;
    }
}
