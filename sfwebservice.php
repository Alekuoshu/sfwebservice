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

// error_reporting(E_ALL);
// ini_set('display_errors', 1);


if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\ServiceLocator; // for hash passw

const SFWEBSERVICE_PATH_LOG = _PS_ROOT_DIR_ . "/modules/sfwebservice/log/";


class Sfwebservice extends Module
{
    protected $config_form = false;
    // public $confirmations = array();
    private $SFWEBSERVICE_LIVE_MODE;
    private $SFWEBSERVICE_SECRET_STRING;
    private $SFWEBSERVICE_KEY_FOR_HASH;
    private $SFWEBSERVICE_ACTIVE;
    private $SFWEBSERVICE_CLIENT_ID;
    private $SFWEBSERVICE_CLIENT_SECRET;
    private $SFWEBSERVICE_USERNAME;
    private $SFWEBSERVICE_PASSWORD;
    private $SFWEBSERVICE_URL_POST_DATA;
    private $SFWEBSERVICE_URL_GET_DATA;
    private $SFWEBSERVICE_URL_GET_TOKEN;
    private $SFWEBSERVICE_GRANT_TYPE;
    private $SFWEBSERVICE_GET_ID_ORDER;
    // variables for sf integration register and login
    private $SFWEBSERVICE_ACTIVE_2;
    private $SFWEBSERVICE_CLIENT_ID_2;
    private $SFWEBSERVICE_CLIENT_SECRET_2;
    private $SFWEBSERVICE_USERNAME_2;
    private $SFWEBSERVICE_PASSWORD_2;
    private $SFWEBSERVICE_GRANT_TYPE_2;
    private $SFWEBSERVICE_URL_GET_TOKEN_2;
    private $SFWEBSERVICE_CLIENT_SECRETKEY_PASSW;
    private $SFWEBSERVICE_URL_CONTACT_INSERT;
    private $SFWEBSERVICE_URL_CONTACT_UPDATE;
    private $SFWEBSERVICE_URL_LOGIN;
    private $SFWEBSERVICE_URL_RESET_FORGOT;
    private $SFWEBSERVICE_URL_CHANGE_PASSW;
    private $SFWEBSERVICE_URL_EMAIL_VERIFY;
    private $SFWEBSERVICE_URL_SET_TOKEN;
    private $SFWEBSERVICE_URL_RESET_OTP;
    private $SFWEBSERVICE_URL_GET_PROFILE;
    private $SFWEBSERVICE_PRIVACY_POLICY;
    private $SFWEBSERVICE_PRIVACY_POLICY_2;
    private $SFWEBSERVICE_TERMS_CONDITIONS;


