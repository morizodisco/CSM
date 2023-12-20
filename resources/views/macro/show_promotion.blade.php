@extends('common.layout')
@section('content')
    <div id="code_wrap">
        <div class="log_list">
            <table style="width: 400px;">
                <tr>
                    <th>プロモーションコード</th>
                </tr>
                @foreach($promotions as $promotion)
                    <tr>
                        <td><input type="text" name="promotion" value="{{ $promotion->code }}"></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@stop

