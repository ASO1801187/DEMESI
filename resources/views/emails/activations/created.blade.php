@component('mail::message')
# 新規登録していただきありがとうございます！
下記のリンクをクリックしてアカウントの有効化を行ってください。
@component('mail::button', ['url' => $link])
アカウントの有効化
@endcomponent
Thanks,
@endcomponent