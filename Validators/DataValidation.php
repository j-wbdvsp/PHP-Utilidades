<?php
namespace Utilidades\Validators;

/**
 * Clase para validar diferentes tipos de datos en España.
 *
 * @package Utilidades\Validators
 * @author Juan Angulo <juan@webdevspain.com>
 * @version 1.0
 */
class DataValidation
{
    ######################
    # BOF Identificacion #
    ######################
    /**
     * Valida un número de NIF español.
     *
     * @param string $numero El número de NIF a validar.
     * @return bool True si el número es válido, False si no.
     */
    public static function validaNIF(string $numero): bool
    {
        return (bool) preg_match('/^[0-9]{8}[a-zA-Z]{1}$/', $numero);
    }

    /**
     * Valida un número de NIE español, tanto el formato viejo como el formato digital.
     *
     * @param string $numero El número de NIE a validar.
     * @return bool True si el número es válido, False si no.
     */
    public static function validaNIE(string $numero): bool
    {
        // Validar NIE formato antiguo (letra + 7 dígitos + letra)
        $formatoAntiguo = '/^[XYZxyz][0-9]{7}[A-Za-z]$/';

        // Validar NIE formato nuevo (letra + 8 dígitos + letra)
        $formatoNuevo = '/^[XYZxyz][0-9]{8}[A-Za-z]$/';

        return (bool) preg_match($formatoAntiguo, $numero) || preg_match($formatoNuevo, $numero);
    }

    /**
     * Valida un número de pasaporte.
     *
     * @param string $pasaporte El número de pasaporte a validar.
     * @param string $pais (opcional) El código del país para el cual se debe validar el pasaporte.
     * @return bool True si el pasaporte es válido, False si no.
     */
    public static function validaPasaporte(string $pasaporte, string $pais = 'ES'): bool
    {
        $regex = self::getRegexPasaportePorPais($pais);
        return (bool) preg_match($regex, $pasaporte);
    }

    /**
     * Obtiene la expresión regular para validar un pasaporte según el país.
     *
     * @param string $pais El código ISO2 del país para el cual se debe validar el pasaporte.
     * @return string La expresión regular para validar el pasaporte del país especificado.
     */
    private static function getRegexPasaportePorPais(string $pais): string
    {
        switch (strtoupper($pais)) {
            // Europa
            case 'AL':
            case 'AD':
            case 'CZ':
            case 'DK':
            case 'FI':
            case 'FR':
            case 'DE':
            case 'GR':
            case 'IS':
            case 'IT':
            case 'MK':
            case 'MD':
            case 'MC':
            case 'ME':
            case 'NL':
            case 'NO':
            case 'PL':
            case 'RO':
            case 'RU':
            case 'SM':
            case 'RS':
            case 'SI':
            case 'ES':
            case 'SE':
            case 'CH':
            case 'UA':
            case 'VA':
                // Expresión regular para pasaportes de varios países europeos
                return '/^[A-Z]{2}[0-9]{7}$/';
            case 'AT':
                // Expresión regular para pasaporte austriaco
                return '/^[A-Z]{1}[0-9]{7}$/';
            case 'BG':
                // Expresión regular para pasaporte búlgaro
                return '/^[0-9]{9}$/';
            case 'HR':
                // Expresión regular para pasaporte croata
                return '/^[A-Z]{2}[0-9]{7}$/';
            case 'CY':
                // Expresión regular para pasaporte chipriota
                return '/^[A-Z]{2}[0-9]{7}$/';
            case 'EE':
                // Expresión regular para pasaporte estonio
                return '/^[0-9A-Z]{7}$/';
            case 'HU':
                // Expresión regular para pasaporte húngaro
                return '/^[0-9A-Z]{2}[0-9]{7}$/';
            case 'LV':
                // Expresión regular para pasaporte letón
                return '/^[0-9A-Z]{7}$/';
            case 'LI':
                // Expresión regular para pasaporte liechtensteiniano
                return '/^[0-9]{3}[0-9]{3}[0-9]{3}$/';
            case 'LT':
                // Expresión regular para pasaporte lituano
                return '/^[0-9]{8}$/';
            case 'LU':
                // Expresión regular para pasaporte luxemburgués
                return '/^[0-9]{8}$/';
            case 'MT':
                // Expresión regular para pasaporte maltés
                return '/^[0-9]{7}$/';
            case 'NO':
                // Expresión regular para pasaporte noruego
                return '/^[0-9]{9}$/';
            case 'SK':
                // Expresión regular para pasaporte eslovaco
                return '/^[0-9A-Z]{8}$/';
            case 'VA':
                // Expresión regular para pasaporte del Vaticano
                return '/^[0-9]{5}$/';
            // América
            case 'CA':
            case 'MX':
            case 'BR':
            case 'CO':
            case 'VE':
            case 'CL':
            case 'PE':
            case 'GY':
            case 'SR':
            case 'UY':
            case 'PY':
            case 'BO':
                // Expresión regular para pasaportes de varios países americanos
                return '/^[A-Z]{2}[0-9]{6}$/';
            case 'AR':
                // Expresión regular para pasaporte argentino
                return '/^[A-Z]{3}[0-9]{6}$/';
            case 'US':
                // Expresión regular para pasaporte estadounidense
                return '/^[0-9]{9}$/';
            default:
                // Si no se encuentra un país específico, se usa una expresión regular genérica
                return '/.*/';
        }
    }
    
