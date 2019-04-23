
# Konduto - Magento 2

# Description
Konduto is a fraud detection service that helps e-commerce merchants spot fraud with Buying Behavior.

# Requirements
- PHP **7.x.x** or higher
- MySQL **5.6.x** or higher
- Active account at [Konduto](https://www.konduto.com/ "Konduto")

# Instalattion
It is possible to install the Konduto module for Magento 2 via [.zip](https://github.com/konduto/magento2/archive/master.zip), via [Git](https://github.com) ou via [Composer](https://getcomposer.org).

#### Via [composer](https://getcomposer.org)
- Go to the Magento root directory and add the module:
> `composer require konduto/magento2`
- Update the available Magento modules
> `bin/magento setup:upgrade`
- The ​**Konduto_Antifraud**​ module should be displayed in the list of Magento modules
> `bin/magento module:status`

#### Via [git](https://github.com)
- Go to the Magento root directory and add the module
> `git clone https://github.com/konduto/magento2.git app/code/Konduto/Antifraud/`
- Update the available Magento modules
> `bin/magento setup:upgrade`
- The ​**Konduto_Antifraud**​​ module should be displayed in the list of Magento modules
> `bin/magento module:status`

#### Via [.zip](https://github.com/konduto/magento2/archive/master.zip)
- Create the following folder (s) inside the Magento ​app​​ folder
> `code/Konduto/Antifraud`
- Download [.zip](https://github.com/konduto/magento2/archive/master.zip)
- The path should be ​**app / code / Konduto / Antifraud**
- Extract the **​.zip**​​ files into the ​**Antifraud** folder
- In the root directory, update the available Magento modules
> `bin/magento setup:upgrade`
- The **Konduto_Antifraud** module should be displayed in the list of Magento modules
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

## Doubts
If you need information about the platform or API, please follow the [Konduto Help](https://ajuda.konduto.com/)

## Credits
- [Konduto](https://github.com/konduto)
- [All Contributors](https://github.com/konduto/magento2/graphs/contributors)
