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

namespace Orangecat\CompanyMethods\Model;

use Magento\Framework\Model\AbstractModel;
use Orangecat\CompanyMethods\Api\Data\CompanyMethodsInterface;

/**
 * Company methods model.
 */
class CompanyMethods extends AbstractModel implements CompanyMethodsInterface
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Orangecat\CompanyMethods\Model\ResourceModel\CompanyMethods::class);
    }

    /**
     * @inheritdoc
     */
    public function getCompanyId(): int
    {
        return (int)$this->getData(self::COMPANY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyId(int $companyId): CompanyMethodsInterface
    {
        return $this->setData(self::COMPANY_ID, $companyId);
    }

    /**
     * @inheritdoc
     */
    public function getMethodType(): string
    {
        return (string)$this->getData(self::METHOD_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setMethodType(string $methodType): CompanyMethodsInterface
    {
        return $this->setData(self::METHOD_TYPE, $methodType);
    }

    /**
     * @inheritdoc
     */
    public function getMethodCode(): string
    {
        return (string)$this->getData(self::METHOD_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setMethodCode(string $methodCode): CompanyMethodsInterface
    {
        return $this->setData(self::METHOD_CODE, $methodCode);
    }
}