    /**
     * Valida un NIF de una empresa extranjera operando en España.
     *
     * @param string $nif El número de identificación fiscal a validar.
     * @return bool True si el NIF es válido, False si no.
     */
    public static function validaNIFEmpresaExtranjera(string $nif): bool
    {
        // Expresión regular para validar NIF de empresas extranjeras operando en España
        // Puede variar según las normativas específicas para cada país
        return (bool) preg_match('/^[A-HJ-NP-TV-Z]{1}[0-9]{7}[0-9A-J]{1}$/i', $nif);
    }

    /**
     * Valida un número de CIF español.
     *
     * @param string $numero El número de CIF a validar.
     * @return bool True si el número es válido, False si no.
     */
    public static function validaCIF(string $numero): bool
    {
        return (bool) preg_match('/^[ABCDEFGHJKLMNPQRSUVW]{1}\d{7}[0-9A-Ja-k]{1}$/', $numero);
    }
    ######################
    # EOF Identificacion #
    ######################
    
    ########################
    # BOF Datos Personales #
    ########################    
    /**
     * Valida un nombre completo.
     *
     * @param string $nombre El nombre completo a validar.
     * @return bool True si el nombre es válido, False si no.
     */
    public static function validaNombre(string $nombre): bool
    {
        // Se permite cualquier carácter alfabético, espacios y apóstrofes
        return (bool) preg_match('/^[\p{L}\s\']+$/u', $nombre);
    }    

    /**
     * Valida un código postal español.
     *
     * @param string $codigoPostal El código postal a validar.
     * @return bool True si el código postal es válido, False si no.
     */
    public static function validaCodigoPostal(string $codigoPostal): bool
    {
        return (bool) preg_match('/^(?:0[1-9]|[1-4]\d|5[0-2])\d{3}$/', $codigoPostal);
    }

    /**
     * Normaliza un número de teléfono eliminando espacios y caracteres no numéricos.
     *
     * @param string $telefono El número de teléfono a normalizar.
     * @return string El número de teléfono normalizado.
     */
    private static function normalizarTelefono(string $telefono): string
    {
        // Eliminar espacios y caracteres no numéricos del número de teléfono
        return preg_replace('/[^0-9]/', '', $telefono);
    }

    /**
     * Valida un número de teléfono según el país.
     *
     * @param string $telefono El número de teléfono a validar.
     * @param string $pais (opcional) El código del país para el cual se debe validar el número de teléfono.
     * @return bool True si el número de teléfono es válido para el país dado, False si no.
     */
    public static function validaTelefono(string $telefono, string $pais = 'ES'): bool
    {
        $regex = self::getRegexTelefonoPorPais($pais);
        return (bool) preg_match($regex, self::normalizarTelefono($telefono));
    }

