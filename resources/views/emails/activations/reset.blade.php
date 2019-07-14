@component('mail::message')
# リセットURLを送信しました
下記のリンクをクリックしてパスワードリセットを行ってください。
@component('mail::button', ['url' => $link])
パスワードリセット
@endcomponent
Thanks,
@endcomponent