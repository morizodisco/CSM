@extends('common.layout')
@section('content')
    <div id="code_wrap">
        <div class="log_list">
            <table style="width: 400px;">
                <tr>
                    <th>メディアコード</th>
                    <th>プロモーションコード</th>
                </tr>
                @foreach($codes as $code)
                    <tr>
                        <td><input type="text" name="media" value="{{ $code['media'] }}"></td>
                        <td><input type="text" name="promotion" value="{{ $code['promotion'] }}"></td>
                    </tr>
                @endforeach
            </table>
            @php
                // echo count($codes);
            @endphp
        </div>
    </div>
@stop

