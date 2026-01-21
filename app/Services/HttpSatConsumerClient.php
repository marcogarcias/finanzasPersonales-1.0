<?php

namespace App\Services;

use PhpCfdi\SatEstadoCfdi\Contracts\ConsumerClientInterface;
use PhpCfdi\SatEstadoCfdi\Contracts\ConsumerClientResponseInterface;
use PhpCfdi\SatEstadoCfdi\Utils\ConsumerClientResponse;
use Illuminate\Support\Facades\Http;

class HttpSatConsumerClient implements ConsumerClientInterface
{
    public function consume(string $uri, string $expression): ConsumerClientResponseInterface
    {
        $soapAction = 'http://tempuri.org/IConsultaCFDIService/Consulta';
        
        $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
   <soapenv:Header/>
   <soapenv:Body>
      <tem:Consulta>
         <tem:expresionImpresa><![CDATA[{$expression}]]></tem:expresionImpresa>
      </tem:Consulta>
   </soapenv:Body>
</soapenv:Envelope>
XML;

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => $soapAction,
        ])->send('POST', $uri, [
            'body' => $xml,
        ]);

        $clientResponse = new ConsumerClientResponse();
        
        if ($response->successful()) {
            $body = $response->body();
            
            // Simple parsing of the response
            // The response looks like: <ConsultaResult><a:CodigoEstatus>...</a:CodigoEstatus><a:Estado>Vigente</a:Estado>...</ConsultaResult>
            
            $tags = ['CodigoEstatus', 'Estado', 'EsCancelable', 'EstatusCancelacion'];
            foreach ($tags as $tag) {
                if (preg_match("/<[a-z0-9:]*{$tag}>(.*?)<\/[a-z0-9:]*{$tag}>/", $body, $matches)) {
                    $clientResponse->set($tag, $matches[1]);
                }
            }
        } else {
            \Log::error("SAT Estatus Error: " . $response->status() . " - " . $response->body());
        }

        return $clientResponse;
    }
}
