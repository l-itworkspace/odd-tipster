@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1>
                @if(isset($request_sport_type))
                    $request_sport_type
                @elseif(\Request::get('sport_type') && $sport_types->where('type' ,\Request::get('sport_type'))->first())
                    {{ $sport_types->where('type' ,\Request::get('sport_type'))->first()->details }}
                @else
                    All
                @endif
            </h1>
        </div>
        <div class="card-body pb-0">
            <table class="table mb-0" >
                <thead>
                    <tr>
                        <td>Teams</td>
                        <td>W1</td>
                        <td>Draw</td>
                        <td>W2</td>
                        <td>Website</td>
                        <td>Start</td>
                    </tr>
                </thead>
                <tbody>
                @foreach($matches as $k => $match)

                    @php
                        $has_odd = $match->odd;
                        $auth_user = auth()->user();
                    @endphp
                    <tr data-id="{{ $match->id }}" data-odd-id="{{ $has_odd ? $has_odd->site_slug : '' }}">
                        <td>
                            <span>{{ $match->home_team  }}</span> - <span>{{  $match->guest_team }}</span>
                        </td>
                        <td>
                             <span class="{{  $auth_user ? 'cursor-pointer text-info' : '' }}">{{ $match->odd ? $match->odd->win_home : 'X' }}</span>
                        </td>
                        <td>
                            <span class="{{  $auth_user ? 'cursor-pointer text-info' : '' }}">
                                {{ $has_odd->draw ?? 'X' }}
                            </span>
                        </td>
                        <td>
                            <span class="{{  $auth_user ? 'cursor-pointer text-info' : '' }}">{{ $has_odd ? $has_odd->win_guest : 'X' }}</span>
                        </td>
                        <td>
                            <span>{{ $has_odd ? $has_odd->site_nickname : 'X'}}</span>
                        </td>
                        <td>
                            <span>{{ date('d , H:i' , strtotime($match->start_time)) }}</span>
                        </td>
                    </tr>
                    <tr class="details">
                        <td colspan="7" class="border-top-0 text-center">
                            <a class="" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample-{{ $match->id }}">
                                Details
                            </a>
                        </td>
                    </tr>
                @endforeach
                @if(!count($matches))
                    <tr><td colspan="7" class="text-center">Empty Data</td></tr>
                @endif
                </tbody>
            </table>
        </div>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{  $matches->links()  }}
        </div>
    </div>
@endsection

