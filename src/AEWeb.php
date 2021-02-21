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
    private $host = "aeweb.svc-sitec.com";

    private $guzzle = NULL;
    private $openapi = NULL;

    public const NO_IMPLEMENTADO = 0;
    public const ENDPOINT_ERROR_GENERAR = 950;
    public const RESPUESTA_NO_DISPONIBLE = 951;

    function __construct(string $empresa, string $token)
    {
        $this->empresa = $empresa;
        $this->token = $token;

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


    private function API_CALL(string $metodo, string $url, ?array $variables=NULL, ?array $querystrings=NULL, ?array $body=NULL)
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
            $opciones["auth"] = array("app", $this->token);

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
	public function GET_SesionPerfil(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/sesion/perfil"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_SesionVerificarCredenciales(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/verificar-credenciales"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_SesionLogin(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/login"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_SesionLogout(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/logout"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_SesionTfaGenerar(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/sesion/tfa-generar"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_SesionTfaHabilitar(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/tfa-habilitar"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_SesionDesbloquear(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/sesion/desbloquear"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_Cpmx(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/cpmx/<cp>"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_CfdiUsocfdis(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/cfdi/usocfdis"; break; case "empresa-id": $url = "<empresa>/cfdi/usocfdis/<id>"; break;  default: $url = "<empresa>/cfdi/usocfdis/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_CfdiUsocfdis(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/cfdi/usocfdis"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function DELETE_CfdiUsocfdis(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/cfdi/usocfdis/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function PATCH_CfdiUsocfdis(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/cfdi/usocfdis/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function GET_ContabilidadCuentas(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/contabilidad/cuentas"; break; case "empresa-id": $url = "<empresa>/contabilidad/cuentas/<id>"; break;  default: $url = "<empresa>/contabilidad/cuentas/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_ContabilidadCuentas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/cuentas"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function DELETE_ContabilidadCuentas(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/contabilidad/cuentas/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function PATCH_ContabilidadCuentas(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/contabilidad/cuentas/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function GET_Documentos(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "empresa": $url = "<empresa>/documentos"; break; case "empresa-id": $url = "<empresa>/documentos/<id>"; break; case "aleatorio-empresa-id": $url = "<empresa>/documentos/<id>/<aleatorio>"; break;  default: $url = "<empresa>/documentos/<id>/<aleatorio>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_Documentos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/documentos"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_DocumentosQuery(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL)
    {
        $url = "<empresa>/documentos/query";

        return $this->API_CALL("POST", $url, $variables, $querystrings, $body);
    }
	public function DELETE_Documentos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function PATCH_Documentos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "<empresa>/documentos/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function GET_DocumentosAleatorio(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<rfc>/<folio>/aleatorio"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_DocumentosDetalles(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<id>/<aleatorio>/detalles"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_DocumentosArchivos(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/documentos/<id>/<aleatorio>/archivos"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_EstadisticaPorsurtir(?array $variables=NULL,?array $querystrings=NULL){ $url = "<empresa>/estadistica/porsurtir/<almacen_id>/<marca_id>"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }

}
