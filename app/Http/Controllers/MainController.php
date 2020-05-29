<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function render(Request $request){
        $require_file = '';
        if($request->has('file')){
            $require_file = 'required';
        }
        $this->validate($request, [
            'data' => 'required',
            'file' => '',
            'file.name'=>$require_file,
            'file.data'=>$require_file,
        ]);
       
        $file_tmp = $this->saveFile($request);
        $document = new Document($file_tmp);
        
        // Table with a spanned cell
        $document->setData($request->data);

        $document->generate();
        $file = $document->export();
        unlink($document->getPathFileTmp());

        if($request->has('file')){
            unlink($file_tmp);
        }
        
        return response()->download($file);
    }
    private function saveFile(Request $request){
        $file = storage_path('app/layouts/').'LISTA_DE_RAYA.docx';
        if($request->has('file') && isset($request->file['data'])){
            if (!file_exists(storage_path('app/layouts/upload'))) {
                mkdir(storage_path('app/layouts/upload'), 0777, true);
            }
            $file = storage_path('app/layouts/upload').'/'.time().'.docx';
            file_put_contents($file, base64_decode($request->file['data']));
            // if($request->file['name']){
            //     $file = storage_path('app/layouts/upload').'/'.$request->file['name'].'.docx';
            //     file_put_contents($file, base64_decode($request->file['data']));
            // }else{
            //     $file = storage_path('app/layouts/').'/'.time().'.docx';
            //     file_put_contents($file, base64_decode($request->file['data']));
            // }
        }
        return $file;
    }
}
