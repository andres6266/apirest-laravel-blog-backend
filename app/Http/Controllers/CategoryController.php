<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//Respuesta
use Illuminate\Http\Response;
//Modelo
use App\Category;

//Validador
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    //Invocar el middleware para verificar el token dek usuario
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index' , 'show']]);
    }


    public function pruebas(Request $request)
    {
        return 'Accion de pruebas de CategoryController';
    }

    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'categories' => $category
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoria no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }


    public function store(Request $request)
    {
        //Recoger datos
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            //Validar
            $validate = Validator::make($params_array, [
                'name' => 'required'
            ]);




            //Guardar

            if ($validate->fails()) {
                $data = [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Error al guardar'
                ];
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No se ha enviado ningun dato'
            ];
        }

        //Devolver resultado

        return response()->json($data, $data['code']);
    }
    
    
    public function update($id, Request $request){
        //Recoger parametros por psot
        
        $json=$request->input('json',null);
        $params_array=json_decode($json,true);
        
        if(!empty($params_array)){
            
            
            //Validar
            $validate=Validator::make($params_array,[
                'name'=>'required'
            ]);
            
            //Quitar lo que no se quiere actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            
            //Actualizar el registro(categoria)
            $category=Category::where('id',$id)->update($params_array);

            
            //Devolver los datos
            $data=[
                'code'=>200,
                'status'=>'success',
                'category'=>$params_array
            ];
        }else{
            $data=[
                'code'=>404,
                'status'=>'error',
                'message'=>'No se ha enviado ningun dato'
            ];
        }
        
        return response()->json($data, $data['code']);

    }
}