    public function __construct()
    {
        $this->name = 'sfwebservice';
        $this->tab = 'front_office_features';
        $this->version = '1.3.3';
        $this->author = 'Alekuoshu';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('SF Web Service');
        $this->description = $this->l('Module for SaleForce Integration');

        $this->confirmUninstall = $this->l('Are you sure you want uninstall this module?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->SFWEBSERVICE_LIVE_MODE = Configuration::get('SFWEBSERVICE_LIVE_MODE');
        $this->SFWEBSERVICE_SECRET_STRING = Configuration::get('SFWEBSERVICE_SECRET_STRING');
        $this->SFWEBSERVICE_KEY_FOR_HASH = Configuration::get('SFWEBSERVICE_KEY_FOR_HASH');
        $this->SFWEBSERVICE_ACTIVE = Configuration::get('SFWEBSERVICE_ACTIVE');
        $this->SFWEBSERVICE_CLIENT_ID = Configuration::get('SFWEBSERVICE_CLIENT_ID');
        $this->SFWEBSERVICE_CLIENT_SECRET = Configuration::get('SFWEBSERVICE_CLIENT_SECRET');
        $this->SFWEBSERVICE_USERNAME = Configuration::get('SFWEBSERVICE_USERNAME');
        $this->SFWEBSERVICE_PASSWORD = Configuration::get('SFWEBSERVICE_PASSWORD');
        $this->SFWEBSERVICE_URL_POST_DATA = Configuration::get('SFWEBSERVICE_URL_POST_DATA');
        $this->SFWEBSERVICE_URL_GET_DATA = Configuration::get('SFWEBSERVICE_URL_GET_DATA');
        $this->SFWEBSERVICE_URL_GET_TOKEN = Configuration::get('SFWEBSERVICE_URL_GET_TOKEN');
        $this->SFWEBSERVICE_GRANT_TYPE = Configuration::get('SFWEBSERVICE_GRANT_TYPE');
        $this->SFWEBSERVICE_GET_ID_ORDER = Tools::getValue('SFWEBSERVICE_GET_ID_ORDER');
        // variables for sf integration register and login
        $this->SFWEBSERVICE_ACTIVE_2 = Configuration::get('SFWEBSERVICE_ACTIVE_2');
        $this->SFWEBSERVICE_CLIENT_ID_2 = Configuration::get('SFWEBSERVICE_CLIENT_ID_2');
        $this->SFWEBSERVICE_CLIENT_SECRET_2 = Configuration::get('SFWEBSERVICE_CLIENT_SECRET_2');
        $this->SFWEBSERVICE_USERNAME_2 = Configuration::get('SFWEBSERVICE_USERNAME_2');
        $this->SFWEBSERVICE_PASSWORD_2 = Configuration::get('SFWEBSERVICE_PASSWORD_2');
        $this->SFWEBSERVICE_GRANT_TYPE_2 = Configuration::get('SFWEBSERVICE_GRANT_TYPE_2');
        $this->SFWEBSERVICE_URL_GET_TOKEN_2 = Configuration::get('SFWEBSERVICE_URL_GET_TOKEN_2');
        $this->SFWEBSERVICE_CLIENT_SECRETKEY_PASSW = Configuration::get('SFWEBSERVICE_CLIENT_SECRETKEY_PASSW');
        $this->SFWEBSERVICE_URL_CONTACT_INSERT = Configuration::get('SFWEBSERVICE_URL_CONTACT_INSERT');
        $this->SFWEBSERVICE_URL_CONTACT_UPDATE = Configuration::get('SFWEBSERVICE_URL_CONTACT_UPDATE');
        $this->SFWEBSERVICE_URL_LOGIN = Configuration::get('SFWEBSERVICE_URL_LOGIN');
        $this->SFWEBSERVICE_URL_RESET_FORGOT = Configuration::get('SFWEBSERVICE_URL_RESET_FORGOT');
        $this->SFWEBSERVICE_URL_CHANGE_PASSW = Configuration::get('SFWEBSERVICE_URL_CHANGE_PASSW');
        $this->SFWEBSERVICE_URL_EMAIL_VERIFY = Configuration::get('SFWEBSERVICE_URL_EMAIL_VERIFY');
        $this->SFWEBSERVICE_URL_SET_TOKEN = Configuration::get('SFWEBSERVICE_URL_SET_TOKEN');
        $this->SFWEBSERVICE_URL_RESET_OTP = Configuration::get('SFWEBSERVICE_URL_RESET_OTP');
        $this->SFWEBSERVICE_URL_GET_PROFILE = Configuration::get('SFWEBSERVICE_URL_GET_PROFILE');
        $this->SFWEBSERVICE_PRIVACY_POLICY = Configuration::get('SFWEBSERVICE_PRIVACY_POLICY');
        $this->SFWEBSERVICE_PRIVACY_POLICY_2 = Configuration::get('SFWEBSERVICE_PRIVACY_POLICY_2');
        $this->SFWEBSERVICE_TERMS_CONDITIONS = Configuration::get('SFWEBSERVICE_TERMS_CONDITIONS');

        // init this function
        $this->sendDataMasive();

    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('SFWEBSERVICE_LIVE_MODE', false);
        Configuration::updateValue('SFWEBSERVICE_SECRET_STRING', '') &&
        Configuration::updateValue('SFWEBSERVICE_KEY_FOR_HASH', '') &&
        Configuration::updateValue('SFWEBSERVICE_ACTIVE', '') &&
        Configuration::updateValue('SFWEBSERVICE_CLIENT_ID', '') &&
        Configuration::updateValue('SFWEBSERVICE_CLIENT_SECRET', '') &&
        Configuration::updateValue('SFWEBSERVICE_USERNAME', '') &&
        Configuration::updateValue('SFWEBSERVICE_PASSWORD', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_POST_DATA', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_GET_DATA', '') &&
        Configuration::updateValue('SFWEBSERVICE_GRANT_TYPE', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_GET_TOKEN', '') &&
        Configuration::updateValue('SFWEBSERVICE_ACTIVE_2', '') &&
        Configuration::updateValue('SFWEBSERVICE_CLIENT_ID_2', '') &&
        Configuration::updateValue('SFWEBSERVICE_CLIENT_SECRET_2', '') &&
        Configuration::updateValue('SFWEBSERVICE_USERNAME_2', '') &&
        Configuration::updateValue('SFWEBSERVICE_PASSWORD_2', '') &&
        Configuration::updateValue('SFWEBSERVICE_GRANT_TYPE_2', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_GET_TOKEN_2', '') &&
        Configuration::updateValue('SFWEBSERVICE_CLIENT_SECRETKEY_PASSW', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_CONTACT_INSERT', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_CONTACT_UPDATE', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_LOGIN', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_RESET_FORGOT', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_CHANGE_PASSW', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_EMAIL_VERIFY', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_SET_TOKEN', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_RESET_OTP', '') &&
        Configuration::updateValue('SFWEBSERVICE_URL_GET_PROFILE', '') &&
        Configuration::updateValue('SFWEBSERVICE_PRIVACY_POLICY', '') &&
        Configuration::updateValue('SFWEBSERVICE_PRIVACY_POLICY_2', '') &&
        Configuration::updateValue('SFWEBSERVICE_TERMS_CONDITIONS', '');

        // copy some custom tpl to current theme
        // pediasure
        // $fromCustomerPartials = _PS_ROOT_DIR_.'/modules/sfwebservice/theme/files_to_copy/templates/customer/_partials/';
        // $toCustomerPartials_pe = _PS_ROOT_DIR_.'/themes/dekora_theme4/templates/customer/_partials/';
        // $fromCustomer = _PS_ROOT_DIR_.'/modules/sfwebservice/theme/files_to_copy/templates/customer/';
        // $toCustomer_pe = _PS_ROOT_DIR_.'/themes/dekora_theme4/templates/customer/';
        // $fromPartials = _PS_ROOT_DIR_.'/modules/sfwebservice/theme/files_to_copy/templates/_partials/';
        // $toPartials_pe = _PS_ROOT_DIR_.'/themes/dekora_theme4/templates/_partials/';
        // // copy js required
        // $fromAssetsJs = _PS_ROOT_DIR_.'/modules/sfwebservice/assetts/js/';
        // $toAssetsJs_pe = _PS_ROOT_DIR_.'/themes/dekora_theme4/assets/js/';
        
        // $this->copyCustomFiles($fromCustomerPartials, $toCustomerPartials_pe);
        // $this->copyCustomFiles2($fromCustomer, $toCustomer_pe);
        // $this->copyCustomFiles3($fromPartials, $toPartials_pe);
        // $this->copyCustomFiles4($fromAssetsJs, $toAssetsJs_pe);

        // // ensure
        // $toCustomerPartials_en = _PS_ROOT_DIR_.'/themes/dekora_theme4_ensure/templates/customer/_partials/';
        // $toCustomer_en = _PS_ROOT_DIR_.'/themes/dekora_theme4_ensure/templates/customer/';
        // $toPartials_en = _PS_ROOT_DIR_.'/themes/dekora_theme4_ensure/templates/_partials/';
        // // copy js required
        // $toAssetsJs_en = _PS_ROOT_DIR_.'/themes/dekora_theme4_ensure/assets/js/';

        // $this->copyCustomFiles($fromCustomerPartials, $toCustomerPartials_en);
        // $this->copyCustomFiles2($fromCustomer, $toCustomer_en);
        // $this->copyCustomFiles3($fromPartials, $toPartials_en);
        // $this->copyCustomFiles4($fromAssetsJs, $toAssetsJs_en);

        // // glucerna
        // $toCustomerPartials_glu = _PS_ROOT_DIR_.'/themes/dekora_theme4_glucerna/templates/customer/_partials/';
        // $toCustomer_glu = _PS_ROOT_DIR_.'/themes/dekora_theme4_glucerna/templates/customer/';
        // $toPartials_glu = _PS_ROOT_DIR_.'/themes/dekora_theme4_glucerna/templates/_partials/';
        // // copy js required
        // $toAssetsJs_glu = _PS_ROOT_DIR_.'/themes/dekora_theme4_glucerna/assets/js/';

        // $this->copyCustomFiles($fromCustomerPartials, $toCustomerPartials_glu);
        // $this->copyCustomFiles2($fromCustomer, $toCustomer_glu);
        // $this->copyCustomFiles3($fromPartials, $toPartials_glu);
        // $this->copyCustomFiles4($fromAssetsJs, $toAssetsJs_glu);

        // // similac
        // $toCustomerPartials_simi = _PS_ROOT_DIR_.'/themes/dekora_theme4_similac/templates/customer/_partials/';
        // $toCustomer_simi = _PS_ROOT_DIR_.'/themes/dekora_theme4_similac/templates/customer/';
        // $toPartials_simi = _PS_ROOT_DIR_.'/themes/dekora_theme4_similac/templates/_partials/';
        // // copy js required
        // $toAssetsJs_simi = _PS_ROOT_DIR_.'/themes/dekora_theme4_similac/assets/js/';

        // $this->copyCustomFiles($fromCustomerPartials, $toCustomerPartials_simi);
        // $this->copyCustomFiles2($fromCustomer, $toCustomer_simi);
        // $this->copyCustomFiles3($fromPartials, $toPartials_simi);
        // $this->copyCustomFiles4($fromAssetsJs, $toAssetsJs_simi);


        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHomeSF') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SFWEBSERVICE_LIVE_MODE');
        Configuration::deleteByName('SFWEBSERVICE_SECRET_STRING') &&
        Configuration::deleteByName('SFWEBSERVICE_KEY_FOR_HASH') &&
        Configuration::deleteByName('SFWEBSERVICE_ACTIVE') &&
        Configuration::deleteByName('SFWEBSERVICE_CLIENT_ID') &&
        Configuration::deleteByName('SFWEBSERVICE_CLIENT_SECRET') &&
        Configuration::deleteByName('SFWEBSERVICE_USERNAME') &&
        Configuration::deleteByName('SFWEBSERVICE_PASSWORD') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_POST_DATA') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_GET_DATA') &&
        Configuration::deleteByName('SFWEBSERVICE_GRANT_TYPE') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_GET_TOKEN') &&
        Configuration::deleteByName('SFWEBSERVICE_ACTIVE_2') &&
        Configuration::deleteByName('SFWEBSERVICE_CLIENT_ID_2') &&
        Configuration::deleteByName('SFWEBSERVICE_CLIENT_SECRET_2') &&
        Configuration::deleteByName('SFWEBSERVICE_USERNAME_2') &&
        Configuration::deleteByName('SFWEBSERVICE_PASSWORD_2') &&
        Configuration::deleteByName('SFWEBSERVICE_GRANT_TYPE_2') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_GET_TOKEN_2') &&
        Configuration::deleteByName('SFWEBSERVICE_CLIENT_SECRETKEY_PASSW') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_CONTACT_INSERT') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_CONTACT_UPDATE') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_LOGIN') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_RESET_FORGOT') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_CHANGE_PASSW') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_EMAIL_VERIFY') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_SET_TOKEN') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_RESET_OTP') &&
        Configuration::deleteByName('SFWEBSERVICE_URL_GET_PROFILE') &&
        Configuration::deleteByName('SFWEBSERVICE_PRIVACY_POLICY') &&
        Configuration::deleteByName('SFWEBSERVICE_PRIVACY_POLICY_2') &&
        Configuration::deleteByName('SFWEBSERVICE_TERMS_CONDITIONS');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall() &&
            $this->unRegisterHook('header') &&
            $this->unRegisterHook('backOfficeHeader') &&
            $this->unRegisterHook('displayHomeSF') &&
            $this->unRegisterHook('actionOrderStatusPostUpdate') &&
            $this->unRegisterHook('displayHome');
    }

    /**
     * Copy some files to current theme
     */
    public function copyCustomFiles($from, $to)
    {
        //Abro el directorio que voy a leer
        $dir = opendir($from);
        //Recorro el directorio para leer los archivos que tiene
        while(($file = readdir($dir)) !== false){
            if(strpos($file, '.') !== 0){
                //Copio el archivo manteniendo el mismo nombre en la nueva carpeta
                $success = copy($from.$file, $to.$file);
            }
        }

        // if($success){
        //     error_log("1-Instalando modulo: Copiado de tpl exitoso!");
        // }else {
        //     error_log("1-Instalando modulo: Error al copiar tpl!");
        // }

        closedir($dir);

    }
    public function copyCustomFiles2($from, $to)
    {
        //Abro el directorio que voy a leer
        $dir = opendir($from);
        //Recorro el directorio para leer los archivos que tiene
        while(($file = readdir($dir)) !== false){
            if(strpos($file, '.') !== 0){
                //Copio el archivo manteniendo el mismo nombre en la nueva carpeta
                $success = copy($from.$file, $to.$file);
            }
        }

        // if($success){
        //     error_log("2-Instalando modulo: Copiado de tpl exitoso!");
        // }else {
        //     error_log("2-Instalando modulo: Error al copiar tpl!");
        // }

        closedir($dir);

    }
    public function copyCustomFiles3($from, $to)
    {
        //Abro el directorio que voy a leer
        $dir = opendir($from);
        //Recorro el directorio para leer los archivos que tiene
        while(($file = readdir($dir)) !== false){
            if(strpos($file, '.') !== 0){
                //Copio el archivo manteniendo el mismo nombre en la nueva carpeta
                $success = copy($from.$file, $to.$file);
            }
        }

        // if($success){
        //     error_log("3-Instalando modulo: Copiado de tpl exitoso!");
        // }else {
        //     error_log("3-Instalando modulo: Error al copiar tpl!");
        // }

        closedir($dir);

    }
    public function copyCustomFiles4($from, $to)
    {
        //Abro el directorio que voy a leer
        $dir = opendir($from);
        //Recorro el directorio para leer los archivos que tiene
        while(($file = readdir($dir)) !== false){
            if(strpos($file, '.') !== 0){
                //Copio el archivo manteniendo el mismo nombre en la nueva carpeta
                $success = copy($from.$file, $to.$file);
            }
        }

        // if($success){
        //     error_log("4-Instalando modulo: Copiado de tpl exitoso!");
        // }else {
        //     error_log("4-Instalando modulo: Error al copiar tpl!");
        // }

        closedir($dir);

    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitSfwebserviceModule')) == true) {
            $this->postProcess();

            $id_orderM = $this->SFWEBSERVICE_GET_ID_ORDER;
            if($id_orderM){
                self::logtxt("Entered in manually mode... ID_Order: $id_orderM");
                $this->sendDataManually($id_orderM);

            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSfwebserviceModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm(), $this->getCredentialsForm(), $this->getSendManuallyForm(), $this->getSFIntegrationForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings Login / Resgistration Mode'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'SFWEBSERVICE_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-paragraph"></i>',
                        'desc' => $this->l('Enter a secret string'),
                        'name' => 'SFWEBSERVICE_SECRET_STRING',
                        'label' => $this->l('Secret String'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter a key for hash'),
                        'name' => 'SFWEBSERVICE_KEY_FOR_HASH',
                        'label' => $this->l('Key for hash'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Create the structure of your form for credentials.
     */
    protected function getCredentialsForm()
    {
    return array(
        // API consumme credentials
    'form' => array(
        'legend' => array(
        'title' => $this->l('API sent data consumme credentials'),
        'icon' => 'icon-key',
        ),
        'input' => array(
        array(
            'type' => 'switch',
            'label' => $this->l('API Active'),
            'name' => 'SFWEBSERVICE_ACTIVE',
            'is_bool' => true,
            'desc' => $this->l('Active this options'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            ),
        ),
        array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '<i class="icon icon-cog"></i>',
            'desc' => $this->l('Enter grant type'),
            'name' => 'SFWEBSERVICE_GRANT_TYPE',
            'label' => $this->l('Grant Type'),
        ),
        array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '<i class="icon icon-link"></i>',
            'desc' => $this->l('Enter URL for get token'),
            'name' => 'SFWEBSERVICE_URL_GET_TOKEN',
            'label' => $this->l('URL for Token'),
        ),
        array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '<i class="icon icon-asterisk"></i>',
            'desc' => $this->l('Enter a client id'),
            'name' => 'SFWEBSERVICE_CLIENT_ID',
            'label' => $this->l('Client Id'),
        ),
        array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '<i class="icon icon-lock"></i>',
            'desc' => $this->l('Enter a secret id'),
            'name' => 'SFWEBSERVICE_CLIENT_SECRET',
            'label' => $this->l('Client Secret'),
        ),
        array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '<i class="icon icon-user"></i>',
            'desc' => $this->l('Enter a user'),
            'name' => 'SFWEBSERVICE_USERNAME',
            'label' => $this->l('Username'),
        ),
        array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '<i class="icon icon-key"></i>',
            'desc' => $this->l('Enter a password'),
            'name' => 'SFWEBSERVICE_PASSWORD',
            'label' => $this->l('Password'),
        ),
        array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '<i class="icon icon-link"></i>',
            'desc' => $this->l('Enter URL for Loyalty Post Data'),
            'name' => 'SFWEBSERVICE_URL_POST_DATA',
            'label' => $this->l('URL Post Data'),
        ),
        array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '<i class="icon icon-link"></i>',
            'desc' => $this->l('Enter URL for Loyalty Get Data'),
            'name' => 'SFWEBSERVICE_URL_GET_DATA',
            'label' => $this->l('URL Get Data'),
        ),
        ),
        'submit' => array(
        'title' => $this->l('Save'),
        ),
    ),
    );
    }

    /**
     * Create the structure of send manually data.
     */
    protected function getSendManuallyForm()
    {
        return array(
            // Send manually data
        'form' => array(
            'legend' => array(
            'title' => $this->l('Send Manually Data'),
            'icon' => 'icon-terminal',
            ),
            'input' => array(
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-gears"></i>',
                'desc' => $this->l('Enter order id'),
                'name' => 'SFWEBSERVICE_GET_ID_ORDER',
                'label' => $this->l('Order ID'),
            ),
            ),
            'submit' => array(
            'title' => $this->l('Send'),
            ),
        ),
        );
    }

    /**
     * Create the structure of your form for sf integration credentials.
     */
    protected function getSFIntegrationForm()
    {
        return array(
            // API consumme credentials
        'form' => array(
            'legend' => array(
            'title' => $this->l('API Register/Login consumme credentials'),
            'icon' => 'icon-key',
            ),
            'input' => array(
            array(
                'type' => 'switch',
                'label' => $this->l('API Active'),
                'name' => 'SFWEBSERVICE_ACTIVE_2',
                'is_bool' => true,
                'desc' => $this->l('Active this options'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-cog"></i>',
                'desc' => $this->l('Enter grant type'),
                'name' => 'SFWEBSERVICE_GRANT_TYPE_2',
                'label' => $this->l('Grant Type'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL for get token'),
                'name' => 'SFWEBSERVICE_URL_GET_TOKEN_2',
                'label' => $this->l('URL for Token'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-asterisk"></i>',
                'desc' => $this->l('Enter a client id'),
                'name' => 'SFWEBSERVICE_CLIENT_ID_2',
                'label' => $this->l('Client Id'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-lock"></i>',
                'desc' => $this->l('Enter a secret id'),
                'name' => 'SFWEBSERVICE_CLIENT_SECRET_2',
                'label' => $this->l('Client Secret'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-user"></i>',
                'desc' => $this->l('Enter a user'),
                'name' => 'SFWEBSERVICE_USERNAME_2',
                'label' => $this->l('Username'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-key"></i>',
                'desc' => $this->l('Enter a password'),
                'name' => 'SFWEBSERVICE_PASSWORD_2',
                'label' => $this->l('Password'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-asterisk"></i>',
                'desc' => $this->l('Enter secrect key for SF Passw'),
                'name' => 'SFWEBSERVICE_CLIENT_SECRETKEY_PASSW',
                'label' => $this->l('SF Passw Secret Key'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL for contact insert'),
                'name' => 'SFWEBSERVICE_URL_CONTACT_INSERT',
                'label' => $this->l('URL Contact Insert'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL for contact update'),
                'name' => 'SFWEBSERVICE_URL_CONTACT_UPDATE',
                'label' => $this->l('URL Contact Update'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL for login'),
                'name' => 'SFWEBSERVICE_URL_LOGIN',
                'label' => $this->l('URL Login'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL for reset or forgot password'),
                'name' => 'SFWEBSERVICE_URL_RESET_FORGOT',
                'label' => $this->l('URL Reset/Forgot Passw'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL for change password'),
                'name' => 'SFWEBSERVICE_URL_CHANGE_PASSW',
                'label' => $this->l('URL Change Passw'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL for email verify'),
                'name' => 'SFWEBSERVICE_URL_EMAIL_VERIFY',
                'label' => $this->l('URL Email Verify'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL for set token'),
                'name' => 'SFWEBSERVICE_URL_SET_TOKEN',
                'label' => $this->l('URL Set Token'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL Reset OTP'),
                'name' => 'SFWEBSERVICE_URL_RESET_OTP',
                'label' => $this->l('URL Reset OTP'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'prefix' => '<i class="icon icon-link"></i>',
                'desc' => $this->l('Enter URL Get Profile'),
                'name' => 'SFWEBSERVICE_URL_GET_PROFILE',
                'label' => $this->l('URL Get Profile'),
            ),
            array(
                'col' => 6,
                'type' => 'textarea',
                'rows' => '8',
                'tinymce' => true,
                'class' => 'rte',
                'autoload_rte' => true,
                'verify_html' => false,
                'desc' => $this->l('Enter Privacy Policy text'),
                'name' => 'SFWEBSERVICE_PRIVACY_POLICY',
                'label' => $this->l('Privacy Policy'),
            ),
            array(
                'col' => 6,
                'type' => 'textarea',
                'rows' => '8',
                'tinymce' => true,
                'class' => 'rte',
                'autoload_rte' => true,
                'verify_html' => false,
                'desc' => $this->l('Enter another Privacy Policy text'),
                'name' => 'SFWEBSERVICE_PRIVACY_POLICY_2',
                'label' => $this->l('Another Privacy Policy'),
            ),
            array(
                'col' => 6,
                'type' => 'textarea',
                'rows' => '8',
                'tinymce' => true,
                'class' => 'rte',
                'autoload_rte' => true,
                'verify_html' => false,
                'desc' => $this->l('Enter Terms and Conditions text'),
                'name' => 'SFWEBSERVICE_TERMS_CONDITIONS',
                'label' => $this->l('Terms and Conditions'),
            ),
            ),
            'submit' => array(
            'title' => $this->l('Save'),
            ),
        ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'SFWEBSERVICE_LIVE_MODE' => (int) Configuration::get('SFWEBSERVICE_LIVE_MODE'),
            'SFWEBSERVICE_SECRET_STRING' => Configuration::get('SFWEBSERVICE_SECRET_STRING'),
            'SFWEBSERVICE_KEY_FOR_HASH' => Configuration::get('SFWEBSERVICE_KEY_FOR_HASH'),
            'SFWEBSERVICE_ACTIVE' => Configuration::get('SFWEBSERVICE_ACTIVE'),
            'SFWEBSERVICE_CLIENT_ID' => Configuration::get('SFWEBSERVICE_CLIENT_ID'),
            'SFWEBSERVICE_CLIENT_SECRET' => Configuration::get('SFWEBSERVICE_CLIENT_SECRET'),
            'SFWEBSERVICE_USERNAME' => Configuration::get('SFWEBSERVICE_USERNAME'),
            'SFWEBSERVICE_PASSWORD' => Configuration::get('SFWEBSERVICE_PASSWORD'),
            'SFWEBSERVICE_URL_POST_DATA' => Configuration::get('SFWEBSERVICE_URL_POST_DATA'),
            'SFWEBSERVICE_URL_GET_DATA' => Configuration::get('SFWEBSERVICE_URL_GET_DATA'),
            'SFWEBSERVICE_URL_GET_TOKEN' => Configuration::get('SFWEBSERVICE_URL_GET_TOKEN'),
            'SFWEBSERVICE_GRANT_TYPE' => Configuration::get('SFWEBSERVICE_GRANT_TYPE'),
            'SFWEBSERVICE_GET_ID_ORDER' => Tools::getValue('SFWEBSERVICE_GET_ID_ORDER'),
            'SFWEBSERVICE_ACTIVE_2' => Configuration::get('SFWEBSERVICE_ACTIVE_2'),
            'SFWEBSERVICE_CLIENT_ID_2' => Configuration::get('SFWEBSERVICE_CLIENT_ID_2'),
            'SFWEBSERVICE_CLIENT_SECRET_2' => Configuration::get('SFWEBSERVICE_CLIENT_SECRET_2'),
            'SFWEBSERVICE_USERNAME_2' => Configuration::get('SFWEBSERVICE_USERNAME_2'),
            'SFWEBSERVICE_PASSWORD_2' => Configuration::get('SFWEBSERVICE_PASSWORD_2'),
            'SFWEBSERVICE_GRANT_TYPE_2' => Configuration::get('SFWEBSERVICE_GRANT_TYPE_2'),
            'SFWEBSERVICE_URL_GET_TOKEN_2' => Configuration::get('SFWEBSERVICE_URL_GET_TOKEN_2'),
            'SFWEBSERVICE_CLIENT_SECRETKEY_PASSW' => Configuration::get('SFWEBSERVICE_CLIENT_SECRETKEY_PASSW'),
            'SFWEBSERVICE_URL_CONTACT_INSERT' => Configuration::get('SFWEBSERVICE_URL_CONTACT_INSERT'),
            'SFWEBSERVICE_URL_CONTACT_UPDATE' => Configuration::get('SFWEBSERVICE_URL_CONTACT_UPDATE'),
            'SFWEBSERVICE_URL_LOGIN' => Configuration::get('SFWEBSERVICE_URL_LOGIN'),
            'SFWEBSERVICE_URL_RESET_FORGOT' => Configuration::get('SFWEBSERVICE_URL_RESET_FORGOT'),
            'SFWEBSERVICE_URL_CHANGE_PASSW' => Configuration::get('SFWEBSERVICE_URL_CHANGE_PASSW'),
            'SFWEBSERVICE_URL_EMAIL_VERIFY' => Configuration::get('SFWEBSERVICE_URL_EMAIL_VERIFY'),
            'SFWEBSERVICE_URL_SET_TOKEN' => Configuration::get('SFWEBSERVICE_URL_SET_TOKEN'),
            'SFWEBSERVICE_URL_RESET_OTP' => Configuration::get('SFWEBSERVICE_URL_RESET_OTP'),
            'SFWEBSERVICE_URL_GET_PROFILE' => Configuration::get('SFWEBSERVICE_URL_GET_PROFILE'),
            'SFWEBSERVICE_PRIVACY_POLICY' => Configuration::get('SFWEBSERVICE_PRIVACY_POLICY'),
            'SFWEBSERVICE_PRIVACY_POLICY_2' => Configuration::get('SFWEBSERVICE_PRIVACY_POLICY_2'),
            'SFWEBSERVICE_TERMS_CONDITIONS' => Configuration::get('SFWEBSERVICE_TERMS_CONDITIONS'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key), true);
        }
    }

    /* curl management */
    public function canUseCurl()
    {
        return (
            function_exists('curl_init')
            && function_exists('curl_setopt')
            && function_exists('curl_exec')
            && function_exists('curl_close')
        );
    }

    /**
     * Error log
    *
    * @param string $text text that will be saved in the file
    * @return void Error record in file "log_errors.log"
    */
    public static function logtxt($text = "")
    {
        if (file_exists(SFWEBSERVICE_PATH_LOG)) {
            $fp = fopen(_PS_ROOT_DIR_ . "/modules/sfwebservice/log/log_errors.log", "a+");
            fwrite($fp, date('l jS \of F Y h:i:s A') . ", " . $text . "\r\n");
            fclose($fp);
            return true;
        } else {
            self::createPath(SFWEBSERVICE_PATH_LOG);
        }
    }

    /**
     * Recursively create a string of directories
     */
    public static function createPath($path) {

        if (is_dir($path))
            return true;

        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);
        $return = self::createPath($prev_path);
        return ($return && is_writable($prev_path)) ? mkdir($path) : false;
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        // if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        // }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookdisplayHomeSF()
    {

    }

    /**
     * Only for testing
     */
    public function hookdisplayHome()
    {
        if ($this->SFWEBSERVICE_LIVE_MODE == 1) {
            // self::logtxt("probando log en hookdisplayHomeSF()");

            // obtenemos el lenguaje para saber el pais de la tienda
            $languages = Language::getLanguages(true, $this->context->shop->id);
            $lang_code = $languages[0]['language_code'];
            if($lang_code == 'es-co') {
                $id_country = 69;
                $id_state = 339;
                $city = 'Bogotá';
            }
            if($lang_code == 'es-mx') {
                $id_country = 145;
                $id_state = 65;
                $city = 'Ciudad de México';
            }
            if($lang_code == 'es-pe') {
                $id_country = 171;
                $id_state = 338;
                $city = 'Lima';
            }


            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['token'])) {
                // init lib for decrypt
                require_once('lib/AesCipher.php');

                $str= $this->SFWEBSERVICE_SECRET_STRING;
                $hash_key = $this->SFWEBSERVICE_KEY_FOR_HASH;
                $key = hash_hmac('sha256', $str, $hash_key, true);

                // $cipher_text = 'bJF/kb05AnFVdAR5fOdvnJuC/ldN/mL/nOd1kfSM5GyKJF8imlJh3oS0iilAy2dwmK/if3HXgn4Mx4mUhlCJw8O1pPN1N1aYQVdns4ttCX7vGhAIPVT2wnYbFyFKgGNOeDy+ojPvN5orOE5s8NJ+mPga/WRb4Vga671UnFgRQF+eqjDYeFysB4zwKzHUhzq+r7vskFPl0wKkxlVXYH6GRKKW7d1J0SueqUQ6qmASdVk=';

                $cipher_text = $_POST['token'];
                $cipher_text = str_replace(' ', '+', $cipher_text);
                // var_dump($cipher_text);

                // decrypt cipher text
                $AesCipher = new AesCipher();
                $Textdecrypted = $AesCipher->decrypt($key, $cipher_text);
                // var_dump($Textdecrypted);
                $json = array(
                'masterJson' => $Textdecrypted
            );
                $jsonObject = json_decode($json['masterJson']);

                $firstName = $jsonObject->firstName; // yes
                $lastName = $jsonObject->lastName; // yes
                $phone = $jsonObject->phone; // yes
                $email = $jsonObject->email; // yes
                $idSF = $jsonObject->id; //yes
                $exp = $jsonObject->exp; // numero de segundos que el token es valido
                $iat = $jsonObject->iat; // tiempo en que se genero el token
                $plaintextPassword = $firstName.$idSF.$lastName; // yes
                // hashing passwd
                $crypto = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
                $passwd = $crypto->hash($plaintextPassword);


                // var_dump($passw);
                // $email = 'alejandro.villegas@farmalisto.com.co'; //for testing

                // validate the inputs
                if (property_exists($jsonObject, 'firstName') == false) {
                    self::logtxt("Error, firstName input not found in json data!");
                    var_dump("<br>Error, firstName input not found in json data!<br>");
                }
                if (property_exists($jsonObject, 'lastName') == false) {
                    self::logtxt("Error, lastName input not found in json data!");
                    var_dump("Error, lastName input not found in json data!<br>");
                }
                if (property_exists($jsonObject, 'phone') == false) {
                    self::logtxt("Error, phone input not found in json data!");
                    var_dump("Error, phone input not found in json data!<br>");
                }
                if (property_exists($jsonObject, 'email') == false) {
                    self::logtxt("Error, email input not found in json data!");
                    var_dump("Error, email input not found in json data!<br>");
                }
                if (property_exists($jsonObject, 'id') == false) {
                    self::logtxt("Error, id input not found in json data!");
                    var_dump("Error, id input not found in json data!<br>");
                }
                if (property_exists($jsonObject, 'exp') == false) {
                    self::logtxt("Error, exp input not found in json data!");
                    var_dump("Error, exp input not found in json data!<br>");
                }
                if (property_exists($jsonObject, 'iat') == false) {
                    self::logtxt("Error, iat input not found in json data!");
                    var_dump("Error, iat input not found in json data!<br>");
                }

                // special validate (current time is less than iat+exp seconds)
                $iat = intval($iat);
                $exp = intval($exp);
                // var_dump(($iat + $exp)*(-1));
                // var_dump(time());
                if (time() < ($iat + $exp)) {
                // if (time() < ($iat + $exp)*(-1)) {
                    self::logtxt("json received success!: $Textdecrypted");
                    // echo '--Result: json received success! <br><br>';
                    // var_dump($Textdecrypted);

                    // value if user exist in db
                    $customerExists = CustomerCore::customerExists($email, $returnId = true);
                    if ($customerExists) {
                        // login customer
                        $customer = new Customer();
                        $customer = $customer->getByEmail($email);
                        if (!Validate::isLoadedObject($customer)){
                            // echo '<br><br>--The customer could not be registered!';
                            self::logtxt("--The customer could not be registered!");
                        }
                        $customer->logged = 1;
                        $this->context->customer = $customer;
                        $this->context->cookie->id_customer = $customer->id;
                        $this->context->cookie->customer_lastname = $customer->lastname;
                        $this->context->cookie->customer_firstname = $customer->firstname;
                        $this->context->cookie->logged = 1;
                        $this->context->cookie->check_cgv = 1;
                        $this->context->cookie->is_guest = $customer->isGuest();
                        $this->context->cookie->passwd = $customer->passwd;
                        $this->context->cookie->email = $customer->email;

                        // Actualiza algunos datos que vienen del brandsite
                        $customerUpd = new Customer($customer->id);
                        $customerUpd->lastname = $lastName;
                        $customerUpd->firstname = $firstName;
                        $customerUpd->siret = $phone;
                        $customerUpd->note = $idSF;
                        $resUpd = $customerUpd->update();
                        if($resUpd) {
                            self::logtxt("### Customer data updated from saleforce!");
                        }else {
                            // maneja error
                            self::logtxt('### No se actualizo la data en prestashop!');
                        }

                        // echo '<br><br>--Customer exist...Customer logged!';
                        self::logtxt("--Customer exist...Customer logged!");

                        // // Tools::redirect('index.php?controller=my-account');
                        // Tools::redirect('index.php?controller=identity');
                        Tools::redirect(__PS_BASE_URI__);

                    } else {
                        // echo '<br><br>--Customer don\'t exist...Register please!';
                        self::logtxt("--Customer don\'t exist...Register please!");

                        // now will customer register
                        $customer = new Customer();
                        $customer->lastname = $lastName;
                        $customer->firstname = $firstName;
                        $customer->email = $email;
                        $customer->passwd = $passwd;
                        $customer->note = $idSF;
                        $customer->active = 1;
                        $customer->is_guest = 0;
                        $customer->siret = $phone;
                        // validate if the current customer was created!
                        if($customer->add()) {
                            // echo '<br><br>--Customer registered successful!';
                            self::logtxt("--Customer registered successful!");

                            // now will login customer after register
                            $customer = new Customer();
                            $customer = $customer->getByEmail($email);
                            if (!Validate::isLoadedObject($customer)){
                                // echo '<br><br>--The customer could not be registered!';
                                self::logtxt("--The customer could not be registered!");
                            }else{
                                $customer->logged = 1;
                                $this->context->customer = $customer;
                                $this->context->cookie->id_customer = $customer->id;
                                $this->context->cookie->customer_lastname = $customer->lastname;
                                $this->context->cookie->customer_firstname = $customer->firstname;
                                $this->context->cookie->logged = 1;
                                $this->context->cookie->check_cgv = 1;
                                $this->context->cookie->is_guest = $customer->isGuest();
                                $this->context->cookie->passwd = $customer->passwd;
                                $this->context->cookie->email = $customer->email;

                                $address = new Address();
                                // value if customer address exist
                                $addressExist = $address->getFirstCustomerAddressId($customer->id);
                                self::logtxt("id address: $addressExist");
                                // if not exist, create it
                                if($addressExist < 1) {
                                    $address->id_country = $id_country;
                                    $address->id_state = $id_state;
                                    $address->id_customer = $customer->id;
                                    $address->id_manufacturer = 0;
                                    $address->id_supplier = 0;
                                    $address->id_warehouse = 0;
                                    $address->alias = 'My Address';
                                    $address->company = '';
                                    $address->lastname = $lastName;
                                    $address->firstname = $firstName;
                                    $address->address1 = ' ';
                                    $address->address2 = '';
                                    $address->postcode = '';
                                    $address->city = ' ';
                                    $address->other = '';
                                    $address->phone = '';
                                    $address->phone_mobile = $phone;
                                    $address->vat_number = '';
                                    $address->dni = '';
                                    $address->active = 1;
                                    $address->deleted = 0;
                                    if($address->add()) {
                                        self::logtxt("--Address customer created!");
                                    }

                                    // update table ps_address for leave empty fields
                                    $db = Db::getInstance();
                                    $sql = 'UPDATE '._DB_PREFIX_.'address SET address1="", city="" WHERE id_customer = '.$customer->id;
                                    $dbResult = $db->getValue($sql);
                                    // self::logtxt("result: $dbResult");
                                }

                                // echo '<br><br>--Customer logged after resgister!';
                                self::logtxt("--Customer logged after register!");

                                // Tools::redirect('index.php?controller=my-account');
                                // Tools::redirect('index.php?controller=identity');
                                Tools::redirect(__PS_BASE_URI__);

                        }

                        }else {
                            // echo '<br><br>--The customer could not be registered! - 2';
                            self::logtxt("--The customer could not be registered! - 2");
                        }

                    }


                } else {
                    self::logtxt("json discarded!");
                    // echo '--Result: json discarded!';
                }
            }
        }// end if live mode on

    }

    /**
     * Hook for detect status changed to entregado
     * @param unknown $params
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        if ($this->SFWEBSERVICE_ACTIVE == 1) {

            // value if state changed is equal to 5 (Entregado)
            if($params['newOrderStatus']->id == 5) {
                self::logtxt("---- API Consumme Active!! ----");

                // if CanUseCurl
                if($this->canUseCurl()) {

                    // init lib for decrypt
                    require_once('lib/API.php');

                    // get values request
                    $id_order = $params['id_order'];
                    self::logtxt("id_order: $id_order");

                    $orders = new OrderCore($id_order);

                    $id_customer = $orders->id_customer;
                    self::logtxt("id_customer: $id_customer");

                    $customers = self::getCustomersById($id_customer);
                    // $customers = json_encode($customers, true);
                    // self::logtxt("customer: $customers");

                    $firstname = $customers[0]['firstname'];
                    self::logtxt("firstname: $firstname");

                    $lastname = $customers[0]['lastname'];
                    self::logtxt("lastname: $lastname");

                    $mobilePhone = $customers[0]['siret'];
                    self::logtxt("mobilePhone: $mobilePhone");

                    $email = $customers[0]['email'];
                    self::logtxt("email: $email");

                    $id_saleforce = trim($customers[0]['note']);
                    self::logtxt("id_saleforce: $id_saleforce");

                    $id_address = $orders->id_address_delivery;
                    self::logtxt("id_address: $id_address");

                    $address = new AddressCore($id_address);
                    // $address = json_encode($address, true);
                    // self::logtxt("customer: $address");

                    $id_country = $address->id_country;
                    self::logtxt("id_country: $id_country");

                    $countries = new CountryCore($id_country);
                    $country = $countries->name;
                    self::logtxt("country: $country[1]");

                    $city = $address->city;
                    self::logtxt("city: $city");
                    $dni = $address->dni;
                    self::logtxt("dni: $dni");
                    $id_state = $address->id_state;
                    self::logtxt("id_state: $id_state");

                    $states = new StateCore($id_state);
                    $state = $states->name;
                    self::logtxt("state: $state");

                    $reference = $orders->reference;
                    self::logtxt("reference: $reference");

                    // obtenemos el lenguaje para saber el pais de la tienda
                    $languages = Language::getLanguages(true, $this->context->shop->id);
                    $lang_code = $languages[0]['language_code'];

                    $invoide_id = self::getFacturaDetails($id_order, $lang_code);
                    // $facturaDetails = json_encode($facturaDetails, true);
                    // self::logtxt("facturaDetails: $facturaDetails");
                    self::logtxt("invoide_id: $invoide_id");

                    $date_order = $orders->date_add;
                    $Explodedate = explode(' ', $date_order);
                    $date = $Explodedate[0];
                    $time = $Explodedate[1];
                    $final_datetime = $date.'T'.$time.'.000Z';
                    self::logtxt("date_order: $final_datetime");

                    $orderDetails = $orders->getProductsDetail($id_order);
                    // $orderDetails = json_encode($orderDetails, true);
                    // self::logtxt("orderDetails: $orderDetails");

                    $num_details = count($orderDetails);
                    self::logtxt("Detalles a enviar: $num_details");

                    $data2 = array();
                    $count = -1;

                    foreach ($orderDetails as $key => $itemDetail) {
                        // get details
                        $product_name = $itemDetail['product_name'];
                        self::logtxt("product_name: $product_name");

                        $product_reference = trim($itemDetail['product_reference']);
                        self::logtxt("product_reference: $product_reference");

                        $product_quantity = $itemDetail['product_quantity'];
                        self::logtxt("product_quantity: $product_quantity");

                        $transaction_value = number_format($itemDetail['total_price_tax_incl'], 2, '.', '');
                        self::logtxt("transaction_value: $transaction_value");

                        $id_item = $reference.'-'.($key+1);
                        self::logtxt("id_item: $id_item");

                        // evalua el product_reference para homologar el product name a enviar
                        $db = Db::getInstance();
                        $sql = 'SELECT final_name FROM '._DB_PREFIX_.'ct_transactions_homologa WHERE sku_id = "'.trim($product_reference).'"';
                        $final_name = $db->getValue($sql);
                        $final_name = strtoupper($final_name);
                        self::logtxt("final_name: $final_name");

                        // get data2
                        $count++; 
                        self::logtxt("contador: $count");
                        if($count != $num_details){
                            $data2[$key]['Contact_FirstName__c'] = $firstname;
                            $data2[$key]['Contact_LastName__c'] = $lastname;
                            $data2[$key]['MobilePhone__c'] = $mobilePhone;
                            $data2[$key]['Consumer_Email__c'] = $email;
                            $data2[$key]['System_3P_unique_id__c'] = $id_item;
                            // $data2[$key]['Consumer_Country__c'] = $country[1];
                            // $data2[$key]['Consumer_State__c'] = $state;
                            // $data2[$key]['Consumer_City__c'] = $city;
                            $data2[$key]['Consumer_Personal_Id__c'] = $dni;
                            $data2[$key]['contact_Num__c'] = $id_saleforce;
                            $data2[$key]['Product_Name__c'] = $product_name;
                            $data2[$key]['Product__c'] = $final_name;
                            $data2[$key]['Points__c'] = '';
                            $data2[$key]['SKU_Code__c'] = $product_reference;
                            $data2[$key]['Transaction_Id__c'] = $reference;
                            $data2[$key]['Quantity__c'] = $product_quantity;
                            $data2[$key]['InvoiceId__c'] = $invoide_id;
                            $data2[$key]['Transaction_Type__c'] = 'COMPRA';
                            $data2[$key]['Origin__c'] = 'Ecommerce';
                            $data2[$key]['Transaction_Value__c'] = $transaction_value;
                            $data2[$key]['Transaction_Date__c'] = $final_datetime;
                        }

                    }// End foreach items details

                    // echo "<pre>";
                    // var_dump($params);
                    // echo "</pre>";

                    // echo "<pre>";
                    // var_dump("New Status: Entregado!");
                    // echo "</pre>";
                    self::logtxt("New Status: Entregado!");

                    // Authentication2.0
                    $token = '';
                    $params = array (
                        'grant_type' => $this->SFWEBSERVICE_GRANT_TYPE,
                        'client_id' => $this->SFWEBSERVICE_CLIENT_ID,
                        'client_secret' => $this->SFWEBSERVICE_CLIENT_SECRET,
                        'username' => $this->SFWEBSERVICE_USERNAME,
                        'password' => $this->SFWEBSERVICE_PASSWORD
                    );
                    $URL_TOKEN	= $this->SFWEBSERVICE_URL_GET_TOKEN;
                    $rs 	= API::Authentication2($params,$URL_TOKEN);
                    $array  = API::JSON_TO_ARRAY($rs);
                    $token 	= $array['access_token'];
                    // self::logtxt("Token: $token");

                    $URL = $this->SFWEBSERVICE_URL_POST_DATA;
                    // preparing data
                    $data1 = '{"loyalties":';
                    $data2 = json_encode($data2);
                    $data3 = '}';
                    $data = $data1.$data2.$data3;

                    // $data = json_encode($data2);
                    // self::logtxt("Data: $data");

                    // Sending data
                    $res = API::POST($URL,$token,$data);
                    $result = API::JSON_TO_ARRAY($res);

                    $resultJson = json_encode($result);
                    $resultJson2 = json_encode($result[0]['errors']);
                    self::logtxt("Result: $resultJson");

                    // If everything ok without errors
                    if($result[0]['errors'] != '' || $result[0]['message'] != '') {
                        if($result[0]['errors'] != '') {
                            $resultJson2 = json_encode($result[0]['errors']);
                            self::logtxt("Error: $resultJson2");
                        }elseif($result[0]['message'] != '') {
                            $resultJson3 = json_encode($result[0]['message']);
                            self::logtxt("Error: $resultJson3");
                        }else{
                            self::logtxt("Error: Hubo un error!");
                        }
                        
                    }else{
                        // var_dump('Result: Data enviada con éxito...');
                        self::logtxt("Result: Data enviada con éxito...");

                        foreach ($orderDetails as $key => $itemDetail) {
                            // get details
                            $product_name = $itemDetail['product_name'];
                            $product_reference = trim($itemDetail['product_reference']);
                            $product_quantity = $itemDetail['product_quantity'];
                            $transaction_value = number_format($itemDetail['total_price_tax_incl'], 2, '.', '');
                            $id_item = $reference.'-'.($key+1);

                            // Insertamos data en ps_sf_transactions_history tabla
                            $result =  Db::getInstance()->insert('sf_transactions_history', array(
                                'email' => $email,
                                'dni' => $dni,
                                'id_saleforce' => $id_saleforce,
                                'order_unique_id' => $id_item,
                                'order_id' => $reference,
                                'product_name' => $product_name,
                                'sku_code' => $product_reference,
                                'quantity' => $product_quantity,
                                'invoice_id' => $invoide_id,
                                'transaction_type' => 'COMPRA',
                                'transaction_value' => $transaction_value,
                                'transaction_date' => $final_datetime,
                                'created_date' => date("Y-m-d H:i:s"),
                            ));
                            $error = Db::getInstance()->getMsgError();

                            if ($result == true) {
                                self::logtxt("Registros guardados al history con exito");
                            // var_dump("Registros guardados al history con exito");
                            } else {
                                if ($error != '') {
                                    self::logtxt($error);
                                }
                                self::logtxt("Hubo un error al intentar guardar en el history");
                                // var_dump("1-Hubo un error al intentar guardar en el history");
                            }

                        }// End foreach items details

                    }// end if everything ok

                }// end CanUseCurl

            }

        }else{
            // API desactivated
            // echo "<pre>";
            // var_dump('API Consumme No Active!!');
            // echo "</pre>";
            self::logtxt("API Consumme No Active!!");

        } // end SFWEBSERVICE_ACTIVE
    } // end hookActionOrderStatusPostUpdate

    /**
     * Function for send data manually
     * getting id_order.
     */
    public function sendDataManually($id_order)
    {
        if ($this->SFWEBSERVICE_ACTIVE == 1) {

            self::logtxt("---- API Consumme Active!! Envio manual...----");

                // if CanUseCurl
                if($this->canUseCurl()) {

                    // init lib for decrypt
                    require_once('lib/API.php');

                    // get values request
                    $id_order = $this->SFWEBSERVICE_GET_ID_ORDER;
                    self::logtxt("id_order: $id_order");

                    $orders = new OrderCore($id_order);
                    $reference = $orders->reference;
                    self::logtxt("reference: $reference");

                    $id_customer = $orders->id_customer;
                    self::logtxt("id_customer: $id_customer");

                    $customers = self::getCustomersById($id_customer);
                    // $customers = json_encode($customers, true);
                    // self::logtxt("customer: $customers");

                    $firstname = $customers[0]['firstname'];
                    self::logtxt("firstname: $firstname");

                    $lastname = $customers[0]['lastname'];
                    self::logtxt("lastname: $lastname");

                    $mobilePhone = $customers[0]['siret'];
                    self::logtxt("mobilePhone: $mobilePhone");

                    $email = $customers[0]['email'];
                    self::logtxt("email: $email");

                    $id_saleforce = trim($customers[0]['note']);
                    self::logtxt("id_saleforce: $id_saleforce");

                    $id_address = $orders->id_address_delivery;
                    self::logtxt("id_address: $id_address");

                    $address = new AddressCore($id_address);
                    // $address = json_encode($address, true);
                    // self::logtxt("customer: $address");

                    $id_country = $address->id_country;
                    self::logtxt("id_country: $id_country");

                    $countries = new CountryCore($id_country);
                    $country = $countries->name;
                    self::logtxt("country: $country[1]");

                    $city = $address->city;
                    self::logtxt("city: $city");
                    $dni = $address->dni;
                    self::logtxt("dni: $dni");
                    $id_state = $address->id_state;
                    self::logtxt("id_state: $id_state");

                    $states = new StateCore($id_state);
                    $state = $states->name;
                    self::logtxt("state: $state");

                    // obtenemos el lenguaje para saber el pais de la tienda
                    $languages = Language::getLanguages(true, $this->context->shop->id);
                    $lang_code = $languages[0]['language_code'];

                    $invoide_id = self::getFacturaDetails($id_order, $lang_code);
                    // $facturaDetails = json_encode($invoide_id, true);
                    // self::logtxt("facturaDetails: $facturaDetails");
                    self::logtxt("invoide_id: $invoide_id");

                    $date_order = $orders->date_add;
                    $Explodedate = explode(' ', $date_order);
                    $date = $Explodedate[0];
                    $time = $Explodedate[1];
                    $final_datetime = $date.'T'.$time.'.000Z';
                    self::logtxt("date_order: $final_datetime");

                    $orderDetails = $orders->getProductsDetail($id_order);
                    // $orderDetails = json_encode($orderDetails, true);
                    // self::logtxt("orderDetails: $orderDetails");

                    $num_details = count($orderDetails);
                    self::logtxt("Detalles a enviar: $num_details");

                    $data2 = array();
                    $count = -1;

                    foreach ($orderDetails as $key => $itemDetail) {
                        // get details
                        $product_name = $itemDetail['product_name'];
                        self::logtxt("product_name: $product_name");

                        $product_reference = trim($itemDetail['product_reference']);
                        self::logtxt("product_reference: $product_reference");

                        $product_quantity = $itemDetail['product_quantity'];
                        self::logtxt("product_quantity: $product_quantity");

                        $transaction_value = number_format($itemDetail['total_price_tax_incl'], 2, '.', '');
                        self::logtxt("transaction_value: $transaction_value");

                        $id_item = $reference.'-'.($key+1);
                        self::logtxt("id_item: $id_item");

                        // evalua el product_reference para homologar el product name a enviar
                        $db = Db::getInstance();
                        $sql = 'SELECT final_name FROM '._DB_PREFIX_.'ct_transactions_homologa WHERE sku_id = "'.trim($product_reference).'"';
                        $final_name = $db->getValue($sql);
                        $final_name = strtoupper($final_name);
                        self::logtxt("final_name: $final_name");

                        // get data2
                        $count++;
                        self::logtxt("contador: $count");
                        if($count != $num_details){
                            $data2[$key]['Contact_FirstName__c'] = $firstname;
                            $data2[$key]['Contact_LastName__c'] = $lastname;
                            $data2[$key]['MobilePhone__c'] = $mobilePhone;
                            $data2[$key]['Consumer_Email__c'] = $email;
                            $data2[$key]['System_3P_unique_id__c'] = $id_item;
                            // $data2[$key]['Consumer_Country__c'] = $country[1];
                            // $data2[$key]['Consumer_State__c'] = $state;
                            // $data2[$key]['Consumer_City__c'] = $city;
                            $data2[$key]['Consumer_Personal_Id__c'] = $dni;
                            $data2[$key]['contact_Num__c'] = $id_saleforce;
                            $data2[$key]['Product_Name__c'] = $product_name;
                            $data2[$key]['Product__c'] = $final_name;
                            $data2[$key]['Points__c'] = '';
                            $data2[$key]['SKU_Code__c'] = $product_reference;
                            $data2[$key]['Transaction_Id__c'] = $reference;
                            $data2[$key]['Quantity__c'] = $product_quantity;
                            $data2[$key]['InvoiceId__c'] = $invoide_id;
                            $data2[$key]['Transaction_Type__c'] = 'COMPRA';
                            $data2[$key]['Origin__c'] = 'Ecommerce';
                            $data2[$key]['Transaction_Value__c'] = $transaction_value;
                            $data2[$key]['Transaction_Date__c'] = $final_datetime;
                        }

                    }// End foreach items details

                    // echo "<pre>";
                    // var_dump($params);
                    // echo "</pre>";

                    // echo "<pre>";
                    // var_dump("New Status: Entregado!");
                    // echo "</pre>";
                    self::logtxt("New Status: Entregado!");

                    // Authentication2.0
                    $token = '';
                    $params = array (
                        'grant_type' => $this->SFWEBSERVICE_GRANT_TYPE,
                        'client_id' => $this->SFWEBSERVICE_CLIENT_ID,
                        'client_secret' => $this->SFWEBSERVICE_CLIENT_SECRET,
                        'username' => $this->SFWEBSERVICE_USERNAME,
                        'password' => $this->SFWEBSERVICE_PASSWORD
                    );
                    $URL_TOKEN	= $this->SFWEBSERVICE_URL_GET_TOKEN;
                    $rs 	= API::Authentication2($params,$URL_TOKEN);
                    $array  = API::JSON_TO_ARRAY($rs);
                    $token 	= $array['access_token'];
                    // self::logtxt("Token: $token");

                    $URL = $this->SFWEBSERVICE_URL_POST_DATA;
                    // preparing data
                    $data1 = '{"loyalties":';
                    $data2 = json_encode($data2);
                    $data3 = '}';
                    $data = $data1.$data2.$data3;

                    // self::logtxt("Data: $data");

                    // Sending data
                    $res = API::POST($URL,$token,$data);
                    $result = API::JSON_TO_ARRAY($res);

                    $resultJson = json_encode($result);
                    self::logtxt("Result: $resultJson");

                    // If everything ok without errors
                    if($result[0]['errors'] != '' || $result[0]['message'] != '') {
                        if($result[0]['errors'] != '') {
                            $resultJson2 = json_encode($result[0]['errors']);
                            self::logtxt("Error: $resultJson2");
                        }elseif($result[0]['message'] != '') {
                            $resultJson3 = json_encode($result[0]['message']);
                            self::logtxt("Error: $resultJson3");
                        }else{
                            self::logtxt("Error: Hubo un error!");
                        }
                        
                    }else{
                        self::logtxt("Result: Data enviada con éxito...");

                        foreach ($orderDetails as $key => $itemDetail) {
                            // get details
                            $product_name = $itemDetail['product_name'];
                            $product_reference = trim($itemDetail['product_reference']);
                            $product_quantity = $itemDetail['product_quantity'];
                            $transaction_value = number_format($itemDetail['total_price_tax_incl'], 2, '.', '');
                            $id_item = $reference.'-'.($key+1);

                            // Insertamos data en ps_sf_transactions_history tabla
                            $result =  Db::getInstance()->insert('sf_transactions_history', array(
                                'email' => $email,
                                'dni' => $dni,
                                'id_saleforce' => $id_saleforce,
                                'order_unique_id' => $id_item,
                                'order_id' => $reference,
                                'product_name' => $product_name,
                                'sku_code' => $product_reference,
                                'quantity' => $product_quantity,
                                'invoice_id' => $invoide_id,
                                'transaction_type' => 'COMPRA',
                                'transaction_value' => $transaction_value,
                                'transaction_date' => $final_datetime,
                                'created_date' => date("Y-m-d H:i:s"),
                            ));
                            $error = Db::getInstance()->getMsgError();

                            if ($result == true) {
                                self::logtxt("Registros guardados al history con exito");
                            // var_dump("Registros guardados al history con exito");
                            } else {
                                if ($error != '') {
                                    self::logtxt($error);
                                }
                                self::logtxt("Hubo un error al intentar guardar en el history");
                                // var_dump("1-Hubo un error al intentar guardar en el history");
                            }

                        }// End foreach items details

                    }// end if everything ok

                }// end CanUseCurl

        }else{
            // API desactivated
            // echo "<pre>";
            // var_dump('API Consumme No Active!!');
            // echo "</pre>";
            self::logtxt("API Consumme No Active!!");

        } // end SFWEBSERVICE_ACTIVE
    } // end sendDataManually


    /**
     * Function for send masive data
     * getting id_order.
     */
    public function sendDataMasive()
    {
        if ($this->SFWEBSERVICE_ACTIVE == 1) {

            // $key = $this->SFWEBSERVICE_ACTIVE_KEY_PASSWORD;
            $key = 'sfsendaData@2020';

            if (Tools::getValue('k') == $key) {

                self::logtxt("---- API Consumme Active!! Envio masivo...----");

                // if CanUseCurl
                if($this->canUseCurl()) {

                    // init lib for decrypt
                    require_once('lib/API.php');

                    // get orders ids
                    $sql = new DbQuery();
                    $sql->select('*');
                    $sql->from('orders', 'A');
                    $sql->where('A.current_state = 5 AND NOT EXISTS (SELECT order_id FROM ps_sf_transactions_history WHERE order_id = A.reference)');

                    $resOrders = Db::getInstance()->executeS($sql);
                    // echo "<pre>";
                    // var_dump($resOrders);
                    // echo "</pre>";
                    
                    // $jsonresOrders = json_encode($resOrders);
                    // self::logtxt("resOrders: $jsonresOrders");

                    // if results
                    if (!empty($resOrders)) {
                        $numData = count($resOrders);
                        self::logtxt("sendDataMasive: Hay ".$numData." registros para enviar!");

                        // iterate all id orders that we need
                        foreach ($resOrders as $key => $Order) {

                            // get values request
                            $id_order = $Order['id_order'];
                            self::logtxt("id_order: $id_order");

                            $orders = new OrderCore($id_order);
                            $reference = $orders->reference;
                            self::logtxt("reference: $reference");

                            $id_customer = $orders->id_customer;
                            // self::logtxt("id_customer: $id_customer");

                            $customers = self::getCustomersById($id_customer);
                            // $customers = json_encode($customers, true);
                            // self::logtxt("customer: $customers");

                            $firstname = $customers[0]['firstname'];
                            // self::logtxt("firstname: $firstname");

                            $lastname = $customers[0]['lastname'];
                            // self::logtxt("lastname: $lastname");

                            $mobilePhone = $customers[0]['siret'];
                            // self::logtxt("mobilePhone: $mobilePhone");

                            $email = $customers[0]['email'];
                            self::logtxt("email: $email");

                            $id_saleforce = trim($customers[0]['note']);
                            // self::logtxt("id_saleforce: $id_saleforce");

                            $id_address = $orders->id_address_delivery;
                            // self::logtxt("id_address: $id_address");

                            $address = new AddressCore($id_address);
                            // $address = json_encode($address, true);
                            // self::logtxt("customer: $address");

                            $id_country = $address->id_country;
                            // self::logtxt("id_country: $id_country");

                            $countries = new CountryCore($id_country);
                            $country = $countries->name;
                            // self::logtxt("country: $country[1]");

                            $city = $address->city;
                            // self::logtxt("city: $city");
                            $dni = $address->dni;
                            // self::logtxt("dni: $dni");
                            $id_state = $address->id_state;
                            // self::logtxt("id_state: $id_state");

                            $states = new StateCore($id_state);
                            $state = $states->name;
                            // self::logtxt("state: $state");

                            // obtenemos el lenguaje para saber el pais de la tienda
                            $languages = Language::getLanguages(true, $this->context->shop->id);
                            $lang_code = $languages[0]['language_code'];

                            $invoide_id = self::getFacturaDetails($id_order, $lang_code);
                            // $facturaDetails = json_encode($invoide_id, true);
                            // self::logtxt("facturaDetails: $facturaDetails");
                            // self::logtxt("invoide_id: $invoide_id");

                            $date_order = $orders->date_add;
                            $Explodedate = explode(' ', $date_order);
                            $date = $Explodedate[0];
                            $time = $Explodedate[1];
                            $final_datetime = $date.'T'.$time.'.000Z';
                            // self::logtxt("date_order: $final_datetime");

                            $orderDetails = $orders->getProductsDetail($id_order);
                            // $orderDetails = json_encode($orderDetails, true);
                            // self::logtxt("orderDetails: $orderDetails");

                            $num_details = count($orderDetails);
                            // self::logtxt("Detalles a enviar: $num_details");

                            $data2 = array();
                            $count = -1;

                            foreach ($orderDetails as $key => $itemDetail) {
                                // get details
                                $product_name = $itemDetail['product_name'];
                                // self::logtxt("product_name: $product_name");

                                $product_reference = trim($itemDetail['product_reference']);
                                // self::logtxt("product_reference: $product_reference");

                                $product_quantity = $itemDetail['product_quantity'];
                                // self::logtxt("product_quantity: $product_quantity");

                                $transaction_value = number_format($itemDetail['total_price_tax_incl'], 2, '.', '');
                                // self::logtxt("transaction_value: $transaction_value");

                                $id_item = $reference.'-'.($key+1);
                                // self::logtxt("id_item: $id_item");

                                // evalua el product_reference para homologar el product name a enviar
                                $db = Db::getInstance();
                                $sql = 'SELECT final_name FROM '._DB_PREFIX_.'ct_transactions_homologa WHERE sku_id = "'.trim($product_reference).'"';
                                $final_name = $db->getValue($sql);
                                $final_name = strtoupper($final_name);
                                // self::logtxt("final_name: $final_name");

                                // get data2
                                $count++;
                                // self::logtxt("contador: $count");
                                if($count != $num_details){
                                    $data2[$key]['Contact_FirstName__c'] = $firstname;
                                    $data2[$key]['Contact_LastName__c'] = $lastname;
                                    $data2[$key]['MobilePhone__c'] = $mobilePhone;
                                    $data2[$key]['Consumer_Email__c'] = $email;
                                    $data2[$key]['System_3P_unique_id__c'] = $id_item;
                                    // $data2[$key]['Consumer_Country__c'] = $country[1];
                                    // $data2[$key]['Consumer_State__c'] = $state;
                                    // $data2[$key]['Consumer_City__c'] = $city;
                                    $data2[$key]['Consumer_Personal_Id__c'] = $dni;
                                    $data2[$key]['contact_Num__c'] = $id_saleforce;
                                    $data2[$key]['Product_Name__c'] = $product_name;
                                    $data2[$key]['Product__c'] = $final_name;
                                    $data2[$key]['Points__c'] = '';
                                    $data2[$key]['SKU_Code__c'] = $product_reference;
                                    $data2[$key]['Transaction_Id__c'] = $reference;
                                    $data2[$key]['Quantity__c'] = $product_quantity;
                                    $data2[$key]['InvoiceId__c'] = $invoide_id;
                                    $data2[$key]['Transaction_Type__c'] = 'COMPRA';
                                    $data2[$key]['Origin__c'] = 'Ecommerce';
                                    $data2[$key]['Transaction_Value__c'] = $transaction_value;
                                    $data2[$key]['Transaction_Date__c'] = $final_datetime;
                                }

                            }// End foreach items details

                            // echo "<pre>";
                            // var_dump($params);
                            // echo "</pre>";

                            // echo "<pre>";
                            // var_dump("New Status: Entregado!");
                            // echo "</pre>";
                            // self::logtxt("New Status: Entregado!");

                            // Authentication2.0
                            $token = '';
                            $params = array (
                                'grant_type' => $this->SFWEBSERVICE_GRANT_TYPE,
                                'client_id' => $this->SFWEBSERVICE_CLIENT_ID,
                                'client_secret' => $this->SFWEBSERVICE_CLIENT_SECRET,
                                'username' => $this->SFWEBSERVICE_USERNAME,
                                'password' => $this->SFWEBSERVICE_PASSWORD
                            );
                            $URL_TOKEN	= $this->SFWEBSERVICE_URL_GET_TOKEN;
                            $rs 	= API::Authentication2($params,$URL_TOKEN);
                            $array  = API::JSON_TO_ARRAY($rs);
                            $token 	= $array['access_token'];
                            // self::logtxt("Token: $token");

                            $URL = $this->SFWEBSERVICE_URL_POST_DATA;
                            // preparing data
                            $data1 = '{"loyalties":';
                            $data2 = json_encode($data2);
                            $data3 = '}';
                            $data = $data1.$data2.$data3;

                            // $data = json_encode($data2);
                            // self::logtxt("Data: $data");

                            // Sending data
                            $res = API::POST($URL,$token,$data);
                            $result = API::JSON_TO_ARRAY($res);

                            $resultJson = json_encode($result);
                            self::logtxt("Result: $resultJson");

                            // If everything ok without errors
                            if($result[0]['errors'] != '' || $result[0]['message'] != '') {
                                if($result[0]['errors'] != '') {
                                    $resultJson2 = json_encode($result[0]['errors']);
                                    self::logtxt("Error: $resultJson2");
                                }elseif($result[0]['message'] != '') {
                                    $resultJson3 = json_encode($result[0]['message']);
                                    self::logtxt("Error: $resultJson3");
                                }else{
                                    self::logtxt("Error: Hubo un error!");
                                }
                                
                            }else{
                                self::logtxt("Result: Data enviada con éxito...");

                                foreach ($orderDetails as $key => $itemDetail) {
                                    // get details
                                    $product_name = $itemDetail['product_name'];
                                    $product_reference = trim($itemDetail['product_reference']);
                                    $product_quantity = $itemDetail['product_quantity'];
                                    $transaction_value = number_format($itemDetail['total_price_tax_incl'], 2, '.', '');
                                    $id_item = $reference.'-'.($key+1);

                                    // Insertamos data en ps_sf_transactions_history tabla
                                    $result =  Db::getInstance()->insert('sf_transactions_history', array(
                                        'email' => $email,
                                        'dni' => $dni,
                                        'id_saleforce' => $id_saleforce,
                                        'order_unique_id' => $id_item,
                                        'order_id' => $reference,
                                        'product_name' => $product_name,
                                        'sku_code' => $product_reference,
                                        'quantity' => $product_quantity,
                                        'invoice_id' => $invoide_id,
                                        'transaction_type' => 'COMPRA',
                                        'transaction_value' => $transaction_value,
                                        'transaction_date' => $final_datetime,
                                        'created_date' => date("Y-m-d H:i:s"),
                                    ));
                                    $error = Db::getInstance()->getMsgError();

                                    if ($result == true) {
                                        self::logtxt("Registros guardados al history con exito");
                                    // var_dump("Registros guardados al history con exito");
                                    } else {
                                        if ($error != '') {
                                            self::logtxt($error);
                                        }
                                        self::logtxt("Hubo un error al intentar guardar en el history");
                                        // var_dump("1-Hubo un error al intentar guardar en el history");
                                    }

                                }// End foreach items details

                            }// end if everything ok
                            
                        } //end foreach ids orders


                    }else{
                        self::logtxt("sendDataMasive: No hay registros para enviar!");

                    }// end if results sql statement

                }// end CanUseCurl

            } //end value key for consume

            

        }else{
            // API desactivated
            // echo "<pre>";
            // var_dump('API Consumme No Active!!');
            // echo "</pre>";
            self::logtxt("API Consumme No Active!!");

        } // end SFWEBSERVICE_ACTIVE
    } // end sendDataMasive

    /**
     * Retrieve customers by id_customer.
     *
     * @param integer $id_customer
     *
     * @return array
     */
    public static function getCustomersById($id_customer)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('customer');
        $sql->where('id_customer = '.$id_customer);

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Retrieve factura electronica details.
     *
     * @param integer $id_order
     *
     * @return array
     */
    public static function getFacturaDetails($id_order, $lang_code)
    {
        self::logtxt("::lang:: $lang_code");
        if($lang_code == 'es-co') {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('facturacionelectronica');
            $sql->where('id_order = '.$id_order);

            $res = Db::getInstance()->executeS($sql);
            $invoide_id = $res[0]['prefix_number'];

            return $invoide_id;

        }elseif($lang_code == 'es-pe') {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('vex_nubefact_invoice');
            $sql->where('id_order = '.$id_order);

            $res = Db::getInstance()->executeS($sql);
            $invoide_id = $res[0]['serie'].'-'.$res[0]['numero'];
            // $resultJson = json_encode($res);
            // self::logtxt("::fact:: $resultJson");

            return $invoide_id;

        }else{
            return '0';
        }

    }


}
