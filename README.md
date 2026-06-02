# Orangecat_CompanyMethods

Restrict the payment and shipping methods available to each company at checkout.

**Module:** `Orangecat_CompanyMethods` | **Version:** 1.0.0 | **License:** OSL-3.0 | **Author:** Oliverio Gombert

---

## Table of Contents

1. [Overview](#1-overview)
2. [Theme Compatibility](#2-theme-compatibility)
3. [Requirements](#3-requirements)
4. [Installation](#4-installation)
5. [What Gets Installed](#5-what-gets-installed)
6. [Configuration](#6-configuration)
7. [Store Admin Guide](#7-store-admin-guide)
8. [Buyer Guide (Frontend)](#8-buyer-guide-frontend)
9. [Developer Guide](#9-developer-guide)
10. [REST API](#10-rest-api)
11. [Frontend Routes Reference](#11-frontend-routes-reference)
12. [DevOps & Integrator Notes](#12-devops--integrator-notes)

---

## 1. Overview

`Orangecat_CompanyMethods` lets store administrators define which payment and shipping methods are available to each company. At checkout, the module intercepts Magento's native method-resolution pipeline via plugins and filters the returned lists against the allowed methods stored for the active customer's company.

**Responsibilities:**

- Stores per-company payment and shipping method allowlists in the `mycompany_methods` database table.
- Injects a **Sales Methods** fieldset into the Company admin edit form, exposing multiselect pickers for payment and shipping methods.
- Filters available payment methods at checkout via an `after` plugin on `Magento\Payment\Model\MethodList::getAvailableMethods`.
- Filters available shipping methods at checkout via `after` plugins on `Magento\Quote\Model\ShippingMethodManagement::estimateByExtendedAddress` and `estimateByAddressId`.
- Persists method assignments when a company is saved in the admin via an `after` plugin on `Orangecat\Company\Api\CompanyRepositoryInterface::save`.
- Loads stored assignments back into the admin form via an `after` plugin on the Company `DataProvider`.

### Position in the Orangecat B2B Dependency Chain

```
orangecat/core
  └── Orangecat_Company
        └── Orangecat_CompanyMethods   ← this module
```

### Empty Assignment Semantics

When no methods are stored for a company, the module applies **no restriction** — all store-level payment and shipping methods remain available at checkout. A restriction takes effect only when at least one method code is explicitly saved for that company and type. This is an intentional pass-through default: newly created companies inherit no limits without any admin action required.

---

## 2. Theme Compatibility

| Theme | Status | Notes |
|---|---|---|
| Luma | Supported | All filtering is server-side; no custom templates required |
| Hyvä | Supported | All filtering is server-side; no custom templates required |
| Breeze Evolution | Supported | All filtering is server-side; no custom templates required |

Because the module operates exclusively through backend plugins on Magento's checkout APIs, the method lists presented to the storefront are already filtered before any theme-specific rendering occurs. No theme-specific templates, layout files, or JS components are needed.

---

## 3. Requirements

| Dependency | Version / Notes |
|---|---|
| PHP | >= 8.1 |
| `magento/framework` | `*` |
| `magento/module-payment` | `*` |
| `magento/module-shipping` | `*` |
| `magento/module-quote` | `*` |
| `magento/module-ui` | `*` |
| `orangecat/core` | `*` |
| `orangecat/module-company` | `*` — provides the Company entity and `CompanyManagementInterface` |

---

## 4. Installation

**Step 1 — Add as a git submodule** (if working from the B2B SDK development environment):

```bash
git submodule add git@github.com:olivertar/m2_orangecat_company_methods.git \
    app/code/Orangecat/CompanyMethods
git submodule update --init --recursive
```

**Step 2 — Enable the module** (run inside `reward shell`):

```bash
bin/magento module:enable Orangecat_CompanyMethods
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

---

## 5. What Gets Installed

### Database Tables

#### `mycompany_methods` — Company Payment and Shipping Method Assignments

| Column | Type | Nullable | Notes |
|---|---|---|---|
| `entity_id` | `int(10) unsigned` | No | Primary key, auto-increment |
| `company_id` | `int(10) unsigned` | No | FK → `mycompany.entity_id` (CASCADE DELETE) |
| `method_type` | `varchar(20)` | No | `payment` or `shipping` |
| `method_code` | `varchar(255)` | No | Native Magento method code (e.g. `checkmo`, `flatrate_flatrate`) |

**Constraints:**

- `PRIMARY KEY (entity_id)`
- `FOREIGN KEY (company_id)` → `mycompany (entity_id)` `ON DELETE CASCADE`
- `UNIQUE KEY (company_id, method_type, method_code)` — prevents duplicate assignments

### EAV Attributes

None.

### Data Patches

None. No default records, roles, CMS pages, or seed data are created on install.

---

## 6. Configuration

This module has **no system.xml** and therefore no global or store-scoped configuration stored in `core_config_data`.

All configuration is per-company and is stored directly in the `mycompany_methods` table (see [What Gets Installed](#5-what-gets-installed)). Settings are managed through the Company admin edit form (see [Store Admin Guide](#7-store-admin-guide)).

---

## 7. Store Admin Guide

### Accessing the Sales Methods Settings

1. In the Magento Admin, navigate to **Companies** (the main Companies grid provided by `Orangecat_Company`).
2. Click **Edit** on the target company.
3. Scroll to the **Sales Methods** fieldset near the bottom of the form.

### Sales Methods Fieldset

| Field | Description |
|---|---|
| **Payment Methods** | Multiselect. Lists all active payment methods for the store. Select one or more to restrict this company to those methods. Leave empty to allow all. |
| **Shipping Methods** | Multiselect. Lists all active carriers and their methods. Select one or more to restrict this company to those methods. Leave empty to allow all. |

The **Payment Methods** picker is populated from `Magento\Payment\Api\PaymentMethodListInterface::getActiveList`. The **Shipping Methods** picker lists all active carriers from `Magento\Shipping\Model\Config::getActiveCarriers`, displayed as `Carrier Title - Method Title`.

### Workflow: Restrict a Company to Specific Methods

**Example:** Allow only Purchase Order and Company Credit for payment; restrict shipping to flat rate only.

1. Open the company edit form.
2. In **Payment Methods**, hold `Ctrl`/`Cmd` and select `Purchase Order` and `Company Credit`.
3. In **Shipping Methods**, select `Flat Rate - Fixed`.
4. Click **Save Company**.

From this point, buyers belonging to that company will see only those methods at checkout. All other methods are hidden, regardless of store-level configuration.

### Removing All Restrictions

To re-allow all store methods for a company, open the company form, clear all selections in both multiselects (click each selected item to deselect, or use your browser's multiselect clear mechanism), and save. When no items are stored, the module applies no filtering.

---

## 8. Buyer Guide (Frontend)

There are no dedicated frontend pages or routes in this module. The module's effect is fully transparent to the buyer — restricted methods simply do not appear in the checkout shipping step or payment step.

**What buyers experience:**

- During checkout, only the payment and shipping methods allowed for their company are shown.
- If their company has no restrictions configured, all store-level methods appear as normal.
- If a previously saved cart or session references a method that has since been restricted, the buyer will need to select an available method before completing the order.

No frontend login, dashboard, or URL is associated with this module.

---

## 9. Developer Guide

### Module Structure

```
Orangecat/CompanyMethods/
├── Api/
│   ├── CompanyMethodsRepositoryInterface.php   # Service contract for CRUD
│   └── Data/
│       └── CompanyMethodsInterface.php          # Data transfer object contract
├── Model/
│   ├── CompanyMethods.php                       # ORM model (AbstractModel)
│   ├── CompanyMethodsRepository.php             # Repository implementation
│   ├── Config/Source/
│   │   ├── PaymentMethods.php                   # OptionSource: active payment methods
│   │   └── ShippingMethods.php                  # OptionSource: active shipping carriers/methods
│   └── ResourceModel/
│       ├── CompanyMethods.php                   # Resource model → mycompany_methods
│       └── CompanyMethods/Collection.php        # Collection
├── Plugin/
│   ├── Api/CompanyRepositoryPlugin.php          # Saves method assignments on company save
│   ├── Payment/PaymentMethodListPlugin.php      # Filters payment methods at checkout
│   ├── Shipping/ShippingMethodManagementPlugin.php  # Filters shipping methods at checkout
│   └── Ui/Component/Form/Company/
│       └── DataProviderPlugin.php               # Loads assignments into admin form
├── etc/
│   ├── adminhtml/di.xml                         # Admin-area DI: DataProvider + Repo plugins
│   ├── db_schema.xml                            # mycompany_methods table
│   ├── di.xml                                   # Frontend DI: preferences + checkout plugins
│   └── module.xml
├── view/adminhtml/ui_component/
│   └── mycompany_company_form.xml               # Extends company form with Sales Methods fieldset
└── registration.php
```

### Key Classes

#### `Orangecat\CompanyMethods\Api\Data\CompanyMethodsInterface`

Data object for a single method assignment row.

```php
public function getEntityId();
public function setEntityId($entityId);
public function getCompanyId(): int;
public function setCompanyId(int $companyId): self;
public function getMethodType(): string;   // 'payment' | 'shipping'
public function setMethodType(string $methodType): self;
public function getMethodCode(): string;   // e.g. 'checkmo', 'flatrate_flatrate'
public function setMethodCode(string $methodCode): self;
```

Constants: `TYPE_PAYMENT = 'payment'`, `TYPE_SHIPPING = 'shipping'`.

#### `Orangecat\CompanyMethods\Api\CompanyMethodsRepositoryInterface`

```php
public function save(CompanyMethodsInterface $method): CompanyMethodsInterface;
public function getById(int $entityId): CompanyMethodsInterface;
public function getByCompanyId(int $companyId): array;
public function getMethodsByCompanyAndType(int $companyId, string $type): array;  // returns string[]
public function deleteByCompanyId(int $companyId): bool;
public function deleteByCompanyIdAndType(int $companyId, string $type): bool;
```

### Observers

None — this module registers no event observers.

### Plugins

| Class | Target | Hook | Purpose |
|---|---|---|---|
| `Plugin\Payment\PaymentMethodListPlugin` | `Magento\Payment\Model\MethodList` | `after getAvailableMethods` | Filters the returned payment method list to those allowed for the customer's company |
| `Plugin\Shipping\ShippingMethodManagementPlugin` | `Magento\Quote\Model\ShippingMethodManagement` | `after estimateByExtendedAddress`, `after estimateByAddressId` | Filters the returned shipping method list to those allowed for the customer's company |
| `Plugin\Api\CompanyRepositoryPlugin` | `Orangecat\Company\Api\CompanyRepositoryInterface` | `after save` | Reads `sales_methods` from the POST request and persists payment/shipping assignments |
| `Plugin\Ui\Component\Form\Company\DataProviderPlugin` | `Orangecat\Company\Ui\Component\Form\Company\DataProvider` | `after getData` | Injects stored `payment_methods` and `shipping_methods` comma-separated strings into the form data array |

**Filtering logic (both checkout plugins):** If the company has zero stored methods of a given type, the original result is returned unchanged. If at least one method is stored, only methods whose code appears in the stored list are kept; all others are removed. Errors during lookup are caught, logged, and the original list is returned as a safe fallback.

### JS Components

None — this module ships no JavaScript files.

### Email Templates

None.

### ACL Resources

None — this module does not register ACL resources. Access to the Sales Methods fieldset is governed by the existing Company edit ACL from `Orangecat_Company`.

### Adding Custom Logic

- **Custom filtering logic:** Decorate `Orangecat\CompanyMethods\Api\CompanyMethodsRepositoryInterface` via `di.xml` preference or add a `before` plugin on `PaymentMethodListPlugin::afterGetAvailableMethods` to modify the allowed list before filtering is applied.
- **Custom persistence logic:** Add an `after` plugin on `Orangecat\CompanyMethods\Api\CompanyMethodsRepositoryInterface::save` or override `CompanyRepositoryPlugin::saveMethodsByType` via a preference to add validation or side-effects when assignments change.
- **Extending the admin form:** Add additional fields to the `sales_methods` fieldset by extending `mycompany_company_form.xml` in your own module's `view/adminhtml/ui_component/` directory.

---

## 10. REST API

This module exposes **no REST endpoints**. There is no `etc/webapi.xml`. Method assignment is performed exclusively through the admin UI.

---

## 11. Frontend Routes Reference

This module has **no frontend controllers or routes**. There is no `etc/frontend/routes.xml`. All functionality is backend-only.

---

## 12. DevOps & Integrator Notes

### Deployment Checklist

```bash
# Run inside reward shell (PHP container)
bin/magento module:enable Orangecat_CompanyMethods
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Integration Token Scope

This module has no dedicated ACL resources. An integration token with access to the Company entity (via `Orangecat_Company` ACL resources) is sufficient for any programmatic use. No additional minimum ACL permissions are required.

### Disabling Without Uninstalling

```bash
bin/magento module:disable Orangecat_CompanyMethods
bin/magento setup:upgrade
bin/magento cache:flush
```

When disabled, the checkout filter plugins are deactivated — all store-level payment and shipping methods become available to all companies again. The `mycompany_methods` table and its data **persist** in the database. Re-enabling the module immediately restores all previously configured restrictions without data loss.

### Data Integrity Notes

- **Cascade delete:** Rows in `mycompany_methods` are automatically deleted when the referenced company (`mycompany.entity_id`) is deleted. No orphan cleanup is needed.
- **Unique constraint:** The `(company_id, method_type, method_code)` unique key prevents duplicate assignments. The `CompanyRepositoryPlugin` performs a delete-then-insert strategy on each save, so stale entries are always replaced cleanly.
- **Method code stability:** Stored method codes are raw Magento codes (`checkmo`, `flatrate_flatrate`, etc.). If a payment or shipping provider is removed from the store, its codes remain in `mycompany_methods` as orphaned strings — they will simply never match any available method and have no visible effect. Clean them up manually if needed.
