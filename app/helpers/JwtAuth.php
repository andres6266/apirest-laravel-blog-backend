<?php

namespace App\Helpers;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{

    public $key;

    public function __construct()
    {
        $this->key='clave_super_secreta';
    }
    public function signup($email,$password,$getToken=null){

        //Buscar si existe el usuario con credenciales
        $user=User::where([
            'email'=>$email,
            'password'=>$password,
        ])->first();

        //Comproabar si son correctas
        $signup=false;
        if(is_object($user)){
            $signup=true;
        }
        //Generar el token con los datos del usuario identificado
        if($signup){
            $token=array(
                //Informa que es el usuario actual
                'sub'=>$user->id,
                'email'=>$user->email,
                'name'=>$user->name,
                'surname'=>$user->surname,
                'description'=>$user->description,
                'image'=>$user->image,
                //Cuado se creo el token
                'iat'=>time(),
                //Cuando expira el token en este caso en una semana
                'exp'=>time()+(7*24*60*60)
            );

            $jwt=JWT::encode($token,$this->key,'HS256');
            $decoded=JWT::decode($jwt,$this->key,['HS256']);


            //Devolver los datos decodificados o el token en funcion de un parametro

            if(is_null($getToken)){
                $data=$jwt;
            }else{
                $data= $decoded;
            }


        }else{
            $data=array(
                'status'=>'error',
                'message'=>'Login incorrecto'
            );
        }

        return $data;
    }





    

    public function checkToken($jwt,$getIdentity=false){
        $auth=false;


        try {

            $jwt=str_replace('"','',$jwt);
            $decoded=JWT::decode($jwt,$this->key,['HS256']);

        } catch (\UnexpectedValueException $th) {
            $auth=false;
        } catch (\DomainException $e){
            $auth=false;

        }

        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth=true;

        }else{
            $auth=false;
        }

        if($getIdentity){
            return $decoded;
        }

        return $auth;
    }
}
