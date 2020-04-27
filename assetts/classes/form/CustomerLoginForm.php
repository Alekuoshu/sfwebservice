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

class CustomerLoginFormCore extends AbstractForm
{
    private $context;
    private $urls;

    protected $template = 'customer/_partials/login-form.tpl';

    public function __construct(
        Smarty $smarty,
        Context $context,
        TranslatorInterface $translator,
        CustomerLoginFormatter $formatter,
        array $urls
    ) {
        parent::__construct(
            $smarty,
            $translator,
            $formatter
        );

        $this->context = $context;
        $this->translator = $translator;
        $this->formatter = $formatter;
        $this->urls = $urls;
        $this->constraintTranslator = new ValidateConstraintTranslator(
            $this->translator
        );
    }

    public function validate()
    {
        // *** Email validation...***
        // $emailField = $this->getField('email');
        // $id_customer = Customer::customerExists($emailField->getValue(), true, true);
        // if (!$id_customer) {
        //     $emailField->addError($this->translator->trans(
        //         'El correo "%mail%" no está registrado aún, por favor registrese para poder continuar.', array('%mail%' => $emailField->getValue()), 'Shop.Notifications.Error'
        //     ));
        // } // fin email validation

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
        } // fin passw validation

        $this->validateFieldsLengths();
        // $this->validateByModules();

        return parent::validate();
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

    protected function validateFieldsLengths()
    {
        $this->validateFieldLength('email', 128, $this->getEmailMaxLengthViolationMessage());
        // $this->validateFieldMinimunLength('password', 8, $this->getPasswMaxLengthViolationMessage());

    }

    protected function validateFieldMinimunLength($fieldName, $minimunLength, $violationMessage)
    {
        $field = $this->getField($fieldName);
        if (strlen($field->getValue()) < $minimunLength) {
            $field->addError($violationMessage);
        }
    }

    // protected function getPasswMaxLengthViolationMessage()
    // {
    //     return $this->translator->trans(
    //     'La Contraseña debe ser mínimo de 8 caracteres. Usa una combinación letras y números',
    //         array('password', 8),
    //         'Shop.Notifications.Error'
    //     );
    // }

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

