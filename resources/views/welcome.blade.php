@extends('layouts.app')

@section('title') Odds Tipster @endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h1>
                @if(isset($request_sport_type))
                    $request_sport_type
                @elseif(\Request::get('sport_id') && $sport_types->where('id' ,\Request::get('sport_id'))->first())
                    {{ $sport_types->where('id' ,\Request::get('sport_id'))->first()->name }}
                    @if(\Request::get('cat_id') && $sport_types->where('id' ,\Request::get('cat_id'))->first())
                        <small> > {{  $sport_types->where('id' ,\Request::get('cat_id'))->first()->name }}</small>
                    @endif
                @else
                    All
                @endif
            </h1>
            <input type="date" name="date" value="{{ \Request::get('date') ?? date('Y-m-d') }}">
        </div>
        <div class="card-body pb-0 scrollable-x">
            <table class="table mb-0" >
{{--                <thead>--}}
{{--                    <tr>--}}
{{--                        <td>Teams</td>--}}
{{--                        <td>W1</td>--}}
{{--                        <td>Draw</td>--}}
{{--                        <td>W2</td>--}}
{{--                        <td class="d-none d-sm-table-cell">Website</td>--}}
{{--                        <td class="d-none d-sm-table-cell">Start</td>--}}
{{--                    </tr>--}}
{{--                </thead>--}}
                <tbody>

                @php
                    $auth_user = auth()->user();
                @endphp

                @foreach($tournaments as $k => $tournament)
                    <tr data-id="{{ $tournament->id }}" data-odd-id="">
                        <td colspan="6" class="bg-info text-white"><span>{{ $tournament->name }}</span></td>
                    </tr>
                    @if($tournament->games)
                        @foreach($tournament->games as $k => $game)
                            <tr class="child-p-03">
                                <td><span>{{ $game->home_team  }} - {{ $game->guest_team  }}</span></td>
                                <td><span>{{ $game->odd->win_home ?? 'X'  }}</span></td>
                                <td><span>{{ $game->odd->win_guest ?? 'X' }}</span></td>
                                <td><span>{{ $game->odd->win_guest ?? 'X' }}</span></td>
                                <td class="d-none d-sm-table-cell"><span>{{ $game->location ?: ''  }}</span></td>
                                <td class="white-space-nowrap"><span>{{ date('d , H:i' , strtotime($game->start_time))  }}</span></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
                @if(!count($tournaments))
                    <tr><td colspan="7" class="text-center">Empty Data</td></tr>
                @endif
                </tbody>
            </table>
        </div>
        </div>
        <div class="d-flex justify-content-center mt-3">
{{--            {{  $matches->links() }}--}}
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function (){
            $('input[name="date"]').on('change' , function (){
                let path = location.href.replace(/[\?\&]?date=.*/ , '');
                if(path.search(/\?/) > -1){
                    location.href = path + '&date=' + $(this).val();
                }else{
                    location.href = path + '?date=' + $(this).val();
                }
            });
        })
    </script>
@endsection
