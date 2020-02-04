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

use Symfony\Component\Translation\TranslatorInterface;

/**
 * StarterTheme TODO: B2B fields, Genders, CSRF.
 */
class CustomerFormCore extends AbstractForm
{
    protected $template = 'customer/_partials/customer-form.tpl';

    private $context;
    private $urls;
    /** @var string phone */
    public $phone;
    /** @var string errors */
    public $errors;

    private $customerPersister;
    private $guest_allowed;
    private $passwordRequired = true;

    public function __construct(
        Smarty $smarty,
        Context $context,
        TranslatorInterface $translator,
        CustomerFormatter $formatter,
        CustomerPersister $customerPersister,
        array $urls
    ) {
        parent::__construct(
            $smarty,
            $translator,
            $formatter
        );

        $this->context = $context;
        $this->urls = $urls;
        $this->customerPersister = $customerPersister;
    }

    public function setGuestAllowed($guest_allowed = true)
    {
        $this->formatter->setPasswordRequired(!$guest_allowed);
        $this->guest_allowed = $guest_allowed;

        return $this;
    }

    public function setPasswordRequired($passwordRequired)
    {
        $this->passwordRequired = $passwordRequired;

        return $this;
    }

    public function fillFromCustomer(Customer $customer)
    {
        // Recuperamos los datos de ps_address filtrando por customer.
        $address = Customer::getAdressParamsByCustomer($customer->id);

        $params = get_object_vars($customer);
        $params['id_customer'] = $customer->id;
        $params['birthday'] = $customer->birthday === '00-00-0000' ? null : date('d-m-Y', strtotime(Tools::displayDate($customer->birthday)));
        // $params['birthday'] = $customer->birthday === '00-00-0000' ? null : Tools::displayDate($customer->birthday);
        $params['phone'] = $address['phone_mobile'];

        return $this->fillWith($params);
    }

    /**
     * @return \Customer
     */
    public function getCustomer()
    {
        $customer = new Customer($this->getValue('id_customer'));

        foreach ($this->formFields as $field) {
            $customerField = $field->getName();
            if ($customerField === 'id_customer') {
                $customerField = 'id';
            }
            if (property_exists($customer, $customerField)) {
                $customer->$customerField = $field->getValue();
            }
        }

        return $customer;
    }

    public function validate()
    {
        $emailField = $this->getField('email');
        $id_customer = Customer::customerExists($emailField->getValue(), true, true);
        $customer = $this->getCustomer();
        if ($id_customer && $id_customer != $customer->id) {
            $emailField->addError($this->translator->trans(
                'La dirección de correo electrónico "%mail%" ya está en uso, por favor, elija otra para registrarse', array('%mail%' => $emailField->getValue()), 'Shop.Notifications.Error'
            ));
        }

        // birthday is from input type text..., so we need to convert to a valid date
        $birthdayField = $this->getField('birthday');
        if (!empty($birthdayField)) {
            $birthdayValue = $birthdayField->getValue();
            if (!empty($birthdayValue)) {
                $dateBuilt = DateTime::createFromFormat(Context::getContext()->language->date_format_lite, $birthdayValue);
                if (!empty($dateBuilt)) {
                    $birthdayField->setValue($dateBuilt->format('Y-m-d'));
                }
            }
        }

        // phone is from input type text..., so we need to convert to a valid phone
        // $phoneField = $this->getField('phone');
        // if (!empty($phoneField)) {
        //     $phoneValue = $phoneField->getValue();

        //     if (!empty($phoneValue)) {
        //         $phone = Customer::checkPhone($phoneValue);

        //         if($phone === 'error-formato'){
        //             $phoneField->addError(sprintf(
        //                 $this->translator->trans(
        //                     'El campo dni no tiene un formato correcto (Ejemplo: 00000000A).', array(), 'Shop.Notifications.Error'
        //                 ),
        //                 $phoneField->getValue()
        //             ));
        //         }
        //     }
        // }

        // *** Passw validation...***
        $passwField = $this->getField('password');
        if (!empty($passwField)) {
            $passwValue = $passwField->getValue();

            if (!empty($passwValue)) {
                // regex here
                $re = '/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!.;\-&{}\[\]@#$%]{8,50}$/';
                // $re = '/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9!.@#$%^&*]{8,50}$/';


                $regexValid = preg_match($re, $passwValue, $matches, PREG_OFFSET_CAPTURE, 0);

                // Print the entire match result
                // var_dump($matches);

                if(!$regexValid){
                    $passwField->addError(sprintf(
                        $this->translator->trans(
                            'La Contraseña debe ser mínimo de 8 caracteres. Usa una combinación letras y números.', array(), 'Shop.Notifications.Error'
                        ),
                        $passwField->getValue()
                    ));
                }
            }
        }

        $this->validateFieldsLengths();
        $this->validateByModules();

        return parent::validate();
    }

