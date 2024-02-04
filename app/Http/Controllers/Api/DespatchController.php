<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\SunatService;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;

class DespatchController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
        ->where('ruc', $data['company']['ruc'])
        ->firstOrFail();

        $sunat = new SunatService();
        $despatch = $sunat->getDespatch($data);

        $api = $sunat->getSeeApi($company);
        $result = $api->send($despatch);

        $ticket = $result->getTicket();
        $result = $api->getStatus($ticket);

        $response['xml'] = $api->getLastXml();
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);
        $response['sunatResponse'] = $sunat->sunatResponse($result);

        return response()->json($response, 200);
    }

    public function xml(Request $request)
    {
        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
        ->where('ruc', $data['company']['ruc'])
        ->firstOrFail();

        $sunat = new SunatService();

        $see = $sunat->getSee($company);
        $despatch = $sunat->getDespatch($data);

        $response['xml'] = $see->getXmlSigned($despatch);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        return response()->json($response, 200);
    }

    public function pdf(Request $request)
    {
        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
        ->where('ruc', $data['company']['ruc'])
        ->firstOrFail();

        $sunat = new SunatService();
        
        $despatch = $sunat->getDespatch($data);
        $sunat->generatePdfReport($despatch);
        return $sunat->getHtmlReport($despatch);
    }
}
