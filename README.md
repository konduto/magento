
# Konduto - Magento 2

# Description
Konduto is a fraud detection service that helps e-commerce merchants spot fraud with Buying Behavior.

# Requirements
- PHP **7.x** or **8.x**
- MySQL **5.6.x** or higher
- Active account at [Konduto](https://www.konduto.com/ "Konduto")

# Installation

#### Via [composer](https://getcomposer.org) (recommended)
- Go to the Magento root directory and add the module:
> `composer require equifax-bvs/konduto-magento2:^1.8`
- Update the available Magento modules
> `bin/magento setup:upgrade`
- The ​**Konduto_Antifraud**​ module should be displayed in the list of Magento modules
> `bin/magento module:status`

#### Via [git](https://github.com)
- Go to the Magento root directory and add the module
> `git clone https://github.com/konduto/magento.git app/code/Konduto/Antifraud/`
- Install the Konduto SDK:
> `composer require konduto/sdk:v2.0.1`
- Update the available Magento modules
> `bin/magento setup:upgrade`
- The ​**Konduto_Antifraud**​​ module should be displayed in the list of Magento modules
> `bin/magento module:status`

# Configuration
1. Setting up your Konduto account
    - In the Magento Administration panel, go to ​**Stores -> Configuration -> Konduto -> Antifraud**
    - In the **Store Environment** field, select the current environment (**Sandbox** or **Production**)
    - Fill in the **Public key** and **Private key** fields with the credentials of your Konduto account
    - Click Save Config
2. Enabling payment methods
    - In the **Settings** tab, in the **Allowed payment methods** field, you must select which payment methods your transactions will be submitted for **Konduto** fraud analysis
    - In the **Payment Mapping** tab, you must select which payment method of the store represents the selected payment method.
3. Enabling order dispatch
    - In the **Settings** tab, in the **Enable Order Dispatch?** field, you must enable this option so that the completed requests are sent to a queue, then sent to the Konduto for analysis

# Fields sent to the Konduto API

Payload built according to the official documentation:
[docs.konduto.com/reference/enviar-um-pedido](https://docs.konduto.com/reference/enviar-um-pedido)

### Order (root)

| Field | Source (Magento) | Notes |
|---|---|---|
| `id` | Order increment ID | required |
| `visitor` | `_kdt` cookie (fingerprint JS) | captured at order placement |
| `total_amount` | Grand total | required |
| `shipping_amount` | Shipping amount | |
| `tax_amount` | Tax amount | sent when > 0 |
| `currency` | Order currency code | falls back to base currency |
| `installments` | Payment `additional_information` (`installments` / `cc_installments`) | required by the API; defaults to `1` |
| `ip` | Order remote IP | IPv4 and IPv6 supported |
| `purchased_at` | Order `created_at` | ISO 8601, UTC (`YYYY-MM-DDTHH:mm:ssZ`) |

### Customer

| Field | Registered customer | Guest |
|---|---|---|
| `id` | Configurable (Customer ID / TaxVat / Email) | e-mail |
| `name` | First + last name | billing first + last name |
| `email` | Account e-mail | order e-mail |
| `dob` | Customer DOB (`YYYY-MM-DD`) | order DOB when available |
| `tax_id` | Mapped CPF/CNPJ attribute or TaxVat | order TaxVat / billing VAT |
| `phone1` | Billing telephone | billing telephone |
| `created_at` | Account creation date | — |
| `new` | — | `true` |

### Payment (list)

Sent as a **list** of payment objects, as required by the API. Card fields are sent
for `credit` **and** `debit`. When the store payment method is not mapped in the
module configuration, the list is omitted (it is optional in the API).

| Field | Notes |
|---|---|
| `type` | `credit`, `debit`, `boleto`, `transfer`, `voucher` (via Payment Mapping config) |
| `amount` | order grand total |
| `status` | `approved` (captured) or `pending` — credit/debit only |
| `bin` | first 6 digits, when available — credit/debit only |
| `last4` | last 4 digits — credit/debit only |
| `expiration_date` | `MMYYYY` (zero-padded) — credit/debit only |

### Billing / Shipping (address)

`name`, `address1` (street, number), `address2` (complement - neighborhood),
`city`, `state`, `zip` (digits only), `country` (ISO 3166-2, defaults to `BR`).
Street/number/complement/neighborhood lines are configurable in **Address Mapping**.

### Shopping cart (list of items)

| Field | Source |
|---|---|
| `sku` | item SKU |
| `name` | item name |
| `unit_cost` | item price (2 decimals) |
| `quantity` | qty ordered |
| `discount` | item discount amount, when > 0 |

Only visible items are sent (children of configurable/bundle products are skipped).

## Doubts
If you need information about the platform or API, please follow the [Konduto Help](https://ajuda.konduto.com/)

## Credits
- [Konduto](https://github.com/konduto)
- [All Contributors](https://github.com/konduto/magento/graphs/contributors)