    protected function validateFieldsLengths()
    {
        $this->validateFieldLength('email', 128, $this->getEmailMaxLengthViolationMessage());
        $this->validateFieldLength('firstname', 255, $this->getFirstNameMaxLengthViolationMessage());
        $this->validateFieldLength('lastname', 255, $this->getLastNameMaxLengthViolationMessage());

        // Añadimos validación para comprobar que la longitud del campo phone es correcto.
        $this->validateFieldLength('phone', 15, $this->getPhoneMaxLengthViolationMessage());
        $this->validateFieldMinimunLength('phone', 10, $this->getPhoneMaxLengthViolationMessage());
        // $this->validateFieldMinimunLength('password', 8, $this->getPasswMaxLengthViolationMessage());

        // evalua si se requiere newPassword
        $customerId = $this->context->customer->id;
        $newPassword = $this->getValue('new_password');
        if($customerId > 0){
            if(strlen($newPassword) > 0) {
                $this->validateFieldMinimunLength('new_password', 8, $this->getPasswMaxLengthViolationMessage());
            }
        }

    }

    protected function validateFieldMinimunLength($fieldName, $minimunLength, $violationMessage)
    {
        $field = $this->getField($fieldName);
        if (strlen($field->getValue()) < $minimunLength) {
            $field->addError($violationMessage);
        }
    }

    protected function getPhoneMaxLengthViolationMessage()
    {
        return $this->translator->trans(
        'El campo %1$s debe ser máximo de 10 números y el código de área (Ejemplo: +570000000000).',
            array('phone', 10),
            'Shop.Notifications.Error'
        );
    }

    protected function getPasswMaxLengthViolationMessage()
    {
        return $this->translator->trans(
        'La Contraseña debe ser mínimo de 8 caracteres. Usa una combinación letras y números',
            array('password', 8),
            'Shop.Notifications.Error'
        );
    }

    /**
     * @param $fieldName
     * @param $maximumLength
     * @param $violationMessage
     */
    protected function validateFieldLength($fieldName, $maximumLength, $violationMessage)
    {
        $emailField = $this->getField($fieldName);
        if (strlen($emailField->getValue()) > $maximumLength) {
            $emailField->addError($violationMessage);
        }
    }

    /**
     * @return mixed
     */
    protected function getEmailMaxLengthViolationMessage()
    {
        return $this->translator->trans(
            'The %1$s field is too long (%2$d chars max).',
            array('email', 128),
            'Shop.Notifications.Error'
        );
    }

    protected function getFirstNameMaxLengthViolationMessage()
    {
        return $this->translator->trans(
            'The %1$s field is too long (%2$d chars max).',
            array('first name', 255),
            'Shop.Notifications.Error'
        );
    }

    protected function getLastNameMaxLengthViolationMessage()
    {
        return $this->translator->trans(
            'The %1$s field is too long (%2$d chars max).',
            array('last name', 255),
            'Shop.Notifications.Error'
        );
    }

