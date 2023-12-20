@extends('common.layout')
@section('content')
    <div id="code_wrap">
        <div class="log_list promotion_code_form">
            @if(session()->has('message'))
                <p>{{ session('message') }}</p>
            @else
                <form method="post">
                    @csrf
                    <table style="width: 400px;">
                        <tr>
                            <th>チェック URL</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="check_url"></td>
                        </tr>
                    </table>
                    <div style="width: 400px; text-align: center; margin-top: 4px">
                        <button type="submit" name="status_flag" value="0" style="width: 198px">NG</button>
                        <button type="submit" name="status_flag" value="1" style="width: 198px">OK</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@stop

