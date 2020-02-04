<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Farmalisto SA <alejandro.villegas@farmalisto.com.co>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `ps_sf_transactions_history` (
`id`  int(11) NOT NULL AUTO_INCREMENT,
`email`  varchar(50) NOT NULL ,
`dni`  varchar(50) NOT NULL ,
`id_saleforce`  varchar(50) NULL ,
`order_unique_id`  varchar(50) NOT NULL ,
`order_id`  varchar(50) NOT NULL ,
`product_name`  varchar(300) NOT NULL ,
`sku_code`  varchar(50) NOT NULL ,
`quantity`  int(11) NOT NULL ,
`invoice_id`  varchar(50) NOT NULL ,
`transaction_type`  varchar(20) NOT NULL ,
`transaction_value`  decimal(11,2) NOT NULL ,
`transaction_date`  varchar(50) NOT NULL ,
`created_date`  DATETIME NOT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
ROW_FORMAT=COMPACT;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
