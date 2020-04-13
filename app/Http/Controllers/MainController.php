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
        $file = storage_path('app/layouts/').'FORMATO - NOMINA TACNA.docx';
        if($request->has('file') && isset($request->file['data'])){
            if($request->file['name']){
                if (!file_exists(storage_path('app/layouts/upload'))) {
                    mkdir(storage_path('app/layouts/upload'), 0777, true);
                }
                $file = storage_path('app/layouts/upload').'/'.$request->file['name'].'.docx';
                file_put_contents($file, base64_decode($request->file['data']));
            }else{
                $file = storage_path('app/layouts/').'/'.time().'.docx';
                file_put_contents($file, base64_decode($request->file['data']));
            }
        }
        $document = new Document($file);

        // Table with a spanned cell
        $document->setData($request->data);

        $document->generate();
        $file = $document->export();
        unlink($document->getPathFileTmp());
        return response()->download($file);
    }
}