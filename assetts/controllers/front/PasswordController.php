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

use PrestaShop\PrestaShop\Core\Crypto\Hashing;
use PrestaShop\PrestaShop\Adapter\ServiceLocator; // for hash passw

class PasswordController extends PasswordControllerCore
{
    public $php_self = 'password';
    public $auth = false;
    public $ssl = true;

    /**
     * Start forms process
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $this->setTemplate('customer/password-email');

        if (Tools::isSubmit('email')) {
            $this->sendRenewPasswordLink();
        } elseif (Tools::getValue('token') && ($id_customer = (int)Tools::getValue('id_customer'))) {
            $this->changePassword();
        } elseif (Tools::getValue('token') || Tools::getValue('id_customer')) {
            $this->errors[] = $this->trans('We cannot regenerate your password with the data you\'ve submitted', array(), 'Shop.Notifications.Error');
        }
    }

    protected function sendRenewPasswordLink()
    {
        // instanciamos el modulo sfwebservice
        if (Module::isInstalled('sfwebservice')) {
            $sfModule = Module::getInstanceByName('sfwebservice');

            if (Validate::isLoadedObject($sfModule) && $sfModule->active) {
                if (Configuration::get('SFWEBSERVICE_ACTIVE_2') == '1') {

                    // Data para enviar por la API
                    $email = Tools::getValue('email');

                    // Consulta db ps_country para el id del pais
                    $db = Db::getInstance();
                    $sql = 'SELECT id_country FROM '._DB_PREFIX_.'country WHERE active = 1';
                    $id_country = $db->getValue($sql);

                    // evalua idlang
                    $idlang = (int) Configuration::get('PS_LANG_DEFAULT');

                    // Obtiene nombre del pais
                    $Country = new Country();
                    $countryName = $Country::getNameById($idlang, $id_country);

                    // evalua nombre para codificar pais para el siteSignature a enviar
                    if($countryName == 'Perú') $siteSignature = 'PE';
                    if($countryName == 'México') $siteSignature = 'MX';
                    if($countryName == 'Colombia') $siteSignature = 'CO';

                    // init lib for consume API
                    require_once (_PS_MODULE_DIR_.'sfwebservice'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'API.php');

                    // Authentication2.0
                    $token = '';
                    $params = array (
                        'grant_type' => Configuration::get('SFWEBSERVICE_GRANT_TYPE_2'),
                        'client_id' => Configuration::get('SFWEBSERVICE_CLIENT_ID_2'),
                        'client_secret' => Configuration::get('SFWEBSERVICE_CLIENT_SECRET_2'),
                        'username' => Configuration::get('SFWEBSERVICE_USERNAME_2'),
                        'password' => Configuration::get('SFWEBSERVICE_PASSWORD_2')
                    );
                    // echo "<pre>";
                    // var_dump($params);
                    // echo "</pre>";
                    // die();
                    // error_log("params: ".json_encode($params));

                    $URL_TOKEN = Configuration::get('SFWEBSERVICE_URL_GET_TOKEN_2');
                    $rs = API::Authentication2($params,$URL_TOKEN);
                    $array = API::JSON_TO_ARRAY($rs);
                    $token = $array['access_token'];
                    // echo "<pre>";
                    // var_dump($token);
                    // echo "</pre>";
                    // die();

                    // consume la API para setear el OTP
                    $urlResetOTP = Configuration::get('SFWEBSERVICE_URL_RESET_OTP');
                    // $sfModule::logtxt("### Integration SF: Reset OTP -> $urlResetOTP");

                    // generate OTP
                    // $OTP = $this->generateNumericOTP(6);
                    // $sfModule::logtxt("### Integration SF: OTP -> $OTP");

                    // dataOTP
                    $dataOTP['userEmail'] = $email;
                    // $dataOTP['OTP'] = $OTP;
                    $dataOTP['siteSignature'] = $siteSignature;

                    // preparing data
                    $dataToSFOTP = json_encode($dataOTP);

                    // $requesting = json_decode($dataToSFOTP);
                    // echo "<pre>";
                    // var_dump($dataToSFOTP);
                    // echo "</pre>";
                    $sfModule::logtxt("### Integration SF Reset OTP: Json enviado -> $dataToSFOTP");

                    // Sending data
                    $resOTP = API::POST($urlResetOTP,$token,$dataToSFOTP);
                    $resuOTP = API::JSON_TO_ARRAY($resOTP);
                    $resultOTP = $resuOTP["operationCode"];
                    $resultArrayOTP = json_decode($resultOTP);

                    $sfModule::logtxt("### Integration SF Reset OTP: Respuesta -> $resultOTP");

                    // echo "<pre>";
                    // var_dump($resultArrayOTP);
                    // echo "</pre>";

                    // Si se resetea el OTP
                    if ($resultArrayOTP->rCode == 'opCode_000') {
                        $sfModule::logtxt("### Integration SF ResetOTP: OTP regenerado con éxito!");

                        // verify if email exist
                        $sfModule::logtxt("### Verifica Email ###");

                        // consume la API para verificar si el usuario existe o no
                        $urlEmailVerify = Configuration::get('SFWEBSERVICE_URL_EMAIL_VERIFY');
                        // $sfModule::logtxt("### Integration SF Email Verify: Servicio -> $urlEmailVerify");

                        // dataEV
                        $dataEV['userName'] = $email;
                        $dataEV['email'] = $email;
                        // $dataEV['OTP'] = $OTP;
                        $dataEV['siteSignature'] = $siteSignature;

                        // preparing data
                        $dataToSFEV = json_encode($dataEV);

                        // $requesting = json_decode($dataToSFEV);
                        // echo "<pre>";
                        // var_dump($dataToSFEV);
                        // echo "</pre>";
                        $sfModule::logtxt("### Integration SF Email Verify: Json enviado -> $dataToSFEV");

                        // Sending data
                        $resEV = API::POST($urlEmailVerify,$token,$dataToSFEV);
                        $resEmailVerify = API::JSON_TO_ARRAY($resEV);
                        $resultEmailVerify = $resEmailVerify["operationCode"];
                        $resultArrayEV = json_decode($resultEmailVerify);

                        $sfModule::logtxt("### Integration SF Email Verify: Respuesta -> $resultEmailVerify");

                        // echo "<pre>";
                        // var_dump($resultArrayEV);
                        // echo "</pre>";

                        if ($resultArrayEV->rCode == 'opCode_000') {
                            // email validado
                            $customer = new Customer();
                            $customerExist = $customer->getByEmail($email);
                            
                            // Si existe el usuario
                            if ($customerExist){

                                if (is_null($customer->email)) {
                                    $customer->email = Tools::getValue('email');
                                }
                                // evalua el tiempo configurado por backend para la regeneración de contraseña
                                if ((strtotime($customer->last_passwd_gen.'+'.($minTime = (int) Configuration::get('PS_PASSWD_TIME_FRONT')).' minutes') - time()) > 0) {
                                    $this->errors[] = $this->trans('You can regenerate your password only every %d minute(s)', array((int) $minTime), 'Shop.Notifications.Error');
                                }else {
                                    // todo salio bien, aqui se envia la confirmación
                                    if (!$customer->hasRecentResetPasswordToken()) {
                                        $customer->stampResetPasswordToken();
                                        $customer->update();
                                    }
    
                                    $mailParams = array(
                                        '{email}' => $customer->email,
                                        '{lastname}' => $customer->lastname,
                                        '{firstname}' => $customer->firstname,
                                        '{url}' => $this->context->link->getPageLink('password', true, null, 'token='.$customer->secure_key.'&id_customer='.(int) $customer->id.'&reset_token='.$customer->reset_password_token),
                                    );
    
                                    // envia email
                                    if (
                                        Mail::Send(
                                            $this->context->language->id,
                                            'password_query',
                                            $this->trans(
                                                'Password query confirmation',
                                                array(),
                                                'Emails.Subject'
                                            ),
                                            $mailParams,
                                            $customer->email,
                                            $customer->firstname.' '.$customer->lastname
                                        )
                                    ) {
                                        $sfModule::logtxt("### Integration SF Email Verify: Email existe, se envia el link de confirmación!");
                                        $this->success[] = $this->trans('If this email address has been registered in our shop, you will receive a link to reset your password at %email%.', array('%email%' => $email), 'Shop.Notifications.Success');
                                        $this->setTemplate('customer/password-infos');
        
                                    }else {
                                        $this->errors[] = $this->trans('Hubo un problema y no pudimos enviar el correo de confirmación!', array(), 'Shop.Notifications.Error');
                                    }
    
    
                                }// fin envio confirmacion

                            }else {
                                // Si no existe el usuario se crea antes
                                $sfModule::logtxt("### Integration SF: Usuario no existe en prestashop!");
                                // Consulta el profile en la API
                                // consume la API para obtener el perfil
                                $urlGetProfile = Configuration::get('SFWEBSERVICE_URL_GET_PROFILE');
                                // $sfModule::logtxt("### Integration SF: Get Profile -> $urlGetProfile");

                                // dataGetProfile
                                $dataGetProfile['userName'] = $email;
                                $dataGetProfile['siteSignature'] = $siteSignature;

                                // preparing data
                                $dataToSFGetProfile = json_encode($dataGetProfile);

                                // $requesting = json_decode($dataToSFGetProfile);
                                // echo "<pre>";
                                // var_dump($dataToSFGetProfile);
                                // echo "</pre>";
                                $sfModule::logtxt("### Integration SF Get Profile: Json enviado -> $dataToSFGetProfile");

                                // Sending data
                                $resGetProfile = API::POST($urlGetProfile,$token,$dataToSFGetProfile);
                                $resuGetProfile = API::JSON_TO_ARRAY($resGetProfile);
                                $resultGetProfile = $resuGetProfile["operationCode"];
                                $resultArrayGetProfile = json_decode($resultGetProfile);

                                $sfModule::logtxt("### Integration SF Get Profile: Respuesta -> $resultGetProfile");

                                // echo "<pre>";
                                // var_dump($resultArrayGetProfile);
                                // echo "</pre>";

                                // Si obtiene el perfil
                                if ($resultArrayGetProfile->rCode == 'opCode_000') {
                                    $sfModule::logtxt("### Integration SF Get Profile: Perfil obtenido con éxito!");
                                    // Obtiene datos de la respuesta de la API
                                    $apellido = $resuGetProfile["conInput"]['LastName'];
                                    $nombre = $resuGetProfile["conInput"]['FirstName'];
                                    $fechaNac = $resuGetProfile["conInput"]['Birthdate'];
                                    $telefono = $resuGetProfile["conInput"]['MobilePhone'];
                                    $correo = $resuGetProfile["conInput"]['Email'];
                                    $idSF = $resuGetProfile["conInput"]['Contact_Num__c'];
                                    $plaintextPassword = $nombre.$idSF.$apellido;
                                    // hashing passwd
                                    $crypto = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
                                    $passwd = $crypto->hash($plaintextPassword);

                                    $sfModule::logtxt("### Creando Usuario ###");
                                    // now will customer register
                                    $customer = new Customer();
                                    $customer->lastname = $apellido;
                                    $customer->firstname = $nombre;
                                    $customer->email = $correo;
                                    $customer->passwd = $passwd;
                                    $customer->note = $idSF;
                                    $customer->active = 1;
                                    $customer->is_guest = 0;
                                    $customer->siret = $telefono;
                                    $customer->birthday = $fechaNac;
                                    // validate if the current customer was created!
                                    if($customer->add()) {
                                        $customer = $customer->getByEmail($correo);
                                        // Crea nueva direccion al cliente creado anteriormente
                                        $address = new Address();
                                        $address->id_country = $id_country;
                                        $address->id_customer = $customer->id;
                                        $address->id_manufacturer = 0;
                                        $address->id_supplier = 0;
                                        $address->id_warehouse = 0;
                                        $address->alias = 'Mi Dirección';
                                        $address->lastname = $apellido;
                                        $address->firstname = $nombre;
                                        $address->address1 = ' ';
                                        $address->city = ' ';
                                        $address->phone_mobile = $telefono;
                                        $address->active = 1;
                                        $address->deleted = 0;
                                        if($address->add()) {
                                            $sfModule::logtxt("--Address customer created!");
                                            // update table ps_address for leave empty fields
                                            $db = Db::getInstance();
                                            $sql = 'UPDATE '._DB_PREFIX_.'address SET address1="", city="" WHERE id_customer = '.$customer->id;
                                            $dbResult = $db->getValue($sql);
                                        }
                                        $sfModule::logtxt("### Usuario creado en prestashop con éxito!");

                                        if (is_null($customer->email)) {
                                            $customer->email = Tools::getValue('email');
                                        }
                                        // evalua el tiempo configurado por backend para la regeneración de contraseña
                                        if ((strtotime($customer->last_passwd_gen.'+'.($minTime = (int) Configuration::get('PS_PASSWD_TIME_FRONT')).' minutes') - time()) > 0) {
                                            $this->errors[] = $this->trans('You can regenerate your password only every %d minute(s)', array((int) $minTime), 'Shop.Notifications.Error');
                                        }else {
                                            // todo salio bien, aqui se envia la confirmación
                                            if (!$customer->hasRecentResetPasswordToken()) {
                                                $customer->stampResetPasswordToken();
                                                $customer->update();
                                            }
            
                                            $mailParams = array(
                                                '{email}' => $customer->email,
                                                '{lastname}' => $customer->lastname,
                                                '{firstname}' => $customer->firstname,
                                                '{url}' => $this->context->link->getPageLink('password', true, null, 'token='.$customer->secure_key.'&id_customer='.(int) $customer->id.'&reset_token='.$customer->reset_password_token),
                                            );
            
                                            // envia email
                                            if (
                                                Mail::Send(
                                                    $this->context->language->id,
                                                    'password_query',
                                                    $this->trans(
                                                        'Password query confirmation',
                                                        array(),
                                                        'Emails.Subject'
                                                    ),
                                                    $mailParams,
                                                    $customer->email,
                                                    $customer->firstname.' '.$customer->lastname
                                                )
                                            ) {
                                                $sfModule::logtxt("### Integration SF Email Verify: Email existe, se envia el link de confirmación!");
                                                $this->success[] = $this->trans('If this email address has been registered in our shop, you will receive a link to reset your password at %email%.', array('%email%' => $email), 'Shop.Notifications.Success');
                                                $this->setTemplate('customer/password-infos');
                
                                            }else {
                                                $this->errors[] = $this->trans('Hubo un problema y no pudimos enviar el correo de confirmación!', array(), 'Shop.Notifications.Error');
                                            }
            
            
                                        }// fin envio confirmacion

                                    }else {
                                        //maneja error
                                        $sfModule::logtxt("### Error. Usuario no fué creado en prestashop!");
                                    }
                                    
                                }else {
                                    //maneja error
                                    $sfModule::logtxt("### Integration SF Get Profile: Hubo un error - $resultArrayGetProfile->rCode");
                                }

                            }
                            

                        }else{
                            // maneja error
                            $sfModule::logtxt("### Integration SF Email Verify: Email no existente, no se puede enviar link!");
                            // $this->errors[] = 'Usuario no existe, por favor verifique el correo e intente de nuevo!';
                            $this->errors[] = $this->trans('Usuario no existe, por favor verifique el correo e intente de nuevo!', array(), 'Shop.Notifications.Error');

                        } // fin email verify

                    }else {
                        // sino maneja error
                        if($resultArrayOTP->rCode == 'errCode_010'){
                            $sfModule::logtxt("### Integration SF ResetOTP: Usuario no existe!");
                            $this->errors[] = $this->trans('Usuario no existe, por favor verifique el correo e intente de nuevo!', array(), 'Shop.Notifications.Error');
                        }else {
                            $sfModule::logtxt("### Integration SF ResetOTP: OTP Inválido, no se completó!");
                            $this->errors[] = 'Hubo un problema y no se pudo completar la solicitud!! - '.$resultArrayOTP->rCode;
                        }
                    }


                } // fin integration active
            } // fin modulo activo

            // si el modulo no esta activo se comporta de forma normal
            else {
                 // if (!($email = trim(Tools::getValue('email'))) || !Validate::isEmail($email)) {
                //     $this->errors[] = $this->trans('Invalid email address.', array(), 'Shop.Notifications.Error');
                // } else {
                //     $customer = new Customer();
                //     $customer->getByEmail($e$mailParams = array(
                                        //     '{email}' => $customer->email,
                                        //     '{lastname}' => $customer->lastname,
                                        //     '{firstname}' => $customer->firstname,
                                        //     '{url}' => $this->context->link->getPageLink('password', true, null, 'token='.$customer->secure_key.'&id_customer='.(int) $customer->id.'&reset_token='.$customer->reset_password_token),
                                        // );

                //     if (!Validate::isLoadedObject($customer)) {
                //         $this->success[] = $this->trans(
                //             'If this email address has been registered in our shop, you will receive a link to reset your password at %email%.',
                //             array('%email%' => $customer->email),
                //             'Shop.Notifications.Success'
                //         );
                //         $this->setTemplate('customer/password-infos');
                //     } elseif (!$customer->active) {
                //         $this->errors[] = $this->trans('You cannot regenerate the password for this account.', array(), 'Shop.Notifications.Error');
                //     } elseif ((strtotime($customer->last_passwd_gen.'+'.($minTime = (int) Configuration::get('PS_PASSWD_TIME_FRONT')).' minutes') - time()) > 0) {
                //         $this->errors[] = $this->trans('You can regenerate your password only every %d minute(s)', array((int) $minTime), 'Shop.Notifications.Error');
                //     } else {
                //         if (!$customer->hasRecentResetPasswordToken()) {
                //             $customer->stampResetPasswordToken();
                //             $customer->update();
                //         }

                //         $mailParams = array(
                //             '{email}' => $customer->email,
                //             '{lastname}' => $customer->lastname,
                //             '{firstname}' => $customer->firstname,
                //             '{url}' => $this->context->link->getPageLink('password', true, null, 'token='.$customer->secure_key.'&id_customer='.(int) $customer->id.'&reset_token='.$customer->reset_password_token),
                //         );

                //         if (
                //             Mail::Send(
                //                 $this->context->language->id,
                //                 'password_query',
                //                 $this->trans(
                //                     'Password query confirmation',
                //                     array(),
                //                     'Emails.Subject'
                //                 ),
                //                 $mailParams,
                //                 $customer->email,
                //                 $customer->firstname.' '.$customer->lastname
                //             )
                //         ) {
                //             $this->success[] = $this->trans('If this email address has been registered in our shop, you will receive a link to reset your password at %email%.', array('%email%' => $customer->email), 'Shop.Notifications.Success');
                //             $this->setTemplate('customer/password-infos');
                //         } else {
                //             $this->errors[] = $this->trans('An error occurred while sending the email.', array(), 'Shop.Notifications.Error');
                //         }
                //     }
                // }
            }

        } // fin modulo instalado

    }

    protected function changePassword()
    {

        $token = Tools::getValue('token');
        $id_customer = (int)Tools::getValue('id_customer');
        if ($email = Db::getInstance()->getValue('SELECT `email` FROM '._DB_PREFIX_.'customer c WHERE c.`secure_key` = \''.pSQL($token).'\' AND c.id_customer = '.$id_customer)) {
            $customer = new Customer();
            $customer->getByEmail($email);

            if (!Validate::isLoadedObject($customer)) {
                $this->errors[] = $this->trans('Customer account not found', array(), 'Shop.Notifications.Error');
            } elseif (!$customer->active) {
                $this->errors[] = $this->trans('You cannot regenerate the password for this account.', array(), 'Shop.Notifications.Error');
            }

            // Case if both password params not posted or different, then "change password" form is not POSTED, show it.
            if (!(Tools::isSubmit('passwd'))
                || !(Tools::isSubmit('confirmation'))
                || ($passwd = Tools::getValue('passwd')) !== ($confirmation = Tools::getValue('confirmation'))) {
                // Check if passwords are here anyway, BUT does not match the password validation format
                if (Tools::isSubmit('passwd') || Tools::isSubmit('confirmation')) {
                    $this->errors[] = $this->trans('The password and its confirmation do not match.', array(), 'Shop.Notifications.Error');
                }

                $this->context->smarty->assign([
                    'customer_email' => $customer->email,
                    'customer_token' => $token,
                    'id_customer' => $id_customer,
                    'reset_token' => Tools::getValue('reset_token'),
                ]);


                $this->setTemplate('customer/password-new');
            } else {
                // Both password fields posted. Check if all is right and store new password properly.
                if (!Tools::getValue('reset_token') || (strtotime($customer->last_passwd_gen.'+'.(int)Configuration::get('PS_PASSWD_TIME_FRONT').' minutes') - time()) > 0) {
                    Tools::redirect('index.php?controller=authentication&error_regen_pwd');
                } else {
                    // To update password, we must have the temporary reset token that matches.
                    if ($customer->getValidResetPasswordToken() !== Tools::getValue('reset_token')) {
                        $this->errors[] = $this->trans('The password change request expired. You should ask for a new one.', array(), 'Shop.Notifications.Error');
                    } else {
                        $customer->passwd = $this->get('hashing')->hash($password = Tools::getValue('passwd'), _COOKIE_KEY_);
                        $customer->last_passwd_gen = date('Y-m-d H:i:s', time());

                        if ($this->validate() == 'ok') { // valida passw
                            
                            // instanciamos el modulo sfwebservice
                            if (Module::isInstalled('sfwebservice')) {
                                $sfModule = Module::getInstanceByName('sfwebservice');

                                if (Validate::isLoadedObject($sfModule) && $sfModule->active) {

                                    if(Configuration::get('SFWEBSERVICE_ACTIVE_2') == '1'){

                                        // Data para enviar por la API
                                        $email = $customer->email;
                                        $passwd = Tools::getValue('passwd');
                                        $confirmation = Tools::getValue('confirmation');

                                        // codificando password for saleforce
                                        // $secretKey = 'DK7JRDhGJkDFfxKhHilbS6GIsAz78yYr';
                                        $secretKey = Configuration::get('SFWEBSERVICE_CLIENT_SECRETKEY_PASSW');
                                        $encrypt = hash_hmac('sha256', $passwd, $secretKey, true);
                                        $PasswEncrypted = base64_encode($encrypt);

                                        // Consulta db ps_country para el id del pais
                                        $db = Db::getInstance();
                                        $sql = 'SELECT id_country FROM '._DB_PREFIX_.'country WHERE active = 1';
                                        $id_country = $db->getValue($sql);

                                        // evalua idlang
                                        $idlang = (int) Configuration::get('PS_LANG_DEFAULT');

                                        // Obtiene nombre del pais
                                        $Country = new Country();
                                        $countryName = $Country::getNameById($idlang, $id_country);

                                        // evalua nombre para codificar pais para el siteSignature a enviar
                                        if($countryName == 'Perú') $siteSignature = 'PE';
                                        if($countryName == 'México') $siteSignature = 'MX';
                                        if($countryName == 'Colombia') $siteSignature = 'CO';

                                        // init lib for consume API
                                        require_once (_PS_MODULE_DIR_.'sfwebservice'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'API.php');

                                        // Authentication2.0
                                        $tokenApi = '';
                                        $params = array (
                                            'grant_type' => Configuration::get('SFWEBSERVICE_GRANT_TYPE_2'),
                                            'client_id' => Configuration::get('SFWEBSERVICE_CLIENT_ID_2'),
                                            'client_secret' => Configuration::get('SFWEBSERVICE_CLIENT_SECRET_2'),
                                            'username' => Configuration::get('SFWEBSERVICE_USERNAME_2'),
                                            'password' => Configuration::get('SFWEBSERVICE_PASSWORD_2')
                                        );
                                        // echo "<pre>";
                                        // var_dump($params);
                                        // echo "</pre>";
                                        // die();
                                        // error_log("params: ".json_encode($params));

                                        $URL_TOKEN = Configuration::get('SFWEBSERVICE_URL_GET_TOKEN_2');
                                        $rs = API::Authentication2($params,$URL_TOKEN);
                                        $array = API::JSON_TO_ARRAY($rs);
                                        $tokenApi = $array['access_token'];
                                        // echo "<pre>";
                                        // var_dump($token);
                                        // echo "</pre>";
                                        // die();

                                        // consume la API para setear el token para el nuevo passw
                                        $urlSetToken = Configuration::get('SFWEBSERVICE_URL_SET_TOKEN');
                                        // $sfModule::logtxt("### Integration SF: Set token -> $urlSetToken");

                                        // generate new token
                                        $NewToken = $this->generateToken(15);
                                        $sfModule::logtxt("### Integration SF: New Token -> $NewToken");

                                        // dataSetToken
                                        $dataSetToken['userEmail'] = $email;
                                        $dataSetToken['token'] = $NewToken;
                                        $dataSetToken['siteSignature'] = $siteSignature;

                                        // preparing data
                                        $dataToSFToken = json_encode($dataSetToken);

                                        // $requesting = json_decode($dataToSFToken);
                                        // echo "<pre>";
                                        // var_dump($dataToSFToken);
                                        // echo "</pre>";
                                        $sfModule::logtxt("### Integration SF New Token: Json enviado -> $dataToSFToken");

                                        // Sending data
                                        $resToken = API::POST($urlSetToken,$tokenApi,$dataToSFToken);
                                        $resuToken = API::JSON_TO_ARRAY($resToken);
                                        $resultToken = $resuToken["operationCode"];
                                        $resultArrayToken = json_decode($resultToken);

                                        $sfModule::logtxt("### Integration SF New Token: Respuesta -> $resultToken");

                                        // echo "<pre>";
                                        // var_dump($resultArrayToken);
                                        // echo "</pre>";

                                        // Si se setea el token
                                        if ($resultArrayToken->rCode == 'opCode_000') {
                                            $sfModule::logtxt("### Integration SF New Token: Token generado con éxito!");

                                            // consume la API para setear el nuevo passw
                                            $urlResetForgot = Configuration::get('SFWEBSERVICE_URL_RESET_FORGOT');
                                            // $sfModule::logtxt("### Integration SF Reset Forgot: Servicio -> $urlResetForgot");

                                            // dataResetForgot
                                            $dataResetForgot['userName'] = $email;
                                            $dataResetForgot['token'] = $NewToken;
                                            $dataResetForgot['newPassword'] = $PasswEncrypted;
                                            $dataResetForgot['isReset'] = 'true';
                                            $dataResetForgot['siteSignature'] = $siteSignature;

                                            // preparing data
                                            $dataToSFResetForgot = json_encode($dataResetForgot);

                                            // $requesting = json_decode($dataToSFResetForgot);
                                            // echo "<pre>";
                                            // var_dump($dataToSFResetForgot);
                                            // echo "</pre>";
                                            $sfModule::logtxt("### Integration SF Login: Json enviado -> $dataToSFResetForgot");

                                            // Sending data
                                            $resResForg = API::POST($urlResetForgot,$tokenApi,$dataToSFResetForgot);
                                            $resResetForg = API::JSON_TO_ARRAY($resResForg);
                                            $resultArrayResetForgot = json_decode($resResetForg);

                                            $sfModule::logtxt("### Integration SF Reset Forgot: Respuesta -> $resResForg");

                                            // echo "<pre>";
                                            // var_dump($resultArrayResetForgot);
                                            // echo "</pre>";

                                            if ($resultArrayResetForgot->rCode == 'opCode_000') {
                                                $sfModule::logtxt("### Integration SF Reset Forgot: Password reseteado con éxito!");

                                                if ($customer->update()) {
                                                    Hook::exec('actionPasswordRenew', array('customer' => $customer, 'password' => $password));
                                                    $customer->removeResetPasswordToken();
                                                    $customer->update();

                                                    $mail_params = [
                                                        '{email}' => $customer->email,
                                                        '{lastname}' => $customer->lastname,
                                                        '{firstname}' => $customer->firstname
                                                    ];

                                                    if (
                                                        Mail::Send(
                                                            $this->context->language->id,
                                                            'password',
                                                            $this->trans(
                                                                'Your new password',
                                                                array(),
                                                                'Emails.Subject'
                                                            ),
                                                            $mail_params,
                                                            $customer->email,
                                                            $customer->firstname.' '.$customer->lastname
                                                        )
                                                    ) {
                                                        $this->context->smarty->assign([
                                                            'customer_email' => $customer->email
                                                        ]);
                                                        $this->success[] = $this->trans('Your password has been successfully reset and a confirmation has been sent to your email address: %s', array($customer->email), 'Shop.Notifications.Success');
                                                        $this->context->updateCustomer($customer);
                                                        $this->redirectWithNotifications('index.php?controller=my-account');
                                                    } else {
                                                        $this->errors[] = $this->trans('An error occurred while sending the email.', array(), 'Shop.Notifications.Error');
                                                    }
                                                } else {
                                                    $this->errors[] = $this->trans('An error occurred with your account, which prevents us from updating the new password. Please report this issue using the contact form.', array(), 'Shop.Notifications.Error');
                                                }

                                            }else {
                                                $sfModule::logtxt("### Integration SF Reset Forgot: Error generando el token! - ".$resultArrayResetForgot->rCode);
                                            }

                                        }else {
                                            $sfModule::logtxt("### Integration SF New Token: Error generando el token! - $resultArrayToken->rCode");
                                        }


                                    } // fin sfwebservice integration is active

                                } // fin modulo active
                
                            } // fin instancia de modulo sfwebservice

                            
                        } // fin validate passw
                    }
                }
            }
        } else {
            $this->errors[] = $this->trans('We cannot regenerate your password with the data you\'ve submitted', array(), 'Shop.Notifications.Error');
        }

    }

    /**
     * @return bool
     */
    public function display()
    {
        $this->context->smarty->assign(
            array(
                'layout' => $this->getLayout(),
                'stylesheets' => $this->getStylesheets(),
                'javascript' => $this->getJavascript(),
                'js_custom_vars' => Media::getJsDef(),
                'errors' => $this->getErrors(),
                'successes' => $this->getSuccesses(),
            )
        );

        $this->smartyOutputContent($this->template);

        return true;
    }

