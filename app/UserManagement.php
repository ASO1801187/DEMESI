<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserManagement extends Model
{
    //データの新規登録
    function insertData(Request $request){
    $param = [
        'user_name' => $request->user_name,
        'password' => $request->password,
        'user_mailaddress' => $request->user_mailaddress,
        'phone_number' => $request->phone_number,
        'user_twitter' => $request->user_twitter,
        'user_instagram' => $request->user_instagram,
        'user_facebook' => $request->user_facebook,
        'user_blog' => $request->user_blog,
    ];

        //メールアドレス、電話番号の重複確認
    $result1 = DB::select('select mailaddress from user_information where mailaddress=:mailaddress',$param);
    $result2 = DB::select('select mailaddress from user_information where phone_number=:phone_number',$param);
    //検索結果で、メールアドレスの重複が何行かを取得
    $row1 = mysqli_num_rows($result1);
    //検索結果で、電話番号の重複が何行かを取得
    $row2 = mysqli_num_rows($result2);

    //被りがなし（0行）ならデータを登録する。
    if ($row1==0 && $row2==0){
        //データ挿入
        try {
            DB::insert('insert into user_information(user_name,user_password,mailaddress,phone_number)values
           (:user_name,,:mailaddress,:phone_number)', $param);
            DB::insert('insert into user_password(user_password)values (:user_password)', $param);
            DB::inseer('insert into user_sns(user_twitter,user_instagram,user_facebook,user_blog)values
//            (:user_twitter,:user_instagram,:user_facebook,:user_blog)', $param);
        }catch (Exception $e){
            report($e);
            //DB更新失敗
            return 5;
        }
    }elseif ($row1=!0 && $row2==0){
        //メールアドレス重複
        return 2;
    }elseif ($row1==0 && $row2=!0){
        //電話番号重複
        return 3;
    }else{
        //両方重複
        return 4;
    }
        //成功
        return 1;

    }
}
