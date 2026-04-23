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

namespace Orangecat\CompanyMethods\Api\Data;

/**
 * Interface for company method entity.
 *
 * @api
 */
interface CompanyMethodsInterface
{
    public const ENTITY_ID = 'entity_id';
    public const COMPANY_ID = 'company_id';
    public const METHOD_TYPE = 'method_type';
    public const METHOD_CODE = 'method_code';

    public const TYPE_PAYMENT = 'payment';
    public const TYPE_SHIPPING = 'shipping';

    /**
     * Get entity ID.
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get company ID.
     *
     * @return int
     */
    public function getCompanyId(): int;

    /**
     * Set company ID.
     *
     * @param int $companyId
     * @return $this
     */
    public function setCompanyId(int $companyId): self;

    /**
     * Get method type.
     *
     * @return string
     */
    public function getMethodType(): string;

    /**
     * Set method type.
     *
     * @param string $methodType
     * @return $this
     */
    public function setMethodType(string $methodType): self;

    /**
     * Get method code.
     *
     * @return string
     */
    public function getMethodCode(): string;

    /**
     * Set method code.
     *
     * @param string $methodCode
     * @return $this
     */
    public function setMethodCode(string $methodCode): self;
}
