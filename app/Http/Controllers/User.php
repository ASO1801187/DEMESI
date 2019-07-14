<?php

namespace App\Http\Controllers;

use App\Mail\ActivationCreted;
use App\Mail\ResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class User extends Controller
{
    //新規登録
    public function user_insert(Request $request){
        $param = [
            'user_name' => $request->user_name,
            'user_password' => $request->user_password,
            'mailaddress' => $request->mailaddress,
            'phone_number' => $request->phone_number,
        ];

        $mail = [
            'mailaddress' => $request->mailaddress,
        ];

        $phone = [
            'phone_number' => $request->phone_number,
        ];

        $result =  0;
        $Anser1 = DB::select('select * from user_information where mailaddress=:mailaddress',$mail);
        $Anser2 = DB::select('select * from user_information where phone_number=:phone_number',$phone);
        if ($Anser1 != null && $Anser2 != null){
            //mailと電話番号に重複
            $result=4;
        }elseif ($Anser1 != null){
            //mailの重複
            $result=2;
        }else if ($Anser2 != null){
            //電話番号の重複
            $result=3;
        }
        if ($result==4 || $result== 3 || $result ==2){
            $box = array("result"=>$result);
            return $box;
        }

        //データベースの接続開始
        DB::beginTransaction();
        try {
            DB::insert('insert into user_information(user_name,password,mailaddress,phone_number)values
           (:user_name,:user_password,:mailaddress,:phone_number)', $param);
            DB::commit();
            // all good
            $result = 1;
        } catch (\Exception $e) {
            DB::rollback();
            $result = 5;
            //エラー発生
        }

        //成功した場合はメールを送信する
        if($result == 1){
            $items = DB::select('select user_id from user_information where mailaddress = :mailaddress',$mail);
            $box= $items[0];
            Mail::to($request->mailaddress)->send(new ActivationCreted($box));
        }

        $box = array("result"=>$result);
        return $box;
    }

    //登録完了へ
    function user_registration(Request $request){
        $param = [
            'user_id' => $request->user_id,
        ];
        $result=0;
        //データベースの接続開始
        DB::beginTransaction();
        try {
            DB::update('update user_information set Unsubscribe_flag = 1 where user_id = :user_id', $param);
            DB::commit();
            $result=1;
        }catch (\Exception $e) {
            DB::rollback();
            $result=0;
            //エラー発生
        }
        $box = array("result"=>$result);
        //成功
        return $box;
    }


    //プロフィール再設定用の情報取得
    function getData(Request $request){
        $param = [
            'user_id' => $request->user_id,
        ];

        $items = DB::select('select * from user_information where user_id = :user_id',$param);
        if ($items == null){
            $result = 0;
            $box = array('$result'=>$result);
            json_encode($box);
            return $box;
        }
        $result = 1;
        $items = $items[0];
        $box = array('$result'=>$result,'user_name'=>$items->user_name,'user_password'=>$items->password,'mailaddress'=>$items->mailaddress,'phone_number'=>$items->phone_number);
        json_encode($box);
        return $box;
    }

    //プロフィールを再設定
    function update(Request $request){

        $name = [
            'user_id' => $request->user_id,
            'user_name' => $request->user_name,
        ];

        $pass = [
            'user_id' => $request->user_id,
            'user_password' => $request->user_password,
        ];

        $mailaddress = [
            'user_id' => $request->user_id,
            'mailaddress' => $request->mailaddress,
        ];

        $phone_number = [
            'user_id' => $request->user_id,
            'phone_number' => $request->phone_number,
        ];

        $mail = [
            'mailaddress' => $request->mailaddress,
        ];

        $phone = [
            'phone_number' => $request->phone_number,
        ];
        $result = 0;
        $Anser1 = DB::select('select * from user_information where mailaddress=:mailaddress',$mail);
        $Anser2 = DB::select('select * from user_information where phone_number=:phone_number',$phone);
        if ($Anser1 != null && $Anser2 != null){
            //mailと電話番号に重複
            $result=4;
        }elseif ($Anser1 != null){
            //mailの重複
            $result=3;
        }else if ($Anser2 != null){
            //電話番号の重複
            $result=2;
        }

        if ($result==4 || $result== 3 || $result ==2){
            $box = array("result"=>$result);
            return $box;
        }

        //データベースの接続開始
        DB::beginTransaction();
        try {
            DB::update('update user_information set user_name = :user_name where user_id = :user_id', $name);
            DB::update('update user_information set user_password = :user_password where user_id = :user_id', $pass);
            DB::update('update user_information set mailaddress = :mailaddress where user_id = :user_id', $mailaddress);
            DB::update('update user_information set phone_number = :phone_number where user_id = :user_id', $phone_number);
            DB::commit();
            // all good
            $result = 1;
        } catch (\Exception $e) {
            DB::rollback();
            $result = 5;
            //エラー発生
        }

        //成功
        $box = array("result"=>$result);
        //成功
        return $box;

    }

    //パスワード再設定の
    function id_index(Request $request){
        $param = [
            'mailaddress' => $request->mailaddress,
        ];
        $result = 0;
        $items = DB::select('select user_id from user_information where mailaddress = :mailaddress',$param);
        if ($items == null){
            $result = 0;
            $box = array('$result'=>$result);
            json_encode($box);
            return $box;
        }else{
            $result = 1;
        }
        $box = $items[0];
        Mail::to($request->mailaddress)->send(new ResetMail($box));
        return 1;
    }

    //パスワードの再設定
    function reset(Request $request){

        $param = [
            'user_id' => $request->user_id,
            'user_password' => $request->user_password,
        ];
        $result = 0;
        //データベースの接続開始
        DB::beginTransaction();
        try {
            DB::update('update user_information set user_password = :user_password where user_id = :user_id', $param);
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $result = 0;
            $box = array('$result'=>$result);
            json_encode($box);
            return $box;
            //エラー発生
        }
        //成功
        $result = 1;
        $box = array('$result'=>$result);
        json_encode($box);
        return $box;
    }

    //退会処理
    function user_delete(Request $request){
        $param = [
            'user_id' => $request->user_id,
        ];
        //データベースの接続開始
        DB::beginTransaction();
        try {
            DB::update('update user_information set Unsubscribe_flag = 0 where user_id = :user_id', $param);
            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
            $result = 0;
            $box = array('$result'=>$result);
            json_encode($box);
            return $box;
            //エラー発生
        }
        //成功
        $result = 1;
        $box = array('$result'=>$result);
        json_encode($box);
        return $box;
    }

    //ログイン
    function login(Request $request){
        $param = [
            'user_name' => $request->user_name,
            'user_password' => $request->user_password,
        ];
        //IDとパスワード確認
        $result1 = DB::select('select * from user_information where user_name=:user_name AND  user_password=:user_password
        AND Unsubscribe_flag=1 ',$param);
        //行数確認

        if ($result1!=null){
            //存在
            $result = 1;
            $box = array('$result'=>$result);
            json_encode($box);
            return $box;
        }
        //失敗
        $result = 0;
        $box = array('$result'=>$result);
        json_encode($box);
        return $box;
    }

}