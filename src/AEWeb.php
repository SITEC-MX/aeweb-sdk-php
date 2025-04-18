<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * SiTEC AE - Administrador de Empresas Web
 *
 * v.1.0.0.0 - 2021-02-19
 */
namespace Mpsoft\AEWeb;

use \GuzzleHttp\Client;
use \Exception;
use \Throwable;

class AEWeb
{
    private $empresa = NULL;
    private $token = NULL;
    private $token_tipo = NULL;
    private $host = "aeweb.sitec-mx.com";

    private $guzzle = NULL;
    private $openapi = NULL;

    public const NO_IMPLEMENTADO = 0;
    public const ENDPOINT_ERROR_GENERAR = 950;
    public const RESPUESTA_NO_DISPONIBLE = 951;
	public const OK = 1;

    public const OPERADOR_IGUAL = 1;
    public const OPERADOR_MAYORQUE = 2;
    public const OPERADOR_MAYOROIGUALQUE = 3;
    public const OPERADOR_MENORQUE = 4;
    public const OPERADOR_MENOROIGUALQUE = 5;
    public const OPERADOR_DIFERENTE = 6;
    public const OPERADOR_LIKE = 7;
    public const OPERADOR_IN = 8;
    public const OPERADOR_IGUAL_BINARIO = 9;
    public const OPERADOR_NOT_IN = 10;

    public const LOGICA_Y = 1;
    public const LOGICA_O = 2;

    function __construct(string $empresa, string $token, string $token_tipo = "app")
    {
        $this->empresa = $empresa;
        $this->token = $token;
        $this->token_tipo = $token_tipo;

        $this->guzzle = new Client();
    }

    private function ObtenerURLEndPoint(string $url, array $variables, ?array $querystrings)
    {
        $endpoint_elementos = array();
        $endpoint_elementos[] = $this->host;

        $url_elementos = explode("/", $url);
        foreach($url_elementos as $url_elemento) // Para cada elemento de la URL
        {
            if($url_elemento[0] == "<") // Si es una variable
            {
                $variable_nombre = substr($url_elemento, 1, -1);

                if( isset($variables[$variable_nombre]) ) // Si se proporciona la variable requerida
                {
                    $endpoint_elementos[] = $variables[$variable_nombre];
                }
                else // Si no se proporciona la variable requerida
                {
                    throw new Exception("No se proporcionó la variable '{$variable_nombre}'.");
                }
            }
            else // Si no es una variable
            {
                $endpoint_elementos[] = $url_elemento;
            }
        }

        $querystring = NULL;
        if($querystrings) // Si se proporciona query-string
        {
            $querystring = "?" . http_build_query($querystrings);
        }

        $endpoint_url = implode("/", $endpoint_elementos);
        return "https://{$endpoint_url}{$querystring}";
    }

    public function API_CALL(string $metodo, string $url, ?array $variables=NULL, ?array $querystrings=NULL, ?array $body=NULL)
    {
        $estado = array("estado"=>AEWeb::NO_IMPLEMENTADO, "mensaje"=>"OK");

        if(!$variables) // Si no se proporcionan las variables
        {
            $variables = array();
        }

        // Inyectamos la empresa al listado de variables
        $variables["empresa"] = $this->empresa;

        // Calculamos la URL de la llamada
        $endpoint_url = NULL;
        try
        {
            $endpoint_url = $this->ObtenerURLEndPoint($url, $variables, $querystrings);
        }
        catch(Throwable $t) // Error al generar la URL de la llamada
        {
            $estado = array("estado"=>AEWeb::ENDPOINT_ERROR_GENERAR, "mensaje"=>"Error al generar la URL de la llamada.", "debug"=> utf8_encode($t->getMessage()));
        }

        if($endpoint_url) // Si se obtiene la URL de la llamada
        {
            // Generamos las opciones
            $opciones = array();
            $opciones["auth"] = array($this->token_tipo, $this->token);

            if($body)
            {
                $opciones["json"] = $body;
            }

            $response = NULL;
            try
            {
                $response = $this->guzzle->request($metodo, $endpoint_url, $opciones);
            }
            catch(Throwable $t) // Error al generar la URL del Endpoint
            {
                $response = $t->getResponse();
            }

            if($response) // Si hay respuesta
            {
                $response_text = $response->getBody();
                $estado = json_decode($response_text, TRUE);
            }
            else // Si no hay respuesta
            {
                $estado = array("estado"=>AEWeb::RESPUESTA_NO_DISPONIBLE, "mensaje"=>"Error al obtener la respuesta de la llamada.");
            }
        }

        return $estado;
    }

