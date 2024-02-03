<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Validator;
use App\Models\Redirect;
use App\Models\RedirectLog;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class RedirectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return 'all';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Redirect $redirect)
    {
        $rules = [
            'url_destiny' => 'required|url',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->url_destiny === $request->url()) {
            return response()->json(['errors' => ['url_destiny' => ['A URL de destino não pode apontar para a própria aplicação.']]], 422);
        }

        if (!str_starts_with($request->url_destiny, 'https')) {
            return response()->json(['errors' => ['url_destiny' => ['A URL de destino deve ser "https".']]], 422);
        }

        try {
            $response = Http::head($request->url_destiny);
            if ($response->status() !== 200) {
                return response()->json(['errors' => ['url_destiny' => ['A URL de destino não retornou um status 200.']]], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['errors' => ['url_destiny' => ['A URL de destino não é válida ou não está acessível.']]], 422);
        }

        $redirect->status = "Ativo";
        $redirect->url_destiny = $request->input('url_destiny');
        $redirect->last_access = $request->input('last_access');
        $redirect->save();

        return response()->json([
            'data' => $redirect,
            'message' => 'Redirect criado com sucesso'
        ], 201);

        // Ou redirecionar para uma página específica
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
            'last_acess' => $redirect->last_acess,
            'created_at' => $redirect->created_at,
            'updated_at' => $redirect->updated_at
        ];

        return response()->json($data, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Redirect $redirect)
    {
        $validate = $this->validateFields($request);
        if($validate){
            return $validate;
        }

        $redirect->url_destiny = $request->input('url_destiny');
        $redirect->status = $request->input('status');
        $redirect->save();

        // Retornar uma resposta adequada (você pode personalizar isso conforme necessário)
        return response()->json(['message' => 'Redirecionamento atualizado com sucesso']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Redirect $redirect)
    {

        if (!$redirect) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }

        $redirect->delete();

        return response()->json(['message' => 'Registro deletado com sucesso']);
    }

    public function getRequestData($request, $redirect)
    {
        $ipAddress = $request->ip();

        $userAgent = $request->header('User-Agent');

        $referer = $request->header('referer');

        $urlParts = parse_url($redirect->url_destiny);
        parse_str(isset($urlParts['query']), $queryParams);

        return [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'referer' => $referer,
            'query_params' => count($queryParams) ? json_encode($queryParams) : null,
        ];
    }

    public function validateFields($request){
        $rules = [
            'url_destiny' => 'required|url',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $message = response()->json(['errors' => $validator->errors()], 422);
        }

        return isset($message);
    }

    public function redirect(Redirect $redirect, Request $request){
        $data = $this->getRequestData($request, $redirect);
        return $data;

        try {
            RedirectLog::createLog($redirect->id, $data);
        } catch (\Exception $e) {
            return response()->json(['fail_log' => $e], 500);
        }

        $response = Http::get($redirect->url_destiny);

        return $response;
    }
}
