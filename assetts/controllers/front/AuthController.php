<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class AuthController extends AuthControllerCore
{
    public $ssl = true;
    public $php_self = 'authentication';
    public $auth = false;

    public function checkAccess()
    {
        // instanciamos el modulo sfwebservice
        if (Module::isInstalled('sfwebservice')) {
            $sfModule = Module::getInstanceByName('sfwebservice');

            if (Validate::isLoadedObject($sfModule) && $sfModule->active) {

                if(Configuration::get('SFWEBSERVICE_ACTIVE_2') == '1'){

                    $emailVerify = Tools::getValue('emailVerify');
                    if($emailVerify && $this->context->customer->isLogged()){
                        $sfModule::logtxt("### emailVefify true and logout true!");
                        // logout before redirect
                        $this->context->customer->logout();
                        Tools::redirect('index.php?controller=authentication&emailVerify=1');

                    }else{

                        if ($this->context->customer->isLogged() && !$this->ajax) {
                            $this->redirect_after = ($this->authRedirection) ? urlencode($this->authRedirection) : 'my-account';
                            $this->redirect();
                        }

                    }
                }else{

                    if ($this->context->customer->isLogged() && !$this->ajax) {
                        $this->redirect_after = ($this->authRedirection) ? urlencode($this->authRedirection) : 'my-account';
                        $this->redirect();
                    }
        
                }

            }else{
                if ($this->context->customer->isLogged() && !$this->ajax) {
                    $this->redirect_after = ($this->authRedirection) ? urlencode($this->authRedirection) : 'my-account';
                    $this->redirect();
                }
            }

        }else{
            if ($this->context->customer->isLogged() && !$this->ajax) {
                $this->redirect_after = ($this->authRedirection) ? urlencode($this->authRedirection) : 'my-account';
                $this->redirect();
            }
        }

        return parent::checkAccess();
    }

}