    private function ObtenerFirmaDeVariables(?array $variables = NULL)
    {
        if(!$variables)
        {
            $variables = array();
        }

        if(! in_array("empresa", $variables)) // Si no se proporciona empresa como variable
        {
            $variables["empresa"] = NULL;
        }

        $variables_proporcionadas = array_keys($variables);
        asort($variables_proporcionadas);
        $variables_key = implode("-", $variables_proporcionadas);

        return $variables_key;
    }


	public function GET_Perfil(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/perfil"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_EmpresaRegiones(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/empresa/regiones"; break; case "empresa-id": $url = "<empresa>/empresa/regiones/<id>"; break;  default: $url = "<empresa>/empresa/regiones/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_EmpresaRegiones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/empresa/regiones"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_EmpresaRegiones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/empresa/regiones/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_EmpresaRegiones(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/empresa/regiones/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_EmpresaAlmacenes(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/empresa/almacenes"; break; case "empresa-id": $url = "<empresa>/empresa/almacenes/<id>"; break;  default: $url = "<empresa>/empresa/almacenes/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_EmpresaAlmacenes(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/empresa/almacenes"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_EmpresaAlmacenes(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/empresa/almacenes/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_EmpresaAlmacenes(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/empresa/almacenes/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_EmpresaReportesconduccion(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/empresa/reportesconduccion/<id>/<aleatorio>"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_ComisionResumen(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/comision/resumen"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_ComisionDefiniciones(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/comision/definiciones"; break; case "empresa-id": $url = "<empresa>/comision/definiciones/<id>"; break;  default: $url = "<empresa>/comision/definiciones/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ComisionDefiniciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/comision/definiciones"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_ComisionDefiniciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/comision/definiciones/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_ComisionDefiniciones(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/comision/definiciones/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_ComisionPeriodos(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/comision/periodos"; break; case "empresa-id": $url = "<empresa>/comision/periodos/<id>"; break;  default: $url = "<empresa>/comision/periodos/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ComisionPeriodos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/comision/periodos"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_ComisionPeriodos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/comision/periodos/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_ComisionPeriodos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/comision/periodos/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_CatalogoIdiomas(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/catalogo/idiomas"; break; case "empresa-id": $url = "<empresa>/catalogo/idiomas/<id>"; break;  default: $url = "<empresa>/catalogo/idiomas/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_CatalogoIdiomas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/catalogo/idiomas"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_CatalogoIdiomas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/catalogo/idiomas/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_CatalogoIdiomas(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/catalogo/idiomas/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_TiendaHreflangs(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/tienda/hreflangs"; break; case "empresa-id": $url = "<empresa>/tienda/hreflangs/<id>"; break;  default: $url = "<empresa>/tienda/hreflangs/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_TiendaHreflangs(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/tienda/hreflangs"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_TiendaHreflangsQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/tienda/hreflangs/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_TiendaHreflangs(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/tienda/hreflangs/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_TiendaHreflangs(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/tienda/hreflangs/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_TiendaContactos(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/tienda/contactos"; break; case "empresa-id": $url = "<empresa>/tienda/contactos/<id>"; break;  default: $url = "<empresa>/tienda/contactos/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_TiendaContactos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/tienda/contactos"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_TiendaContactos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/tienda/contactos/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_TiendaContactos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/tienda/contactos/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_SesionPerfil(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/sesion/perfil"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_SesionVerificarCredenciales(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/verificar-credenciales"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_SesionLogin(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/login"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_SesionLogout(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/logout"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_SesionTfaGenerar(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/sesion/tfa-generar"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_SesionTfaHabilitar(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/tfa-habilitar"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_SesionDesbloquear(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/desbloquear"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_CfdiUsocfdis(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/cfdi/usocfdis"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_CfdiExportaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/cfdi/exportaciones"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_CfdiObjetoimpuestos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/cfdi/objetoimpuestos"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_CfdiMotivoscancelacion(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/cfdi/motivoscancelacion"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_CfdiConfigautotransportes(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/cfdi/configautotransportes"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_CfdiTipopermisossct(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/cfdi/tipopermisossct"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_ContabilidadCuentas(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/contabilidad/cuentas"; break; case "empresa-id": $url = "<empresa>/contabilidad/cuentas/<id>"; break;  default: $url = "<empresa>/contabilidad/cuentas/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadCuentas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/cuentas"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_ContabilidadCuentas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/cuentas/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_ContabilidadCuentas(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/cuentas/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_ContabilidadDescargasmasivasNotificar(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/descargasmasivas/<id>/notificar"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_ContabilidadDescargasmasivas(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/contabilidad/descargasmasivas"; break; case "empresa-id": $url = "<empresa>/contabilidad/descargasmasivas/<id>"; break;  default: $url = "<empresa>/contabilidad/descargasmasivas/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_ContabilidadDescargasmasivasRangodisponible(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/descargasmasivas/rangodisponible"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadPrecioQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/precio/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_ContabilidadFacturasrecibidas(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/contabilidad/facturasrecibidas"; break; case "empresa-id": $url = "<empresa>/contabilidad/facturasrecibidas/<id>"; break;  default: $url = "<empresa>/contabilidad/facturasrecibidas/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadFacturasrecibidas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/facturasrecibidas"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function DELETE_ContabilidadFacturasrecibidas(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/facturasrecibidas/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadFacturasrecibidasPdf(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/facturasrecibidas/<id>/pdf"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_ContabilidadFacturasrecibidasImportar(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/facturasrecibidas/<id>/importar"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadFacturasrecibidasImportar(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/facturasrecibidas/<id>/importar"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_ContabilidadFacturasrecibidasVincular(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/facturasrecibidas/<id>/vincular"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadFacturasrecibidasVincular(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/facturasrecibidas/<id>/vincular"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_ContabilidadSolicitudesdescarga(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/contabilidad/solicitudesdescarga"; break; case "empresa-id": $url = "<empresa>/contabilidad/solicitudesdescarga/<id>"; break;  default: $url = "<empresa>/contabilidad/solicitudesdescarga/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadSolicitudesdescarga(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/solicitudesdescarga"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_ContabilidadKardex(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/kardex"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_ContabilidadImportadorcfdiProveedores(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/contabilidad/importadorcfdi/proveedores"; break; case "empresa-id": $url = "<empresa>/contabilidad/importadorcfdi/proveedores/<id>"; break;  default: $url = "<empresa>/contabilidad/importadorcfdi/proveedores/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadImportadorcfdiProveedores(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/importadorcfdi/proveedores"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_ContabilidadImportadorcfdiProveedores(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/importadorcfdi/proveedores/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_ContabilidadImportadorcfdiProveedores(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/importadorcfdi/proveedores/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_ContabilidadImportadorcfdiEquivalencias(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/contabilidad/importadorcfdi/equivalencias"; break; case "empresa-id": $url = "<empresa>/contabilidad/importadorcfdi/equivalencias/<id>"; break;  default: $url = "<empresa>/contabilidad/importadorcfdi/equivalencias/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadImportadorcfdiEquivalencias(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/importadorcfdi/equivalencias"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_ContabilidadImportadorcfdiEquivalencias(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/importadorcfdi/equivalencias/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_ContabilidadImportadorcfdiEquivalencias(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/importadorcfdi/equivalencias/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_RutaLugares(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/ruta/lugares"; break; case "empresa-id": $url = "<empresa>/ruta/lugares/<id>"; break;  default: $url = "<empresa>/ruta/lugares/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_RutaLugares(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/lugares"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function DELETE_RutaLugares(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/ruta/lugares/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function PATCH_RutaLugares(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/lugares/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function GET_RutaCartasporte(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/ruta/cartasporte"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_RutaCartasporte(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/cartasporte"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_RutaCartasporteQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/cartasporte/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_RutaCartasporteTimbrar(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/ruta/cartasporte/<id>/timbrar"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_RutaAsignaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/ruta/asignaciones"; break; case "empresa-id": $url = "<empresa>/ruta/asignaciones/<id>"; break;  default: $url = "<empresa>/ruta/asignaciones/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_RutaAsignaciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/asignaciones"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_RutaAsignacionesQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/asignaciones/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function DELETE_RutaAsignaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/ruta/asignaciones/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function PATCH_RutaAsignaciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/asignaciones/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function PATCH_RutaAsignacionesTerminar(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/asignaciones/<id>/terminar"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function GET_RutaCobroformaspago(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/ruta/cobroformaspago"; break; case "empresa-id": $url = "<empresa>/ruta/cobroformaspago/<id>"; break;  default: $url = "<empresa>/ruta/cobroformaspago/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_RutaCobroformaspago(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/cobroformaspago"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function DELETE_RutaCobroformaspago(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/ruta/cobroformaspago/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function PATCH_RutaCobroformaspago(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/ruta/cobroformaspago/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function GET_InventarioCategorias(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/inventario/categorias"; break; case "empresa-id": $url = "<empresa>/inventario/categorias/<id>"; break;  default: $url = "<empresa>/inventario/categorias/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_InventarioCategorias(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/categorias"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_InventarioCategoriasQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/categorias/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_InventarioCategorias(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/categorias/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_InventarioCategorias(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/categorias/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_InventarioCategoriasProductos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/categorias/<id>/productos"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_InventarioMarcas(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/inventario/marcas"; break; case "empresa-id": $url = "<empresa>/inventario/marcas/<id>"; break;  default: $url = "<empresa>/inventario/marcas/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_InventarioMarcas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/marcas"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_InventarioMarcasQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/marcas/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_InventarioMarcas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/marcas/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_InventarioMarcas(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/marcas/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_InventarioPresentacionesdestacadas(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/inventario/presentacionesdestacadas"; break; case "empresa-id": $url = "<empresa>/inventario/presentacionesdestacadas/<id>"; break;  default: $url = "<empresa>/inventario/presentacionesdestacadas/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_InventarioPresentacionesdestacadas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/presentacionesdestacadas"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_InventarioPresentacionesdestacadas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/presentacionesdestacadas/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_InventarioPresentacionesdestacadas(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/presentacionesdestacadas/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function POST_InventarioExistenciaQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/existencia/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_InventarioProductos(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/inventario/productos"; break; case "empresa-id": $url = "<empresa>/inventario/productos/<id>"; break;  default: $url = "<empresa>/inventario/productos/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_InventarioProductos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/productos"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_InventarioProductosQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/productos/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_InventarioProductos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/productos/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_InventarioProductos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/productos/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_InventarioPresentaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/inventario/presentaciones"; break; case "empresa-id": $url = "<empresa>/inventario/presentaciones/<id>"; break;  default: $url = "<empresa>/inventario/presentaciones/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_InventarioPresentaciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/presentaciones"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_InventarioPresentacionesQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/presentaciones/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_InventarioPresentacionesCostos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/presentaciones/costos"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function PATCH_InventarioPresentaciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/presentaciones/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_InventarioPresentaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/presentaciones/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_InventarioRestriccionesventa(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/inventario/restriccionesventa"; break; case "empresa-id": $url = "<empresa>/inventario/restriccionesventa/<id>"; break;  default: $url = "<empresa>/inventario/restriccionesventa/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_InventarioRestriccionesventa(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/restriccionesventa"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_InventarioRestriccionesventa(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/restriccionesventa/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_InventarioRestriccionesventa(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/restriccionesventa/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_InventarioGrupospresentaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/inventario/grupospresentaciones"; break; case "empresa-id": $url = "<empresa>/inventario/grupospresentaciones/<id>"; break;  default: $url = "<empresa>/inventario/grupospresentaciones/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_InventarioGrupospresentaciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/grupospresentaciones"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_InventarioGrupospresentaciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/inventario/grupospresentaciones/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_InventarioGrupospresentaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/grupospresentaciones/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_InventarioGrupospresentacionesPresentaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/inventario/grupospresentaciones/<id>/presentaciones"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_Documentos(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/documentos"; break; case "empresa-id": $url = "<empresa>/documentos/<id>"; break; case "aleatorio-empresa-id": $url = "<empresa>/documentos/<id>/<aleatorio>"; break;  default: $url = "<empresa>/documentos/<id>/<aleatorio>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_Documentos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/documentos"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_DocumentosQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/documentos/query"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_DocumentosCobrosporaplicar(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/cobrosporaplicar"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_DocumentosObtenerparasurtir(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<folio>/obtenerparasurtir"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_DocumentosIndicarcantidadsurtida(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/documentos/<folio>/indicarcantidadsurtida"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_DocumentosRevisarcantidadsurtida(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/documentos/<folio>/revisarcantidadsurtida"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_Documentos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/documentos/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_Documentos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_DocumentosEstadoSAT(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<id>/estadoSAT"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function PATCH_Detallesentrada(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/detallesentrada/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function GET_DocumentosAleatorio(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<rfc>/<folio>/aleatorio"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_DocumentosDetalles(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<id>/<aleatorio>/detalles"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_DocumentosArchivos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<id>/<aleatorio>/archivos"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_TpvConfiguracion(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/tpv/configuracion"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_MovimientoTiposdocumento(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/movimiento/tiposdocumento"; break; case "empresa-id": $url = "<empresa>/movimiento/tiposdocumento/<id>"; break;  default: $url = "<empresa>/movimiento/tiposdocumento/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_MovimientoTiposdocumento(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/movimiento/tiposdocumento"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_MovimientoTiposdocumentoAsignados(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/movimiento/tiposdocumento/asignados"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function PATCH_MovimientoTiposdocumento(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/movimiento/tiposdocumento/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_MovimientoTiposdocumento(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/movimiento/tiposdocumento/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_MovimientoFolios(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/movimiento/folios"; break; case "empresa-id": $url = "<empresa>/movimiento/folios/<id>"; break;  default: $url = "<empresa>/movimiento/folios/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_MovimientoFolios(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/movimiento/folios"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_MovimientoFolios(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/movimiento/folios/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_MovimientoFolios(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/movimiento/folios/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_ContactoPedidosactivos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contacto/<id>/pedidosactivos"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_ContactoCobrosporaplicar(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contacto/<id>/cobrosporaplicar"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContactoExpediente(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contacto/expediente"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_HerramientasDocumentosconpresentacion(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/herramientas/documentosconpresentacion"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_HerramientasPrecios(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/herramientas/precios"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_HerramientasListadeprecios(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/herramientas/listadeprecios"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_EstadisticaPorsurtir(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/estadistica/porsurtir/<almacen_id>/<marca_id>"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_EstadisticaCobrospormarca(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/estadistica/cobrospormarca"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_EstadisticaUtilidadpordetalle(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/estadistica/utilidadpordetalle"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_EstadisticaUnidadesdesplazadas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/estadistica/unidadesdesplazadas"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_EstadisticaTiempocobranza(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/estadistica/tiempocobranza"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_RutaEstadisticaResumenporrutaasignadaactiva(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/ruta/estadistica/resumenporrutaasignadaactiva"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_EstadisticaDocumentossurtidos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/estadistica/documentossurtidos"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_AewebCamposadicionales(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/aeweb/camposadicionales"; break; case "empresa-id": $url = "<empresa>/aeweb/camposadicionales/<id>"; break;  default: $url = "<empresa>/aeweb/camposadicionales/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_AewebCamposadicionales(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/aeweb/camposadicionales"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_AewebCamposadicionales(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/aeweb/camposadicionales/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_AewebCamposadicionales(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/aeweb/camposadicionales/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_AewebDefinicionesexpedientecontacto(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/aeweb/definicionesexpedientecontacto"; break; case "empresa-id": $url = "<empresa>/aeweb/definicionesexpedientecontacto/<id>"; break;  default: $url = "<empresa>/aeweb/definicionesexpedientecontacto/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_AewebDefinicionesexpedientecontacto(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/aeweb/definicionesexpedientecontacto"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function PATCH_AewebDefinicionesexpedientecontacto(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/aeweb/definicionesexpedientecontacto/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function DELETE_AewebDefinicionesexpedientecontacto(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/aeweb/definicionesexpedientecontacto/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function GET_TpvClientesAutorizaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/tpv/clientes/<id>/autorizaciones"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_ContactosClientesAutorizaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contactos/clientes/<id>/autorizaciones"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContactosClientesAutorizaciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contactos/clientes/<id>/autorizaciones"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_Cpmx(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/cpmx/<cp>"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_TokenDesbloquear(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/token/<id>/desbloquear"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_ExtensionDocumentosporimprimir(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/extension/documentosporimprimir"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }

}
