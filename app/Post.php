<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table='posts';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title','content','category_id', 'image',
    ];

    //Relacion de uno a muchos pero inversa (muchos a uno) varios post tienen un usuario
    public function user(){
        return $this->belongsTo('App\User','user_id');
    }
    
    //Relacion de uno a muchos pero inversa (muchos a uno) varios post tienen una categoria
    public function category(){
        return $this->belongsTo('App\Category','category_id');
    }

}
