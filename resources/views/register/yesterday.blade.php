@extends('common.layout')
@section('content')
    <style>
        body {
            background: #424242;
        }
    </style>
<div class="promotion_code_form">
    <form method="post">
        @csrf
        <dl>
            <dt>プロモーションコード</dt>
            <dd><input name="code" value=""></dd>
            <dt>IMP</dt>
            <dd><input name="imp" value=""></dd>
            <dt>アクセス数</dt>
            <dd><input name="access" value=""></dd>
            <dt>CTR</dt>
            <dd><input name="ctr" value=""></dd>
            <dt>発生成果数</dt>
            <dd><input name="occur_num" value=""></dd>
            <dt>発生成果額</dt>
            <dd><input name="occur_price" value=""></dd>
            <dt>確定成果数</dt>
            <dd><input name="confirm_num" value=""></dd>
            <dt>確定成果額</dt>
            <dd><input name="confirm_price" value=""></dd>
            <dt>CVR</dt>
            <dd><input name="cvr" value=""></dd>
            <dt>報酬合計</dt>
            <dd><input name="total" value=""></dd>
        </dl>
        <button type="submit" name="add" value="add">登録する</button>
    </form>
</div>
@stop