    public function submit()
    {
        if ($this->validate()) {
            $clearTextPassword = $this->getValue('password');
            $newPassword = $this->getValue('new_password');

            // echo "<pre>";
            // var_dump($this->context->customer);
            // echo "</pre>";
            // die();

            // instanciamos el modulo sfwebservice
            if (Module::isInstalled('sfwebservice')) {
                $sfModule = Module::getInstanceByName('sfwebservice');
                if (Validate::isLoadedObject($sfModule) && $sfModule->active) {

                    if(Configuration::get('SFWEBSERVICE_ACTIVE_2') == '1'){

                        // Data para guardar y enviar por la API
                        $id_customer = $this->getValue('id_customer'); // si esta vacio es nuevo sino es actualizacion
                        $firstname = $this->getValue('firstname');
                        $lastname = $this->getValue('lastname');
                        $email = $this->getValue('email');
                        $phone = $this->getValue('phone');
                        $password = $this->getValue('password');
                        $birthday = date('Y-m-d', strtotime($this->getValue('birthday')));

                        // codificando password for saleforce
                        // $secretKey = 'DK7JRDhGJkDFfxKhHilbS6GIsAz78yYr';
                        $secretKey = Configuration::get('SFWEBSERVICE_CLIENT_SECRETKEY_PASSW');
                        $encrypt = hash_hmac('sha256', $password, $secretKey, true);
                        $PasswEncrypted = base64_encode($encrypt);

                        // codificando new password for saleforce is required
                        if($newPassword) {
                            $encrypt2 = hash_hmac('sha256', $newPassword, $secretKey, true);
                            $PasswEncryptedNew = base64_encode($encrypt2);
                        }

                        // guarda phone en ps_address
                        // die('Customer id: '.$this->context->customer->id);
                        $address = new Address();
                        // evalua si ya el cliente tiene direcciones registradas
                        $addressExist = $address->getFirstCustomerAddressId($this->context->customer->id);
                        // error_log("id address: $addressExist");

                        // Consulta db ps_country para el id del pais
                        $db = Db::getInstance();
                        $sql = 'SELECT id_country FROM '._DB_PREFIX_.'country WHERE active = 1';
                        $id_country = $db->getValue($sql);

                        // evalua idlang
                        $idlang = (int) Configuration::get('PS_LANG_DEFAULT');

                        // Obtiene nombre del pais
                        $Country = new Country();
                        $countryName = $Country::getNameById($idlang, $id_country);

                        // evalua nombre para codificar pais
                        if($countryName == 'Perú') $codeCountry = 'PE';
                        if($countryName == 'México') $codeCountry = 'MX';
                        if($countryName == 'Colombia') $codeCountry = 'CO';

                        // obtiene el id de la tienda
                        $idShop = $this->context->shop->id;
                        // Shop::getContextShopID();
                        // evalua nombre de la tienda para el siteSignature a enviar
                        if ($idShop == 2) $siteSignature = $codeCountry.'_Pediasure';
                        if ($idShop == 3) $siteSignature = $codeCountry.'_Glucerna';
                        if ($idShop == 4) $siteSignature = $codeCountry.'_Ensure';
                        if ($idShop == 5) $siteSignature = $codeCountry.'_Similac';

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

                        $customerId = $this->context->customer->id;
                        $sfModule::logtxt("### Customer ID: $customerId");

                        // si no existe se crea
                        if ($customerId < 1) {

                            // consume la API para setear el OTP
                            $urlResetOTP = Configuration::get('SFWEBSERVICE_URL_RESET_OTP');
                            // $sfModule::logtxt("### Integration SF: Reset OTP -> $urlResetOTP");

                            // generate OTP
                            $OTP = $this->generateNumericOTP(6);
                            $sfModule::logtxt("### Integration SF: OTP -> $OTP");

                            // dataOTP
                            $dataOTP['userEmail'] = $email;
                            $dataOTP['OTP'] = $OTP;
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
                            if ($resultArrayOTP->rCode == 'errCode_010') {
                                $sfModule::logtxt("### Integration SF ResetOTP: Email no existe, se puede crear el registro!");

                                $sfModule::logtxt("### Registra ###");

                                // consume la API para guardar el registro en saleforce
                                $urlContactInsert = Configuration::get('SFWEBSERVICE_URL_CONTACT_INSERT');

                                // data3
                                $data3['FIRSTNAME'] = $firstname;
                                $data3['LASTNAME'] = $lastname;
                                $data3['Email'] = $email;
                                $data3['MobilePhone'] = $phone;
                                $data3['Birthdate'] = $birthday;
                                $data3['MailingCountry__c'] = $countryName;
                                $data3['City__c'] = '';
                                $data3['MailingPostalCode'] = '';
                                $data3['MailingStreet'] = '';
                                $data3['Sex__c'] = '';

                                // data5
                                $data5['Name'] = $email;
                                $data5['UserID__c'] = '';
                                $data5['Password__c'] = $PasswEncrypted;
                                $data5['UserEmail__c'] = $email;
                                $data5['IDKind__c'] = 'Text';
                                $data5['IDNumber__c'] = '';
                                $data5['OneTimePassword__c'] = '123456';

                                // preparing data
                                $data1 = '{"body":';
                                $data2 = '{"conInput":';
                                $data3 = json_encode($data3);
                                $data4 = ',"wsuInput":';
                                $data5 = json_encode($data5);
                                $data6 = ',"childrenInput":[]';
                                $data7 = '},"siteSignature":';
                                $data8 = '"'.$siteSignature.'"';
                                $data9 = '}';
                                $dataToSF = $data1.$data2.$data3.$data4.$data5.$data6.$data7.$data8.$data9;

                                // $requesting = json_decode($dataToSF);
                                // echo "<pre>";
                                // var_dump($dataToSF);
                                // echo "</pre>";
                                $sfModule::logtxt("### Integration SF: Json enviado -> $dataToSF");

                                // Sending data
                                $res = API::POST($urlContactInsert,$token,$dataToSF);
                                $resContactInsert = API::JSON_TO_ARRAY($res);
                                $resultInsert = $resContactInsert["operationCode"];
                                $resultArray = json_decode($resultInsert);

                                $sfModule::logtxt("### Integration SF: Respuesta -> $resultInsert");

                                // echo "<pre>";
                                // var_dump($resultArray->rCode);
                                // echo "</pre>";

                                if($resultArray->rCode == 'opCode_000'){
                                    // guarda registro
                                    $ok = $this->customerPersister->save(
                                        $this->getCustomer(),
                                        $clearTextPassword,
                                        $newPassword,
                                        $this->passwordRequired
                                    );

                                    if($ok){
                                        // var_dump('Guarda registro en prestashop');
                                        // crea el registro en prestashop
                                        $address->id_country = $id_country;
                                        $address->id_customer = $this->context->customer->id;
                                        $address->id_manufacturer = 0;
                                        $address->id_supplier = 0;
                                        $address->id_warehouse = 0;
                                        $address->alias = 'Mi dirección';
                                        $address->company = '';
                                        $address->lastname = $lastname;
                                        $address->firstname = $firstname;
                                        $address->address1 = ' ';
                                        $address->city = ' ';
                                        $address->phone_mobile = $phone;
                                        $address->active = 1;
                                        $address->deleted = 0;
                                        $address->date_add = date('Y-m-d H:i:s');
                                        $address->date_upd = date('Y-m-d H:i:s');
                                        if ($address->add()) {
                                            // error_log("### Address customer created!");
                                            $sfModule::logtxt("### Integration SF: Address customer created!");
                                            // update table ps_address for leave empty fields
                                            $db = Db::getInstance();
                                            $sql = 'UPDATE '._DB_PREFIX_.'address SET address1="", city="" WHERE id_customer = '.$this->context->customer->id;
                                            $dbResult = $db->getValue($sql);
                                        }
                                    }

                                    if (!$ok) {
                                        foreach ($this->customerPersister->getErrors() as $field => $errors) {
                                            $this->formFields[$field]->setErrors($errors);
                                        }
                                    }

                                }else{
                                    // maneja y muestra error
                                    // echo "<pre>";
                                    // var_dump('No se pudo completar la solicitud! - '.$resultArray->rCode);
                                    // echo "</pre>";
                                    // error_log('### No se pudo completar la solicitud! - '.$resultArray->rCode);
                                    $sfModule::logtxt('### Integration SF: No se pudo completar la solicitud! - '.$resultArray->rCode);
                                    $this->errors[] = 'No se pudo completar la solicitud! - '.$resultArray->rCode;

                                }

                                // echo "<pre>";
                                // var_dump(json_decode($resultInsert));
                                // echo "</pre>";
                                // die('aqui');

                            }else{
                                // maneja error
                                $sfModule::logtxt("### Integration SF Reset OTP: Email existente, usuario en uso!");
                                $this->errors[] = 'Email en uso, por favor utilice otro para poder registrarse!';
                            }



                        } else {

                            $sfModule::logtxt("### Actualiza ###");

                            // aqui vamos actualizar el password si es requerido
                            if($newPassword) {

                                // consume la API para actualizar el registro en saleforce
                                $urlChangePassw = Configuration::get('SFWEBSERVICE_URL_CHANGE_PASSW');
                                // $sfModule::logtxt("### Integration SF Change Passw: Servicio -> $urlChangePassw");

                                // dataCP
                                $dataCP['userName'] = $email;
                                $dataCP['curPassword'] = $PasswEncrypted;
                                $dataCP['newPAssword'] = $PasswEncryptedNew;
                                $dataCP['siteSignature'] = $siteSignature;

                                // preparing data
                                $dataToSFCP = json_encode($dataCP);

                                // $requesting = json_decode($dataToSFCP);
                                // echo "<pre>";
                                // var_dump($dataToSFCP);
                                // echo "</pre>";
                                $sfModule::logtxt("### Integration SF Change Passw: Json enviado -> $dataToSFCP");

                                // Sending data
                                $resCP = API::POST($urlChangePassw,$token,$dataToSFCP);
                                $resultChangePassw = API::JSON_TO_ARRAY($resCP);
                                $resultArrayChangePassw = json_decode($resultChangePassw);

                                $sfModule::logtxt("### Integration SF Change Passw: Respuesta -> $resCP");

                                // echo "<pre>";
                                // var_dump($resultArrayChangePassw);
                                // echo "</pre>";

                                if($resultArrayChangePassw->rCode == 'opCode_000'){
                                    // todo salio bien
                                    $sfModule::logtxt("### Integration SF Change Passw: Password updated");

                                    // consume la API para actualizar el registro en saleforce
                                    $urlContactUpdate = Configuration::get('SFWEBSERVICE_URL_CONTACT_UPDATE');

                                    // data3
                                    $data3['FIRSTNAME'] = $firstname;
                                    $data3['LASTNAME'] = $lastname;
                                    $data3['Email'] = $email;
                                    $data3['MobilePhone'] = $phone;
                                    $data3['Birthdate'] = $birthday;
                                    $data3['MailingCountry__c'] = $countryName;
                                    $data3['City__c'] = '';
                                    $data3['MailingPostalCode'] = '';
                                    $data3['MailingStreet'] = '';
                                    $data3['Sex__c'] = '';

                                    // data5
                                    $data5['Name'] = $email;
                                    $data5['UserID__c'] = ''; // especificar el id de saleforce para actualizar
                                    $data5['Password__c'] = $PasswEncryptedNew;
                                    $data5['UserEmail__c'] = $email;
                                    $data5['IDKind__c'] = 'Text';
                                    // $data5['IDNumber__c'] = '';
                                    $data5['OneTimePassword__c'] = '123456';

                                    // preparing data
                                    $data1 = '{"body":';
                                    $data2 = '{"conInput":';
                                    $data3 = json_encode($data3);
                                    $data4 = ',"wsuInput":';
                                    $data5 = json_encode($data5);
                                    $data6 = ',"childrenInput":[]';
                                    $data7 = '},"siteSignature":';
                                    $data8 = '"'.$siteSignature.'"';
                                    $data9 = '}';
                                    $dataToSF = $data1.$data2.$data3.$data4.$data5.$data6.$data7.$data8.$data9;

                                    // $requesting = json_decode($dataToSF);
                                    // echo "<pre>";
                                    // var_dump($dataToSF);
                                    // echo "</pre>";
                                    $sfModule::logtxt("### Integration SF: Json enviado -> $dataToSF");

                                    // Sending data
                                    $res = API::POST($urlContactUpdate,$token,$dataToSF);
                                    $resContactUpdate = API::JSON_TO_ARRAY($res);
                                    $resultUpdate = $resContactUpdate["operationCode"];
                                    $resultArray = json_decode($resultUpdate);

                                    $sfModule::logtxt("### Integration SF: Respuesta -> $resultUpdate");

                                    // echo "<pre>";
                                    // var_dump($resultArray->rCode);
                                    // echo "</pre>";

                                    if($resultArray->rCode == 'opCode_000'){
                                        // Actualiza registro
                                        $customer = new Customer($customerId);
                                        $customer->lastname = $lastname;
                                        $customer->firstname = $firstname;
                                        $customer->active = 1;
                                        $customer->siret = $phone;
                                        $customer->birthday = $birthday;
                                        // validate if the current customer was created!
                                        $resUpd = $customer->update();
                                        if($resUpd) {
                                            // Actualiza phone en address
                                            $address = new Address($addressExist);
                                            $address->phone_mobile = $phone;
                                            if ($address->update()) {
                                                // error_log("### Customer updated!");
                                                $sfModule::logtxt("### Integration SF: Phone updated!");
                                            }
                                            $sfModule::logtxt("### Integration SF: Customer updated!");
                                        }else {
                                            // maneja error
                                            $sfModule::logtxt('### Integration SF: No se actualizo la data en prestashop!');
                                            // $this->errors[] = 'No se pudo completar la solicitud! - '.$resultArray->rCode;
                                        }


                                    }else{
                                        // maneja y muestra error
                                        // echo "<pre>";
                                        // var_dump('No se pudo completar la solicitud! - '.$resultArray->rCode);
                                        // echo "</pre>";
                                        // error_log('#### No se pudo completar la solicitud! - '.$resultArray->rCode);
                                        $sfModule::logtxt('### Integration SF: No se pudo completar la solicitud! - '.$resultArray->rCode);
                                        $this->errors[] = 'No se pudo completar la solicitud! - '.$resultArray->rCode;
                                    }

                                }else{
                                   // maneja error
                                    $sfModule::logtxt("### Integration SF Change Passw: Error changing password - $resultArrayChangePassw->rCode");
                                    if ($resultArrayChangePassw->rCode == 'errCode_006') {
                                        $error_passw = 'La contraseña actual no coincide!';
                                    }
                                    if ($resultArrayChangePassw->rCode == 'errCode_002') {
                                        $error_passw = 'Usuario o contraseña invalido!';
                                    }
                                    if ($resultArrayChangePassw->rCode == 'errCode_003') {
                                        $error_passw = 'El usuario está deshabilitado o bloqueado. Por favor, contacte al administrador!';
                                    }
                                    $this->errors[] = 'No se pudo actualizar el password - '.$error_passw;

                                }

                            }else{ // fin $newPassword

                                // consume la API para actualizar el registro en saleforce
                                $urlContactUpdate = Configuration::get('SFWEBSERVICE_URL_CONTACT_UPDATE');

                                // data3
                                $data3['FIRSTNAME'] = $firstname;
                                $data3['LASTNAME'] = $lastname;
                                $data3['Email'] = $email;
                                $data3['MobilePhone'] = $phone;
                                $data3['Birthdate'] = $birthday;
                                $data3['MailingCountry__c'] = $countryName;
                                $data3['City__c'] = '';
                                $data3['MailingPostalCode'] = '';
                                $data3['MailingStreet'] = '';
                                $data3['Sex__c'] = '';

                                // data5
                                $data5['Name'] = $email;
                                $data5['UserID__c'] = ''; // especificar el id de saleforce para actualizar
                                $data5['Password__c'] = $PasswEncrypted;
                                $data5['UserEmail__c'] = $email;
                                $data5['IDKind__c'] = 'Text';
                                // $data5['IDNumber__c'] = '';
                                $data5['OneTimePassword__c'] = '123456';

                                // preparing data
                                $data1 = '{"body":';
                                $data2 = '{"conInput":';
                                $data3 = json_encode($data3);
                                $data4 = ',"wsuInput":';
                                $data5 = json_encode($data5);
                                $data6 = ',"childrenInput":[]';
                                $data7 = '},"siteSignature":';
                                $data8 = '"'.$siteSignature.'"';
                                $data9 = '}';
                                $dataToSF = $data1.$data2.$data3.$data4.$data5.$data6.$data7.$data8.$data9;

                                // $requesting = json_decode($dataToSF);
                                // echo "<pre>";
                                // var_dump($dataToSF);
                                // echo "</pre>";
                                $sfModule::logtxt("### Integration SF: Json enviado -> $dataToSF");

                                // Sending data
                                $res = API::POST($urlContactUpdate,$token,$dataToSF);
                                $resContactUpdate = API::JSON_TO_ARRAY($res);
                                $resultUpdate = $resContactUpdate["operationCode"];
                                $resultArray = json_decode($resultUpdate);

                                $sfModule::logtxt("### Integration SF: Respuesta -> $resultUpdate");

                                // echo "<pre>";
                                // var_dump($resultArray->rCode);
                                // echo "</pre>";

                                if($resultArray->rCode == 'opCode_000'){
                                    // Actualiza registro
                                    $customer = new Customer($customerId);
                                    $customer->lastname = $lastname;
                                    $customer->firstname = $firstname;
                                    $customer->active = 1;
                                    $customer->siret = $phone;
                                    $customer->birthday = $birthday;
                                    // validate if the current customer was created!
                                    $resUpd = $customer->update();
                                    if($resUpd) {
                                        // Actualiza phone en address
                                        $address = new Address($addressExist);
                                        $address->phone_mobile = $phone;
                                        if ($address->update()) {
                                            // error_log("### Customer updated!");
                                            $sfModule::logtxt("### Integration SF: Phone updated!");
                                        }
                                        $sfModule::logtxt("### Integration SF: Customer updated!");
                                    }else {
                                        // maneja error
                                        $sfModule::logtxt('### Integration SF: No se actualizo la data en prestashop!');
                                        // $this->errors[] = 'No se pudo completar la solicitud! - '.$resultArray->rCode;
                                    }


                                }else{
                                    // maneja y muestra error
                                    // echo "<pre>";
                                    // var_dump('No se pudo completar la solicitud! - '.$resultArray->rCode);
                                    // echo "</pre>";
                                    // error_log('#### No se pudo completar la solicitud! - '.$resultArray->rCode);
                                    $sfModule::logtxt('### Integration SF: No se pudo completar la solicitud! - '.$resultArray->rCode);
                                    $this->errors[] = 'No se pudo completar la solicitud! - '.$resultArray->rCode;

                                }

                            }


                            // die('aqui 2');

                        } // $curtomerID < 1
                    } // SFWEBSERVICE_ACTIVE_2

                } // Validate::isLoadedObject($sfModule) && $sfModule->active

            } // Module::isInstalled


            // die('hasta aqui llega');
            // if(isset($ok)){
            //     return $ok;
            // }
            return !$this->hasErrors();

        }

        return false;
    }

