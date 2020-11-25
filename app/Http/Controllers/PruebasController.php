<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//Importar los modelos para consultas de prueba
use App\Post;
use App\Category;



class PruebasController extends Controller
{
    public function testOrm(){
        
        /* Seleccionar todos los datos select *  */
        $posts=Post::all();

        /* foreach ($posts as $post) {
           
            echo '<h2>'.$post->title.'</h2><br>';
            echo '<span>'.$post->user->name.' | '.$post->category->name.'</span><br>';
            echo '<h4>'.$post->content.'</h4><br><hr>';
            
        } */
        
        $categories=Category::all();
        
        foreach ($categories as $category) {
            echo '<br><h2>'.$category->name.'</h2><br>';
            
            //Sacar todos los post de la categoria segun su pertenencia
            foreach($category->posts as $post){
                echo '<h3>'.$post->title.'</h3><br>';
                echo '<span>'.$post->user->name.' | '.$post->category->name.'</span><br>';
                echo '<h4>'.$post->content.'</h4><br>';
                

            }

            echo '<hr>';
            
        }


        die();
    }
}
