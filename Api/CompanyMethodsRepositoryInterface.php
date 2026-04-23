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

namespace Orangecat\CompanyMethods\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Orangecat\CompanyMethods\Api\Data\CompanyMethodsInterface;

/**
 * Repository interface for company methods operations.
 *
 * @api
 */
interface CompanyMethodsRepositoryInterface
{
    /**
     * Save company method.
     *
     * @param CompanyMethodsInterface $method
     * @return CompanyMethodsInterface
     * @throws CouldNotSaveException
     */
    public function save(CompanyMethodsInterface $method): CompanyMethodsInterface;

    /**
     * Get method by ID.
     *
     * @param int $entityId
     * @return CompanyMethodsInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): CompanyMethodsInterface;

    /**
     * Get all methods by company ID.
     *
     * @param int $companyId
     * @return CompanyMethodsInterface[]
     */
    public function getByCompanyId(int $companyId): array;

    /**
     * Get method codes by company ID and type.
     *
     * @param int $companyId
     * @param string $type
     * @return string[]
     */
    public function getMethodsByCompanyAndType(int $companyId, string $type): array;

    /**
     * Delete all methods for a company.
     *
     * @param int $companyId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteByCompanyId(int $companyId): bool;

    /**
     * Delete all methods for a company by type.
     *
     * @param int $companyId
     * @param string $type
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteByCompanyIdAndType(int $companyId, string $type): bool;
}
