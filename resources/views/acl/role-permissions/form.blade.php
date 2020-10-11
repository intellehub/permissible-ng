@extends('redprintUnity::page')

@section('title')
  {{trans('permissible::core.set_permissions_for')}}: {{$role->name}}
@stop

@section('content')
<div class="card">

  <div class="card-body">

    <form  method="post" class="form-horizontal">
    {!! csrf_field() !!}

      <label>{{ trans('permissible::core.set_permissions_for') }}: <span class="text-success">{{$role->name}}</span></label>

      <input type="hidden" value="{{$role->id}}" name="role_id">

      <div class="form-group">
        <div class="checkbox col-sm-12">
          <label>
              <input type="checkbox"  name="all_permission" id="all-access" class="all-access" >
              <span style=" color:navy; padding-bottom: 10px; font-weight: bold;">{{ trans('permissible::core.select_all') }}</span>
          </label>
        </div>
      </div>

        @foreach($permissions as $permission)
          <div class="form-group">
            <label class="control-label col-sm-3"> {{ ucwords($permission->type).' '.ucwords($permission->name) }}</label>
            <div class="col-sm-9">
              <input
                type="checkbox"
                class="input-group"
                data-toggle="switch"
                name="permissions{{$permission->id}}"
                value="{{$permission->id}}"
                @if(in_array(ucwords($permission->type).' '.ucwords($permission->name) , $rolePermissionNameLists) == true)
                  checked
                @endif
                > 
            </div>
          </div>
        @endforeach
        

        <div class="panel-footer">
          <button type="submit" class="btn btn-primary " style="margin-bottom: 10px;">{{ trans('permissible::core.save') }}</button>
        </div>

    </form>
  </div> 
</div>
@stop


@section('js')
  @parent
  <script type="text/javascript">
    $(document).ready(function () {
    $(".all-access").click(function () {
        $(".input-group").prop('checked', $(this).prop('checked'));
        });
    });
  </script>

@stop

