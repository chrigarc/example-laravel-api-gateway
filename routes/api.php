<?php

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Broadcast::routes(['middleware' => ['auth:web', 'auth:sanctum']]);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new JsonResponse(['data' => $request->user()]);
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
    Auth::login($user, true);
    $user  = Auth::user();
    $tokenName = strtolower(Str::slug(config('app.name'), '-') . '-token');
    $token = $user->createToken($tokenName, ['access:broadcast'])->plainTextToken;
    $user->token = $token;
    return new JsonResponse(['user' => $user, 'session' => session()->get('a')]);
});

Route::get('csrf-token', function () {
    return csrf_token();
});



Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $user = auth()->user();
    auth('web')->logout();
    try {
        session()->flush();
        $user->tokens()->delete();
    } catch (\Exception $exception) {
        info($exception->getMessage(), [
            'logout' => $user->id
        ]);
    }
    return new \Illuminate\Http\JsonResponse([
        'message' => 'ok'
    ]);
});


Route::middleware('auth:sanctum')->get('/test1', function(Request  $request) {
    $guzzle = new Client();
    $response = $guzzle->get(env('MICRO_S1'));
    return json_decode((string)$response->getBody(), true);
});

Route::middleware('auth:sanctum')->get('/test2', function(Request  $request) {
    $guzzle = new Client();
    $response = $guzzle->get(env('MICRO_S2'));
    return json_decode((string)$response->getBody(), true);
});


Route::get('/test2', function (Request $request) {
    return new \Illuminate\Http\JsonResponse([
        'message' => 'Hola soy laravel en una carpeta. '. now()->format('Y-m-d H:i:s')
    ], 200,
        ['Access-Control-Allow-Origin' => '*']);
});


Route::get('/test1', function (Request $request) {
    return new \Illuminate\Http\JsonResponse([
        'message' => 'Hola soy laravel en una carpeta. '. now()->format('Y-m-d H:i:s')
    ]);
});
