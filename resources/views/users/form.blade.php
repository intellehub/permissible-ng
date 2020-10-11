@extends('redprintUnity::page')

@section('title')
    @if($user->id)
        {{ trans('permissible::core.editing', ['data' => $user->first_name . ' ' . $user->last_name ]) }}
    @else
        {{ trans('permissible::core.add_new_user') }}
    @endif
@stop

@section('page_title')     
    @if($user->id)
        {{ trans('permissible::core.editing', ['data' => $user->first_name . ' ' . $user->last_name ]) }}
    @else
        {{ trans('permissible::core.add_new_user') }}
    @endif
@stop

@section('page_subtitle') 
    @if($user->id)
        {{$user->name}}
    @else
        {{ trans('permissible::core.new') }}
    @endif
@stop

@section('page_icon') <i class="icon-users"></i> @stop

@section('content')
<div class="card">

    <div class="card-body">
        @if(function_exists('redprint') && redprint() && $user->hasRole('su'))
            <p>{{ trans('permissible::core.not_editable_redprint_mode') }}</p>
        @else
        <form method="post" enctype="multipart/form-data" action="{{ route('permissible.user.save') }}" >
        {!! csrf_field() !!}
        <input type="hidden" name="id" value="{{ $user->id }}">
        
            <div class="row">

                <div class="form-group col-md-6">
                    
                    <label class="control-label">{{ trans('permissible::core.first_name') }}<span class="required">*</span></label>

                    <input type="text" class="form-control" placeholder="John" name="first_name" value="{{$user->first_name}}" />

                    @if ($errors->has('first_name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('first_name') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group col-md-6">
                    <label class="control-label">{{ trans('permissible::core.last_name') }}<span class="required">*</span></label>
                    
                    <input type="text" class="form-control" placeholder="Doe" name="last_name" value="{{$user->last_name}}" />

                    @if ($errors->has('last_name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('last_name') }}</strong>
                        </span>
                    @endif

                </div>


            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label class="control-label">{{ trans('permissible::core.email') }}<span class="required">*</span></label>
                    <input type="email" class="form-control" placeholder="test@example.com" name="email" value="{{$user->email}}"/>

                    @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group col-md-6">
                    <label class="control-label">{{ trans('permissible::core.role') }}</label>

                        <select name="role" class="form-control selectpicker" data-live-search="true" title="{{ trans('permissible::core.please_select_role') }}" @if($user->hasRole("su")) disabled="true" @endif>
                        @foreach($roles as $role)
                            <option value="{{$role->id}}" @if($user->hasRole($role->code)) selected @endif>{{$role->name}}</option>
                        @endforeach
                        </select>
                   
                        @if ($errors->has('role'))
                            <span class="help-block">
                                <strong>{{ $errors->first('role') }}</strong>
                            </span>
                        @endif
                </div>

            </div>

            <div class="row">

                <div class="form-group col-md-6">
                    <label class="control-label">{{ trans('permissible::core.password') }}<span class="required">*</span></label>
                    <input type="password" class="form-control" placeholder="Password" name="password"/>

                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group col-md-6">
                    <label class="control-label">{{ trans('permissible::core.password_confirmation') }}<span class="required">*</span></label>
                    <input type="password" class="form-control" placeholder="Retype password" name="password_confirmation"/>
                </div>

            </div>

        </div>

        <div class="card-footer">
            <button class="btn btn-md btn-success" type="submit" style="border-radius: 0px !important;">{{ trans('permissible::core.save') }}</button>
        </div>

        </form>
        @endif
    </div>
</div>
@stop