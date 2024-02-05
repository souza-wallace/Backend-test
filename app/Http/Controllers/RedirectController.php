<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

use Validator;
use Carbon\Carbon;

use App\Models\Redirect;
use App\Models\RedirectLog;

use Vinkla\Hashids\Facades\Hashids;


class RedirectController extends Controller
{
    public function index(Redirect $redirect): object
    {
        return response()->json([
            "data" => $redirect->all()
        ], 200);
    }

    public static function store(Request $request, Redirect $redirect)
    {
        $rules = [
            'url_destiny' => 'required|url',
        ];

        $validateFields = self::validateFields($request);
        if(count($validateFields)){ return $validateFields; }

        $validateUrl = isset($request->url_destiny) ? self::validateUrl($request->url_destiny) : false;
        if(count($validateUrl)){ return response()->json($validateUrl, 500); }

        $redirect->status = "ativo";
        $redirect->url_destiny = $request->input('url_destiny');
        $redirect->last_access = Carbon::now();
        $redirect->save();

        return response()->json([
            'data' => $redirect,
            'message' => 'Redirect created with sucess'
        ], 201);
    }

    public function show(Redirect $redirect)
    {
        if(!$redirect){
            return response()->json([
                'message' => ['error' => "not found"],
            ], 500);
        }

        $data = [
            'code' => $redirect->code,
            'status' => $redirect->status,
            'url_destiny' => $redirect->url_destiny,
            'last_access' => $redirect->last_access,
            'created_at' => $redirect->created_at,
            'updated_at' => $redirect->updated_at
        ];

        return response()->json($data, 200);
    }

    public function update(Request $request, Redirect $redirect)
    {
        $validateFields = self::validateFields($request);
        if(count($validateFields)){ return $validateFields; }

        $validateUrl = $this->validateUrl($request);
        if($validateUrl){ return $validateUrl; }

        $redirect->url_destiny = $request->input('url_destiny');
        $redirect->status = $request->input('status');
        $redirect->save();

        return response()->json(['message' => 'Redirect updated with sucess']);
    }

    public function destroy(Redirect $redirect): object
    {

        if (!$redirect) {
            return response()->json(['message' => 'not found'], 404);
        }

        $redirect->delete();

        return response()->json(['message' => 'Registro deleted with sucess']);
    }

    public function getRequestData($request, $redirect): array
    {
        $ipAddress = $request->ip();

        $userAgent = $request->header('User-Agent');

        $referer = $request->header('referer');

        $queryParamsRequest = $request->query();

        return [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'referer' => $referer,
            'query_params_request' => count($queryParamsRequest) ? $queryParamsRequest : null,
        ];
    }

    public function redirect(Redirect $redirect, Request $request)
    {
        $data = $this->getRequestData($request, $redirect);
        $queryParamsRequest = $data['query_params_request'];

        $urlDestiny = self::generateRedirectUrl($queryParamsRequest, $redirect->url_destiny);
        $code = Hashids::encode($redirect->id, 10);

        // return $code;
        try {
            return RedirectLog::createLog($redirect->id, $code, $data);
        } catch (\Exception $e) {
            \Log::error('Erro ao salvar Log:' . $e->getMessage());
        }
    
        $response = Http::get($urlDestiny);
    
        return $response;
    }

    public static function generateRedirectUrl($queryParamsRequest, $url_destiny){
        $urlQueryParams = [];
    
        parse_str(parse_url($url_destiny, PHP_URL_QUERY), $urlQueryParams);
    
        if($queryParamsRequest){
            $queryParamsRequest = array_filter($queryParamsRequest, function ($value) {
                return isset($value);
            });
        }

        $mergedQueryParams = array_merge($queryParamsRequest ?? [], $urlQueryParams);
        
        $urlWithoutQuery = strtok($url_destiny, '?');
        $urlDestiny = $urlWithoutQuery . '?' . http_build_query($mergedQueryParams);

        return $urlDestiny;
    }

    public function stats($code){
        $logs = RedirectLog::where('redirect_code', $code)->get();

        if (!$logs) {
            return response()->json(['error' => 'Redirect Log not found'], 404);
        }
    
        $totalAccesses = $logs->count();
        $uniqueIPs = $logs->pluck('ip')->unique()->count();
    
        $topReferrers = $logs->groupBy('referer')->sortByDesc(function ($referer) {
            return $referer->count();
        })->take(5)->keys()->toArray();
    
        $last10Days = $logs->groupBy('created_at')->take(10)->map(function ($dayLogs, $date) {
            $total = $dayLogs->count();
            $unique = $dayLogs->pluck('ip')->unique()->count();
            return ['date' => $date, 'total' => $total, 'unique' => $unique];
        })->values();
    
        $stats = [
            'totalAccesses' => $totalAccesses,
            'uniqueIPs' => $uniqueIPs,
            'topReferrers' => $topReferrers,
            'last10Days' => $last10Days,
        ];
    
        return $stats;
    }

    public static function validateUrl($url) {
        $errors = [];
    
        $currentUrl = URL::current();
    
        if ($currentUrl == $url) {
            $errors[] = 'A URL de destino não pode apontar para a própria aplicação.';
        }
    
        $urlComponents = parse_url($url);
    
        if (!$urlComponents || !isset($urlComponents['scheme']) || !isset($urlComponents['host'])) {
            $errors[] = 'A URL de destino é inválida.';
        } else {
            if (!checkdnsrr($urlComponents['host'])) {
                $errors[] = 'O DNS da URL de destino não é válido.';
            }
    
            if ($urlComponents['scheme'] !== 'https') {
                $errors[] = 'A URL de destino deve ser "https".';
            }

            $response = Http::head($url);
            if ($response->status() !== 200) {
                $errors[] = 'A URL de destino não retornou um status 200.';
            }
        }

        if (isset($urlComponents['query'])) {
            parse_str($urlComponents['query'], $queryParams);
            if (isset($queryParams[""]) || in_array("", array_values($queryParams))) {
                $errors[] = 'A URL de destino não pode ter parâmetros de consulta com chaves vazias.';
            }
        }
    
        return $errors;
    }

    public static function validateFields($request): array
    {
        $rules = [
            'url_destiny' => 'required|url',
        ];

        $errors = [];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors[] = $validator->errors();
        }
        return $errors;
    }
    
}
