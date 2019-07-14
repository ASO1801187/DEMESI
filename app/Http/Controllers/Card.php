<?php

namespace App\Http\Controllers;

use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Scalar\String_;
use function PHPSTORM_META\type;

class Card extends Controller
{
    function template_get(Request $request){
        try {
            $result = DB::select('select tenple_id,temple_sample from tenple_flag where tenple_flag=1');
            return json_encode($result);
        } catch (\PDOException $e) {
            return $e;
        }
    }

    function template_Coordinate(Request $request){
        $tenple_id = $request->tenple_id;

        try {
            $result = DB::select('select tenple_data,tenple_id  from tenple_information where tenple_id='.$tenple_id);
            return json_encode($result);

        } catch (\PDOException $e) {
            return $e;

        }
    }


    function newcard(Request $request, $id){
        $result = ["result" => 0];

        if (!empty($_FILES)) {
            $tenple_id = $request->tenple_id;
            try {
                $ans = DB::select('select tenple_data, tenple_x, tenple_y from tenple_information where tenple_id='.$tenple_id);

            } catch (\PDOException $e) {
                return $e;

            }
            $id = $request->id;
            $time = date("YmdHis");
            $info = $_FILES['img']['type'];
            $info = explode('/',$info);
            $a = base_path().'/public/img/'.$time.".".$info[1]; //$time;

            if (move_uploaded_file($_FILES['img']['tmp_name'], $a)) {
                $img = Image::make($a);
                $img->fit(1254, 758);
                $img->save($a);

                foreach ($ans as $value) {
                    $value_ans = $value->tenple_data;
                    $text =$request->$value_ans;
                    $x = $value->tenple_x;
                    $y = $value->tenple_y;

                    $img->text($text, $x, $y, function ($font) {
                        $font->file(base_path() . '/public/font/APJapanesefont.ttf');
                        $font->size(40);
                        $font->color('#0000ff');

                    });

                }

                $path = base_path() ."/public/meisi/".$time.".png";

                $img->save($path);
                $path = "/public/meisi/".$time.".png";

                $param = [
                    'id' => $id,
                    'path' => $path,
                    'tenple_id' => $tenple_id

                ];

                try {
                    DB::beginTransaction();
                    DB::insert('insert into meisi(user_id,path,tenple_id)values(:id,:path,:tenple_id)',$param);
                    $meisi_id = DB::select('select meisi_id from meisi where path="'.$path.'"');
                    $text = 'http://localhost:8081/untitled/public/card/infomation/' . (String)$meisi_id[0]->meisi_id;
                    $qrcode = new QRCode();
                    $position = 'bottom-right';
                    $img->insert($qrcode->render($text), $position, 10, 20);
                    $img->save();
                    DB::commit();

                } catch (\PDOException $e) {
                    DB::rollBack();
                    return $e;

                }

                try{
                    DB::beginTransaction();
                    foreach ($ans as $value) {
                        $data_name = $value->tenple_data;
                        $insert_data =$request->$value_ans;
                        $param = [
                            'id' => $meisi_id[0]->meisi_id,
                            'data_name' => $data_name,
                            'insert_data' => $insert_data

                        ];
                        DB::insert('insert into meisi_data(meisi_id,data_name,value)value(:id,:data_name,:insert_data)',$param);

                    }
                    DB::commit();
                    $result["result"] = 1;

                }catch (\PDOException $e){
                    DB::rollBack();
                    return $e;
                }

            }else{
                $result = 3;

            }
        }else{
            $result = 4;

        }
        return json_encode($result);
    }

    function AllCardTableReturn(Request $request, $id){
        try {
            $result = DB::select('select * from meisi where user_id ='.$id);
            return json_encode($result);

        } catch (\PDOException $e) {
            return $e;

        }
    }

    function CardInformationGet(Request $request, $meisiid){
        try {
            $result = DB::select('select * from meisi_data where meisi_id ='.$meisiid);
            return json_encode($result);

        } catch (\PDOException $e) {
            return $e;

        }
    }
}