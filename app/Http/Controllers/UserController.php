<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
//Helper
use App\Helpers\JwtAuth;
use Illuminate\Http\Resources\Json\Resource;
//Storage para imagenes
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function pruebas(Request $request)
    {
        return 'Accion de pruebas de UserController';
    }

    public function register(Request $request)
    {

        //Recoger los datos del usuario por POST
        $json = $request->input('json', null);

        //Decodificar JSON y obtener un objeto
        $params = json_decode($json); //objeto

        $params_array = json_decode($json, true); //array

        //Validar si no estan vacios

        if (!empty($params) && !empty($params_array)) {

            //Limpiar datos para quitar estacios
            $params_array = array_map('trim', $params_array);


            //Validar los datos
            $validate = Validator::make($params_array, [
                'name'        =>    'required|alpha_num',
                'surname'     =>    'required|alpha_num',
                //Comprobar si el usuario ya existe
                'email'       =>    'email|required|unique:users',//unique:users ser refiere a campounic y la tabla a validar
                'password'    =>    'required'
            ]);

            //Validacion fallida
            if ($validate->fails()) {
                $data = array(
                    'status'      =>    'error',
                    'code'        =>    404,
                    'message'     =>    'Error al crear el usuario',
                    'errors'      =>    $validate->errors()
                );
            } else {
                //Validacion Correcta

                //Cifrar la contrasena
                 $pwd=hash('sha256',$params->password);
                 
                 

                 
                //Crear el usuario
                $user=new User();
                $user->name =$params_array['name'];
                $user->surname  =$params_array['surname'];
                $user->email=$params_array['email'];
                $user->password =$pwd;
                $user->role ='ROLE_USER';
                
                
                
                //Guardar el Usuario
                $user->save();
                
                
                //Obtener datos en formato JSON
                
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado correctamente',
                    'user'=>$user
                );
            }
            
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos o estan nulos'
            );
            
        }
        return response()->json($data, $data['code']);
    }
    public function login(Request $request)
    {
        
        //Cargar el servicio en el controlador
        $jwtAuth = new \JwtAuth();

        //Recibir los datos por POST
        $json=$request->input('json',null);
        $params=json_decode($json);

        $params_array=json_decode($json,true);

        //Validar los datos
        
        $validate = Validator::make($params_array, [
            'email'       =>    'email|required',
            'password'    =>    'required'
        ]);

        //Validacion fallida
        if ($validate->fails()) {
            $signup = array(
                'status'      =>    'error',
                'code'        =>    404,
                'message'     =>    'Error al identificar usuario',
                'errors'      =>    $validate->errors()
            );
        }else{
            //Cifrar la contrasena
            $pwd=hash('sha256',$params->password);

            //Devolver token o datos
            $signup=$jwtAuth->signup($params->email,$pwd);
            if(!empty($params->gettoken)){
                //Devolver el usuario reconocido
                $signup=$jwtAuth->signup($params->email,$pwd,true);
            }

        }
        
        
        //Retornar el usuario al que se le esta asignando el token
        return response()->json($signup,200);
    }

    public function update(Request $request){

        //Comprobar si el usuario esta verificado segun el token
        $token=$request->header('Authorization');
        $jwtAuth=new \JwtAuth();
        $checkToken=$jwtAuth->checkToken($token);
        
        if($checkToken){
            //Recoger los datos por post
            $json=$request->input('json',null);
            
            
            
            //Covierte en un string y en array
            $params_array=json_decode($json,true);


            if($checkToken && !empty($params_array)){

                
                //Sacar usuario identificado
                $user=$jwtAuth->checkToken($token,true);
    
                
    
    
    
                //Validar
                $validate=Validator::make($params_array,[
                    'name'        =>    'required|alpha',
                    'surname'     =>    'required|alpha',
                    //Comprobar si el usuario ya existe
                    'email'       =>    'email|required|unique:users'.$user->sub
                ]);
    
    
    
    
    
                //Quitar los campos que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['role']);
                unset($params_array['password']);
                unset($params_array['created_at']);
                unset($params_array['remember_token']);
    
                //Actualizar usuario
                $user_update=User::where('id',$user->sub)->update($params_array);
    
                //Devolver array con resultado
                $data=array(
                    'code'=>200,
                    'status'=>'success',
                    'message'=>$user,
                    'changes'=>$params_array
                );
            }
        }else{
            $data=array(
                'code'=>400,
                'status'=>'error',
                'message'=>'El usuario no esta identificado'
            );
            
        }
        
        return response()->json($data,$data['code']);
        
    }
    
    
    public function upload(Request $request){

        //Recoger los datos
        $image=$request->file('file0');

        //Validar la imagen
        $validate=Validator::make($request->all(),[
            'file0'=>'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Guardar imagen
        if(!$image || $validate->fails()){
            
            //Devolver resultado
            $data=array(
                'code'=>400,
                'status'=>'error',
                'message'=>'Eror al subir la imagen'
            );
        }else{
            $image_name=time().$image->getClientOriginalName();
            Storage::disk('users')->put($image_name,File::get($image));

            $data=array(
                'image'=>$image_name,
                'code'=>200,
                'status'=>'success'
            );
        }
        

        return response()->json($data,$data['code']);
    }
    
    
    public function getImage($filename){
        
        //Commprobar si existe
        $isset=Storage::disk('users')->exists($filename);
        
        if($isset){
            
            $file= Storage::disk('users')->get($filename);
            
            return new Response($file,200);
        }else{
            
            $data=array(
                'message'=>'La imagen no existe',
                'code'=>404,
                'status'=>'error'
            );
        }
        
        return response()->json($data,$data['code']);
    }

    public function detail_user($id){
        
        $user=User::find($id);

        if(is_object($user)){
            $data=array(
                'code'=>200,
                'status'=>'success',
                'user'=>$user
            );
        }else{
            $data=array(
                'code'=>404,
                'status'=>'error',
                'message'=>'El usuario no existe'
            );
        }

        return response()->json($data,$data['code']);
    }
}
