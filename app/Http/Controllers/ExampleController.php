<?php

namespace App\Http\Controllers;
use iio\libmergepdf\Merger;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function merge(){
        
        // Ruta del directorio donde están los archivos
        $path  = storage_path('app/layouts/upload/'); 

        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..','.DS_Store')); 

        $merger = new Merger;
        $merger->addIterator($files);
        $createdPdf = $merger->merge();
        file_put_contents('ejemplo.pdf', $createdPdf);
    }
}
