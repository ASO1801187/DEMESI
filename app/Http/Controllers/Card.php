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
        $flag = $request->flag;
        if($flag == 1){ //１は個人
            try {
                $result = DB::select('select template_id,template_sample from template_flag where template_flag=1');
                return json_encode($result);

            } catch (\PDOException $e) {
                return json_encode($e);

            }
        }else{
            try {
                $result = DB::select('select template_id,template_sample from template_flag where template_flag=2');
                return json_encode($result);

            } catch (\PDOException $e) {
                return json_encode($e);

            }
        }

    }

    function template_Coordinate(Request $request){
        $tenple_id = $request->template_id;
        try {
            $result = DB::select('select template_data from template_information where template_id="' .$tenple_id.'"');
            return json_encode($result);

        } catch (\PDOException $e) {
            return json_encode($e);

        }
    }


    function newcard(Request $request, $user_id){
        $result = ["result" => 0];
//        if (!empty($_FILES)) {
            $template_id = $request->template_id;

            try {
                $ans = DB::select('select template_data, template_x, template_y from template_information where template_id="'.$template_id.'"');

            } catch (\PDOException $e) {
//                return json_encode($e);
                return json_encode($result);
            }
            $id = $user_id;
            $time = date("YmdHis");
//            $info = $_FILES['img']['type'];
//            $info = explode('/',$info);
//            $a = base_path().'/public/img/'.$time.".".$info[1]; //$time;
//            if (move_uploaded_file($_FILES['img']['tmp_name'], $a)) {
            $img = $request->img;
            $a = base_path().'/public/img/'.$img.".png"; //$time;

            $img = Image::make($a);
            $img->fit(1254, 758);
            $img->save($a);

            foreach ($ans as $value) {
                $value_ans = $value->template_data;
                $text =$request->$value_ans;
                $x = $value->template_x;
                $y = $value->template_y;
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
                'tenple_id' => $template_id

            ];
            try {
                DB::beginTransaction();
                DB::insert('insert into meisi(user_id,path,template_id)values(:id,:path,:tenple_id)',$param);
                $meisi_id = DB::select('select meisi_id from meisi where path="'.$path.'"');
                $text = 'http://18001187.pupu.jp/DEMESI/public/card/collection/' . (String)$meisi_id[0]->meisi_id;
                $qrcode = new QRCode();
                $position = 'bottom-right';
                $img->insert($qrcode->render($text), $position, 10, 20);
                $img->save();
                DB::commit();

            } catch (\PDOException $e) {
                DB::rollBack();
//                return json_encode($e);
                return json_encode($result);
            }
            try{
                DB::beginTransaction();
                foreach ($ans as $value) {
                    $data_name = $value->template_data;
                    $insert_data =$request->$data_name;
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
//                return json_encode($e);
                return json_encode($result);
            }
//            }else{
//                $result = 3;
//
//            }
//        }else{
//            $result = 4;
//
//        }
        return json_encode($result);
    }

    function AllCardTableReturn(Request $request, $user_id){
        try {
            $result = DB::select('select * from meisi where user_id ='.$user_id);
            return json_encode($result);

        } catch (\PDOException $e) {
            return json_encode($e);
        }
    }

    function CardInformationReturn(Request $request, $meisi_id){
        try {
            $result = DB::select('select * from meisi_data where meisi_id ='.$meisi_id);
            return json_encode($result);

        } catch (\PDOException $e) {
            return json_encode($e);
        }
    }

    function InsertCollection(Request $request, $meisi_id, $user_id){
        $param = [
            'user_id' => $user_id,
            'meisi_id' => $meisi_id,

        ];
        $result = 0;
        try{
            DB::beginTransaction();
            DB::insert('insert into meisi_collection(user_id, meisi_id, ) value(:user_id,:meisi_id)',$param);
            DB::commit();
            $result = 1;
            return json_encode($result);

        }catch (\PDOException $e){
            DB::rollBack();
            return $e;
            return json_encode($result);

        }
    }

    function CollectionReturn(Request $request, $user_id){
        $param = $user_id;

        try{
//            $result = DB::select('select meisi_id from meisi_collection where ="'.$param.'"');  meisi.meisi_id,meisi.user_id
            $result = DB::select('select meisi.meisi_id,meisi.user_id from meisi_collection inner join meisi on meisi_collection.meisi_id = meisi.meisi_id where meisi_collection.user_id = "'.$param.'"');
            return json_encode($result);
        }catch (\PDOException $e){
            return json_encode($e);
        }
    }


}