@extends('common.layout')
@section('content')
    <div id="code_wrap">
        <div class="log_list">
            <table style="width: 400px;">
                <tr>
                    <th>メディアコード</th>
                </tr>
                @foreach($genres as $genre)
                    <tr>
                        <td><input type="text" name="media" value="{{ $genre->media_id }}"></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@stop

