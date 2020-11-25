<?php

namespace App\Http\Controllers;

//Modelo
use App\Post;

use Illuminate\Http\Request;
//Para cargar respuestas
use Illuminate\Http\Response;

//Conseguir el usuario identificado
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Storage;
//Validar los datos ingresados
use Illuminate\Support\Facades\Validator;

//Imagen
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    public function __construct()
    {
        //Invocar el middleware para verificar el token del usuario
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getImage',
            'getPostsByCategory',
            'getPostsByUser'
        ]]);
    }

    public function index()
    {
        //Todo y adjunto con los datso a que categoria pertenece
        $posts = Post::all()->load('category');
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function show($id)
    {
        //Todo y adjunto con los datso a que categoria y usuario al que pertenece
        $post = Post::find($id)->load('category')
            ->load('user');

        if (is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La entrada no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }


    public function store(Request $request)
    {
        //Recoger datos
        $json = $request->input('json', null);

        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //Datos por defecto
        $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos incorrectos'
        ];

        if (!empty($params_array)) {

            //Conseguir el usuario identificado segun la sesion
            $user = $this->getIdentity($request);

            //Validar los datos
            $validate = Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'image' => 'required',
                'category_id' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Faltan datos'
                ];
            } else {
                //Guardar el POST
                $post = new Post();
                //->sub mas informacion en Helpers
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                //Otra alternativa 
                /* 
                $post->category_id=$params_array['category_id'];
                $post->title=$params_array['title'];
                $post->content=$params_array['content'];
                $post->image=$params_array['image']; 
                */

                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }


            //Devolver la respuesta
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se han enviado los datos correctamente'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        //Recoger datos por por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            //Validar
            $validate = Validator::make($params_array, [
                'category_id' => 'required',
                'title' => 'required',
                'content' => 'required'
            ]);

            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($validate->errors(), 400);
            }

            //Eliminar lo que no se quiere actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            //Actualizarel registro y conseguir la informacion con get

            //Conseguir el usuario identificado
            $user = $this->getIdentity($request);

            //Comprobar y conseguir si existe el registro y el usuario
            $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

            if (!empty($post) && is_object($post)) {

                $post->update($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'changes' => $params_array,
                    'post' => $post
                ];
            } else {

                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No tiene permiso para actualizar el post'
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Datos incorrectos'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request)
    {
        //Conseguir el usuario modificado

        //Conseguir el usuario identificado para poder eliminar segun el autor
        //Conseguir el usuario identificado
        $user = $this->getIdentity($request);

        //Comprobar y conseguir si existe el registro y el usuario
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

        //Borrar

        if (!empty($post)) {

            $post->delete();

            //Devolver resultados
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        } else {
            //Devolver resultados
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Eror al eliminar el post, no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    //Funcion para reconocer al usuario que esta logueado 
    private function getIdentity(Request $request)
    {
        //Conseguir el usuario identificado
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request)
    {
        //Recoger la imagen de la peticion
        $image = $request->file('file0');

        //Validar
        $validate = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,png,gif'
        ]);


        //Guardar la imagen en un disco (images)
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();

            Storage::disk('images')->put($image_name, File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }


        //Devolver datos
        return response()->json($data, $data['code']);
    }


    public function getImage($filename)
    {
        //Comprobar si existe el fichero
        $isset = Storage::disk('images')->exists($filename);

        if ($isset) {
            //Conseguir la imagen
            $file = Storage::disk('images')->get($filename);

            //Devolver la imagen
            return new Response($file, 200);
        } else {
            //Mostrar error
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];
        }

        //Devolver resultado general
        return response()->json($data, $data['code']);
    }


    public function getPostsByCategory($id)
    {
        $posts = Post::where('category_id', $id)->get();



        if (sizeof($posts) == 0) {
            return response()->json([
                'status' => 'success',
                'cuantity' => false,
                'posts' => $posts
            ], 200);
        } else {
            return response()->json([
                'status' => 'success',
                'cuantity' => true,
                'posts' => $posts
            ], 200);
        }
    }


    public function getPostsByUser($id)
    {
        $posts = Post::where('user_id', $id)->get();


        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }
}
