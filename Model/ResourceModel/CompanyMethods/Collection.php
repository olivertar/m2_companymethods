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

namespace Orangecat\CompanyMethods\Model\ResourceModel\CompanyMethods;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Orangecat\CompanyMethods\Model\CompanyMethods as CompanyMethodsModel;
use Orangecat\CompanyMethods\Model\ResourceModel\CompanyMethods as CompanyMethodsResource;

/**
 * Company methods collection.
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize collection.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CompanyMethodsModel::class, CompanyMethodsResource::class);
    }
}
