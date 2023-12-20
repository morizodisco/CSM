@extends('common.layout')
@section('content')
    <div id="code_wrap">
        <div class="log_list promotion_code_form">
            <table style="width: 400px;">
                <tr>
                    <th>チェック URL</th>
                </tr>
                <tr>
                    <td><input type="text" name="check_url" value="{{ $health_check_list->check_url }}"></td>
                </tr>
            </table>
        </div>
    </div>
@stop