    /**
     * Obtiene la expresión regular para validar un número de teléfono según el país.
     *
     * @param string $pais El código ISO2 del país para el cual se debe validar el número de teléfono.
     * @return string La expresión regular para validar el número de teléfono del país especificado.
     */
    private static function getRegexTelefonoPorPais(string $pais): string
    {

        switch (strtoupper($pais)) {
            // Europa
            case 'AL':
            case 'AD':
            case 'CZ':
            case 'DK':
            case 'FI':
            case 'FR':
            case 'DE':
            case 'GR':
            case 'IS':
            case 'IT':
            case 'MK':
            case 'MD':
            case 'MC':
            case 'ME':
            case 'NL':
            case 'NO':
            case 'PL':
            case 'RO':
            case 'RU':
            case 'SM':
            case 'RS':
            case 'SI':
            case 'ES':
            case 'SE':
            case 'CH':
            case 'UA':
            case 'VA':
                // Expresión regular para pasaportes de varios países europeos
                return '/^(?:\+?34)?[6789]\d{8}$/';
            case 'AT':
            case 'HR':
            case 'CY':
            case 'EE':
            case 'HU':
            case 'LV':
            case 'LI':
            case 'LT':
            case 'LU':
            case 'MT':
            case 'SK':
                // Expresión regular para otros países europeos con formato ligeramente diferente
                return '/^(?:\+?34)?\d{9}$/';
            // América
            case 'CA':
            case 'MX':
            case 'BR':
            case 'CO':
            case 'VE':
            case 'CL':
            case 'PE':
            case 'GY':
            case 'SR':
            case 'UY':
            case 'PY':
            case 'BO':
            case 'AR':
            case 'US':
                // Expresión regular para pasaportes de varios países americanos
                return '/^(?:\+?34)?[6789]\d{8}$/';
            default:
                return '/^\d+$/'; // Expresión regular genérica para otros países
        }
    }

    /**
     * Valida un correo electrónico.
     *
     * @param string $email El correo electrónico a validar.
     * @return bool True si el correo es válido, False si no.
     */
    public static function validaEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valida un número de seguridad social español (NSS).
     *
     * @param string $nss El número de seguridad social a validar.
     * @return bool True si el número es válido, False si no.
     */
    public static function validaNSS(string $nss): bool
    {
        return (bool) preg_match('/^[0-9]{12}$/', $nss);
    }

    /**
     * Valida un DNI/NIF/CIF/NIE español.
     *
     * @param string $documento El documento a validar.
     * @return bool True si el documento es válido, False si no.
     */
    public static function validaDocumento(string $documento): bool
    {
        return self::validaNIF($documento) || self::validaNIE($documento) || self::validaCIF($documento) || self::validaPasaporte($documento) || self::validaNIFEmpresaExtranjera($documento);
    }
    ########################
    # EOF Datos Personales #
    ########################

    #########################
    # BOF Datos Financieros #
    #########################
    /**
    * Valida un número de cuenta bancaria en formato IBAN.
    *
    * @param string $cuenta El número de cuenta bancaria en formato IBAN a validar.
    * @return bool True si la cuenta es válida, False si no.
    */
    public static function validaCuentaBancaria(string $cuenta): bool
    {
        // Elimina espacios y guiones
        $cuenta = strtoupper(str_replace([' ', '-'], '', $cuenta));

        // Comprueba que la longitud sea válida para un IBAN
        if (strlen($cuenta) < 15 || strlen($cuenta) > 34) {
            return false;
        }

        // Comprueba que contenga solo caracteres alfanuméricos
        if (!ctype_alnum($cuenta)) {
            return false;
        }

        // Comprueba el dígito de control
        return self::validaDigitoControlIBAN($cuenta);
    }

    /**
    * Valida el dígito de control de un IBAN.
    *
    * @param string $iban El IBAN a validar.
    * @return bool True si el dígito de control es válido, False si no.
    */
    private static function validaDigitoControlIBAN(string $iban): bool
    {
        $iban = substr($iban, 4) . substr($iban, 0, 4);
        $iban = str_replace(
            ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
            ['10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35'],
            $iban
        );

        $numero = intval($iban);
        $resto = $numero % 97;

        return $resto === 1;
    }    

    /**
     * Valida un número de tarjeta de crédito.
     *
     * @param string $tarjeta El número de tarjeta de crédito a validar.
     * @return bool True si el número es válido, False si no.
     */
    public static function validaTarjetaCredito(string $tarjeta): bool
    {
        return (bool) preg_match('/^\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}$/', $tarjeta);
    }
    #########################
    # EOF Datos Financieros #
    #########################
    
    #######################
    # BOF Datos Seguridad #
    #######################
    /**
     * Valida si una contraseña cumple con los requisitos de seguridad.
     *
     * @param string $password La contraseña a validar.
     * @return bool True si la contraseña es segura, False si no.
     */
    public static function validaPassword(string $password): bool
    {
        return (bool) preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,}$/', $password);
    }

    #######################
    # EOF Datos Seguridad #
    #######################

    ###################
    # BOF Otros Datos #
    ###################

    /**
     * Valida si una URL es válida.
     *
     * @param string $url La URL a validar.
     * @return bool True si la URL es válida, False si no.
     */
    public static function validaURL(string $url): bool
    {
        return (bool) filter_var($url, FILTER_VALIDATE_URL);
    }    
    ###################
    # EOF Otros Datos #
    ###################

}
?>