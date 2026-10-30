<?php

namespace Modules\Eventos\Services;

// Cobro con tarjeta por CROEM / Banco Nacional de Panamá (API de tokens, SOAP 1.1).
// El navegador tokeniza la tarjeta con el widget del banco; aquí solo llega el token y el CVV.
// Credenciales en .env: croem.apiKey, croem.accessCode, croem.merchantAccount, croem.terminal (y opcional croem.widgetUrl, croem.endpoint).
class CroemPasarela
{
    public const WIDGET_URL = 'https://gateway.merchantprocess.net/securecomponent/v2/UIComponent/CreditCard';
    public const ENDPOINT   = 'https://gateway.merchantprocess.net/TokenBnp/TokenWebService.asmx';

    public static function configurada(): bool
    {
        return (string) env('croem.apiKey') !== '' && (string) env('croem.accessCode') !== ''
            && (string) env('croem.merchantAccount') !== '' && (string) env('croem.terminal') !== '';
    }

    /** Datos públicos que necesita el widget en el navegador. */
    public static function paraNavegador(): array
    {
        return [
            'widget' => (string) (env('croem.widgetUrl') ?: self::WIDGET_URL),
            'apiKey' => (string) env('croem.apiKey'),
        ];
    }

    /** @return array{ok:bool,mensaje:string,transaccion?:string} */
    public function cobrar(string $token, string $cvv, int $centavos, string $seguimiento): array
    {
        if (! self::configurada()) {
            return ['ok' => false, 'mensaje' => 'El pago con tarjeta no está disponible por ahora. Elige otro medio de pago.'];
        }
        $x = static fn (string $v) => htmlspecialchars($v, ENT_XML1);
        $xml = '<?xml version="1.0" encoding="utf-8"?>'
            . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/"><soapenv:Header/><soapenv:Body><tem:Sale>'
            . '<tem:APIKey>' . $x((string) env('croem.apiKey')) . '</tem:APIKey>'
            . '<tem:accountToken>' . $x($token) . '</tem:accountToken>'
            . '<tem:accessCode>' . $x((string) env('croem.accessCode')) . '</tem:accessCode>'
            . '<tem:merchantAccountNumber>' . $x((string) env('croem.merchantAccount')) . '</tem:merchantAccountNumber>'
            . '<tem:terminalName>' . $x((string) env('croem.terminal')) . '</tem:terminalName>'
            . '<tem:clientTracking>' . $x($seguimiento) . '</tem:clientTracking>'
            . '<tem:amount>' . number_format($centavos / 100, 2, '.', '') . '</tem:amount>'
            . '<tem:currencyCode>840</tem:currencyCode>'
            . '<tem:cvv>' . $x($cvv) . '</tem:cvv>'
            . '</tem:Sale></soapenv:Body></soapenv:Envelope>';

        $ch = curl_init((string) (env('croem.endpoint') ?: self::ENDPOINT));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $xml, CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER     => ['Content-Type: text/xml; charset=utf-8', 'SOAPAction: "http://tempuri.org/Sale"'],
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err !== '' || $http !== 200 || ! is_string($resp)) {
            log_message('error', '[CROEM] fallo de comunicación: HTTP ' . $http . ' ' . $err);

            return ['ok' => false, 'mensaje' => 'No pudimos comunicarnos con el banco. Intenta de nuevo en unos minutos.'];
        }

        $limpio = preg_replace('/\s+xmlns[^=]*="[^"]*"/', '', str_ireplace(['soapenv:', 'soap:', 'soap12:', 'tem:'], '', $resp));
        $obj    = @simplexml_load_string($limpio);
        $res    = $obj ? $obj->Body->SaleResponse->SaleResult : null;
        if (! $res) {
            log_message('error', '[CROEM] respuesta no interpretable');

            return ['ok' => false, 'mensaje' => 'No pudimos procesar la respuesta del banco. Intenta de nuevo.'];
        }

        $codigo = (string) $res->Code;
        if ($codigo === '00' || $codigo === '11') {
            return ['ok' => true, 'mensaje' => 'Pago aprobado', 'transaccion' => (string) $res->TransactionId];
        }
        log_message('warning', '[CROEM] pago rechazado ' . $codigo . ' ' . (string) $res->Description);

        return ['ok' => false, 'mensaje' => 'Tarjeta rechazada: ' . (string) $res->Description . ' (código ' . $codigo . ').'];
    }
}