    public function submit()
    {
        if ($this->validate()) {
            Hook::exec('actionAuthenticationBefore');

            // instanciamos el modulo sfwebservice
            if (Module::isInstalled('sfwebservice')) {
                $sfModule = Module::getInstanceByName('sfwebservice');

                if (Validate::isLoadedObject($sfModule) && $sfModule->active) {

                    if(Configuration::get('SFWEBSERVICE_ACTIVE_2') == '1'){

                        // Data para guardar y enviar por la API
                        $email = $this->getValue('email');
                        $password = $this->getValue('password');

                        // codificando password for saleforce
                        // $secretKey = 'DK7JRDhGJkDFfxKhHilbS6GIsAz78yYr';
                        $secretKey = Configuration::get('SFWEBSERVICE_CLIENT_SECRETKEY_PASSW');
                        $encrypt = hash_hmac('sha256', $password, $secretKey, true);
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
                        // $sfModule::logtxt("### Token: $token");
                        // echo "<pre>";
                        // var_dump($token);
                        // echo "</pre>";
                        // die();

                        // $customerId = $this->context->customer->id;
                        // $sfModule::logtxt("### Customer ID: $customerId");

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
                                // todo salio bien
                                $sfModule::logtxt("### Integration SF Email Verify: Email existe, se puede loguear!");
                                $sfModule::logtxt("### Loguea ###");

                                // consume la API para login
                                $urlLogin = Configuration::get('SFWEBSERVICE_URL_LOGIN');
                                // $sfModule::logtxt("### Integration SF Login: Servicio -> $urlLogin");

                                // dataLog
                                $dataLog['userName'] = $email;
                                $dataLog['password'] = $PasswEncrypted;
                                $dataLog['siteSignature'] = $siteSignature;

                                // preparing data
                                $dataToSFLog = json_encode($dataLog);

                                // $requesting = json_decode($dataToSFLog);
                                // echo "<pre>";
                                // var_dump($dataToSFLog);
                                // echo "</pre>";
                                $sfModule::logtxt("### Integration SF Login: Json enviado -> $dataToSFLog");

                                // Sending data
                                $resLog = API::POST($urlLogin,$token,$dataToSFLog);
                                $resLogin = API::JSON_TO_ARRAY($resLog);
                                $resultLogin = $resLogin["operationCode"];
                                $resultArrayLogin = json_decode($resultLogin);

                                $sfModule::logtxt("### Integration SF Login: Respuesta -> $resultLogin");
                                // $sfModule::logtxt("### Integration SF Login: Respuesta -> $resLog");

                                // echo "<pre>";
                                // var_dump($resultArrayLogin);
                                // echo "</pre>";

                                if ($resultArrayLogin->rCode == 'opCode_000') {
                                    // todo salio bien
                                    // Obtiene datos de la respuesta del login
                                    $apellido = $resLogin["conInput"]['LastName'];
                                    $nombre = $resLogin["conInput"]['FirstName'];
                                    $idSF = $resLogin["conInput"]['Contact_Num__c'];

                                    $customer = new Customer();
                                    $customerExist = $customer->getByEmail($email);
                                    if (!Validate::isLoadedObject($customerExist)){
                                        $sfModule::logtxt("### Integration SF Login: Usuario no existe en prestashop, se va a crear!");
                                        // Crea el cliente con datos del brandsite
                                        $customer->lastname = $apellido;
                                        $customer->firstname = $nombre;
                                        $customer->email = $email;
                                        $customer->passwd = Tools::encrypt($password);
                                        $customer->note = $idSF;
                                        $customer->active = 1;
                                        $resAdd = $customer->add();
                                        if($resAdd) {
                                            $sfModule::logtxt("### Integration SF: Customer created successfull!");
                                            // crea el registro address en prestashop
                                            $address = new Address();
                                            $address->id_country = $id_country;
                                            $address->id_customer = $customer->id;
                                            $address->id_manufacturer = 0;
                                            $address->id_supplier = 0;
                                            $address->id_warehouse = 0;
                                            $address->alias = 'Mi dirección';
                                            $address->company = '';
                                            $address->lastname = $apellido;
                                            $address->firstname = $nombre;
                                            $address->address1 = ' ';
                                            $address->city = ' ';
                                            // $address->phone_mobile = $phone;
                                            $address->active = 1;
                                            $address->deleted = 0;
                                            $address->date_add = date('Y-m-d H:i:s');
                                            $address->date_upd = date('Y-m-d H:i:s');
                                            if ($address->add()) {
                                                // error_log("### Address customer created!");
                                                $sfModule::logtxt("### Integration SF: Address customer created!");
                                            }else {
                                                $sfModule::logtxt('### Integration SF: Error al intentar crear direccion en prestashop!');
                                            }

                                            // login customer
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

                                            $sfModule::logtxt("### Integration SF Login: Logueado con éxito!");

                                        }else {
                                            // maneja error
                                            $sfModule::logtxt('### Integration SF: Error al intentar crear usuario en prestashop!');
                                        }

                                    }else{
                                        $sfModule::logtxt("### Integration SF Login: Usuario existe, se va a actualizar y loguear!");
                                        // Actualiza algunos datos que vienen del brandsite
                                        $customerUpd = new Customer($customer->id);
                                        $customerUpd->lastname = $apellido;
                                        $customerUpd->firstname = $nombre;
                                        $customerUpd->passwd = Tools::encrypt($password);
                                        $customerUpd->note = $idSF;
                                        $resUpd = $customerUpd->update();
                                        if($resUpd) {
                                            $sfModule::logtxt("### Integration SF: Some Customer data updated!");
                                        }else {
                                            // maneja error
                                            $sfModule::logtxt('### Integration SF: No se actualizo la data en prestashop!');
                                        }

                                        // login customer
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

                                        $sfModule::logtxt("### Integration SF Login: Logueado con éxito!");
                                    }
                                    
                                    

                                }else{
                                    // maneja error
                                    if($resultArrayLogin->rCode == 'errCode_002'){
                                        $sfModule::logtxt("### Integration SF Login: Usuario o contraseña invalido!");
                                        $this->errors[] = 'Usuario o contraseña invalido! - '.$resultArrayLogin->rCode;
                                    }elseif($resultArrayLogin->rCode == 'errCode_003'){
                                        $sfModule::logtxt("### Integration SF Login: El usuario está deshabilitado o bloqueado. Por favor, contacte al administrador!");
                                        $this->errors[] = 'El usuario está deshabilitado o bloqueado. Por favor, contacte al administrador! - '.$resultArrayLogin->rCode;
                                    }else {
                                        $sfModule::logtxt("### Integration SF Login: Hubo un problema y no se pudo completar la solicitud!");
                                        $this->errors[] = 'Hubo un problema y no se pudo completar la solicitud!! - '.$resultArrayLogin->rCode;
                                    }
                                    
                                }

                            }else{
                                // maneja error
                                $sfModule::logtxt("### Integration SF Email Verify: Email no existente, debe registrarse!");
                                $this->errors[] = 'Usuario no existe, por favor registrese para continuar -> ';
                            }

                        }else {
                            // sino maneja error
                            if($resultArrayOTP->rCode == 'errCode_010'){
                                $sfModule::logtxt("### Integration SF ResetOTP: Usuario no existe!");
                                $this->errors[] = 'Usuario no existe, por favor registrese para continuar -> ';
                            }else {
                                $sfModule::logtxt("### Integration SF ResetOTP: OTP Inválido, no se completó!");
                                $this->errors[] = 'Hubo un problema y no se pudo completar la solicitud!! - '.$resultArrayOTP->rCode;
                            }
                        }


                    } // fin sfwebservice integration is active

                } // fin modulo active

            } // fin instancia de modulo sfwebservice


        } // fin validate

        return !$this->hasErrors();
    }

    public function getTemplateVariables()
    {
        if (!$this->formFields) {
            $this->formFields = $this->formatter->getFormat();
        }

        return [
            'action'        => $this->action,
            'urls'          => $this->urls,
            'formFields'    => array_map(
                function (FormField $field) {
                    return $field->toArray();
                },
                $this->formFields
            ),
            'errors' => $this->getErrors()
        ];
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
