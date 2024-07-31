<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entity;
use App\Models\Category;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function dataCategory($category)
    {
        try {
            $data = Entity::with('category')->where('category_id', $category)->get();

            $responseData = [
                'success' => true,
                'data' => $data->map(function ($item) {
                    return [
                        'api' => $item->api,
                        'description' => $item->description,
                        'link' => $item->link,
                        'category' => [
                            'id' => $item->category->id,
                            'category' => $item->category->category,
                        ],
                    ];
                }),
            ];

            return response()->json($responseData, 200);

        } catch (\Throwable $th) {
            return response()->json(['messages' => 'Exits error'], 500);
        }
    }

    public function requestExternalApi()
    {
        try {
            DB::beginTransaction();

            // TO DO la url proporcionada a veces tiene problemas al retornar la informacion y retorna un HTML por defecto
            $url = 'https://web.archive.org/web/20240403172734/https://api.publicapis.org/entries';
            $client = new Client();
            $response = $client->request('GET', $url);
            $responseTotal = json_decode($response->getBody());
            $validCategories = ['Animals', 'Security'];

            foreach($responseTotal->entries as $value){
                if(in_array($value->Category, $validCategories)){
                    $data = [
                        'api' => $value->API,
                        'description' => $value->Description,
                        'link' => $value->Link,
                        'category_id' => ($value->Category == "Animals" ? 1 : 2),
                    ];

                    Entity::create($data);
                }
            }

            DB::commit();
            return response()->json(['messages' => 'Records successfully inserted'], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['messages' => 'Exits error'], 500);
        }
    }
}
