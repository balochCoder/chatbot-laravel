<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/', function (\Illuminate\Http\Request $request) {

    $client = OpenAI::client(env('OPEN_AI_SECRET'));
    $response = $client->completions()->create([
        'model' => 'text-davinci-003',
        'prompt' => $request->prompt,
        'temperature' => 0,
        'max_tokens' => 3000,
        'top_p' => 1,
        'frequency_penalty' => 0.5,
        'presence_penalty' => 0
    ]);


    return response()->json([
        'bot' => $response['choices'][0]['text']
    ]);
})->name('check');