    /**
     * @return array
     */
    protected function getErrors()
    {
        $notifications = $this->prepareNotifications();

        $errors = array();
        if (array_key_exists('error', $notifications)) {
            $errors = $notifications['error'];
        }

        return $errors;
    }

    /**
     * @return array
     */
    protected function getSuccesses()
    {
        $notifications = $this->prepareNotifications();

        $successes = array();

        if (array_key_exists('success', $notifications)) {
            $successes = $notifications['success'];
        }

        return $successes;
    }

    // Function to generate OTP 
    function generateNumericOTP($n) { 
        
        // Take a generator string which consist of 
        // all numeric digits 
        $generator = "1357902468"; 
    
        // Iterate for n-times and pick a single character 
        // from generator and append it to $result 
        
        // Login for generating a random character from generator 
        //     ---generate a random number 
        //     ---take modulus of same with length of generator (say i) 
        //     ---append the character at place (i) from generator to result 
    
        $result = ""; 
    
        for ($i = 1; $i <= $n; $i++) { 
            $result .= substr($generator, (rand()%(strlen($generator))), 1); 
        } 
    
        // Return result 
        return $result; 
    }

    // Function to generate token 
    function generateToken($n) { 
        
        // all numeric digits and letters
        $generator = "1357902468abcdefghijklmnopqrstuvwxyz"; 
    
        $result = ""; 
    
        for ($i = 1; $i <= $n; $i++) { 
            $result .= substr($generator, (rand()%(strlen($generator))), 1); 
        } 
    
        // Return result 
        return $result; 
    }