    public function getTemplateVariables()
    {
        return [
            'action' => $this->action,
            'urls' => $this->urls,
            'errors' => $this->getErrors(),
            'hook_create_account_form' => Hook::exec('displayCustomerAccountForm'),
            'formFields' => array_map(
                function (FormField $field) {
                    return $field->toArray();
                },
                $this->formFields
            ),
        ];
    }

    /**
     * This function call the hook validateCustomerFormFields of every modules
     * which added one or several fields to the customer registration form.
     *
     * Note: they won't get all the fields from the form, but only the one
     * they added.
     */
    private function validateByModules()
    {
        $formFieldsAssociated = array();
        // Group FormField instances by module name
        foreach ($this->formFields as $formField) {
            if (!empty($formField->moduleName)) {
                $formFieldsAssociated[$formField->moduleName][] = $formField;
            }
        }
        // Because of security reasons (i.e password), we don't send all
        // the values to the module but only the ones it created
        foreach ($formFieldsAssociated as $moduleName => $formFields) {
            if ($moduleId = Module::getModuleIdByName($moduleName)) {
                // ToDo : replace Hook::exec with HookFinder, because we expect a specific class here
                $validatedCustomerFormFields = Hook::exec('validateCustomerFormFields', array('fields' => $formFields), $moduleId, true);

                if (is_array($validatedCustomerFormFields)) {
                    array_merge($this->formFields, $validatedCustomerFormFields);
                }
            }
        }
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

}
