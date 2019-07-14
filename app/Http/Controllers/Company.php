<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Company extends Controller
{
    //新規登録
    public function company_insert(Request $request){
        $param = [
            'company_name' => $request->company_name,
            'company_pass' => $request->company_pass,
            'company_place' => $request->company_place,
            'company_phone' => $request->company_phone,
            'company_url' => $request->company_url,
            'company_mail' => $request->company_mail,
        ];

        //データベースの接続開始
        DB::beginTransaction();
        try {
            DB::insert('insert into company_information(Company_name,Company_pass,Company_place,Company_phone,Company_url,Company_mail)values
           (:company_name,:company_pass,:company_place,:company_phone,:company_url,:company_mail)', $param);
            DB::commit();
            // all good
            $result = 1;
        } catch (\Exception $e) {
            DB::rollback();
            return 0;
            //エラー発生
        }


        return 1;
    }

    //プロフィール再設定用の情報取得
    function getData(Request $request){
        $param = [
            'company_id' => $request->company_id,
        ];
        $items = DB::select('select * from company_information where Company_id = :company_id',$param);
        return $items;
    }

    //プロフィールを再設定
    function update(Request $request){

        $name = [
            'company_id' => $request->company_id,
            'company_name' => $request->company_name,
        ];

        $pass = [
            'company_id' => $request->company_id,
            'company_pass' => $request->company_pass,
        ];

        $place = [
            'company_id' => $request->company_id,
            'company_place' => $request->company_place,
        ];

        $phone_number = [
            'company_id' => $request->company_id,
            'company_phone' => $request->company_phone,
        ];

        $url =[
            'company_id' => $request->company_id,
            'company_url' => $request->company_url,
        ];


        //データベースの接続開始
        DB::beginTransaction();
        try {
            DB::update('update company_information set Company_name = :company_name where Company_id = :company_id', $name);
            DB::update('update company_information set Company_pass = :company_pass where Company_id = :company_id', $pass);
            DB::update('update company_information set Company_place = :company_place where Company_id = :company_id', $place);
            DB::update('update company_information set Company_phone = :company_phone where Company_id = :company_id', $phone_number);
            DB::update('update company_information set Company_url = :company_url where Company_id = :company_id', $url);
            DB::commit();
            // all good
            $result = 1;
        } catch (\Exception $e) {
            DB::rollback();
            return 5;
            //エラー発生
        }

        //成功
        return $result;

    }

    //ログイン
    function login(Request $request){
        $param = [
            'company_name' => $request->company_name,
            'company_pass' => $request->company_pass,
        ];
        //IDとパスワード確認
        $result1 = DB::select('select * from company_information where Company_name=:company_name AND  Company_pass=:company_pass',$param);
        //存在確認
        if ($result1!=null){
            //存在
            return 1;
        }
        //失敗
        return 0;
    }
}