    // validate passw
    public function validate()
    {
        $token = Tools::getValue('token');
        $id_customer = (int)Tools::getValue('id_customer');
        if ($email = Db::getInstance()->getValue('SELECT `email` FROM '._DB_PREFIX_.'customer c WHERE c.`secure_key` = \''.pSQL($token).'\' AND c.id_customer = '.$id_customer)) {
            $customer = new Customer();
            $customer->getByEmail($email);

            if (!Validate::isLoadedObject($customer)) {
                $this->errors[] = $this->trans('Customer account not found', array(), 'Shop.Notifications.Error');
            } elseif (!$customer->active) {
                $this->errors[] = $this->trans('You cannot regenerate the password for this account.', array(), 'Shop.Notifications.Error');
            }


            // *** Passw validation...***
            $passwValue = Tools::getValue('passwd');

            if (!empty($passwValue)) {
                // regex here
                $re = '/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!.;\-&{}\[\]@#$%]{8,50}$/';
                // $re = '/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9!.@#$%^&*]{8,50}$/';

                $regexValid = preg_match($re, $passwValue, $matches, PREG_OFFSET_CAPTURE, 0);

                if(!$regexValid){
                    $this->errors[] = $this->trans('La Contraseña debe ser mínimo de 8 caracteres. Usa una combinación letras y números.', array(), 'Shop.Notifications.Error');
                    $this->context->smarty->assign([
                        'customer_email' => $customer->email,
                        'customer_token' => $token,
                        'id_customer' => $id_customer,
                        'reset_token' => Tools::getValue('reset_token'),
                    ]);
                    $this->setTemplate('customer/password-new');
                    return 'error';
                }else{
                    $this->context->smarty->assign([
                        'customer_email' => $customer->email,
                        'customer_token' => $token,
                        'id_customer' => $id_customer,
                        'reset_token' => Tools::getValue('reset_token'),
                    ]);
                    $this->setTemplate('customer/password-new');
                    // $this->redirectWithNotifications($this->getCurrentURL());
                    return 'ok';
                }
            }

        }


        // return parent::validate();
    }
}
