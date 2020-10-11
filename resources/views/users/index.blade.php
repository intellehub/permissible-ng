@extends('redprintUnity::page')

@section('title')
	{{ trans('permissible::core.users') }}
@stop


@section('page_title') {{ trans('permissible::core.users') }} @stop
@section('page_subtitle') {{ trans('permissible::core.manage_users') }} @stop
@section('page_icon') <i class="icon-users"></i> @stop

@section('content')
<div class="card">
	<div class="card-header">
		@if(auth()->user()->hasPermission('admins.manage'))
			<a href="{{route('permissible.user.new')}}" class="btn btn-success btn-sm font-white" style="border-radius: 0px !important;">
				<i class='icon-plus'></i> 
				{{ trans('permissible::core.add_new_user') }}
			</a>
		@endif


  		@if(count(Request::input()))
        <a class="btn btn-default btn-sm float-right" href="{{ route('permissible.user.index') }}">
        	<i class="icon-eraser"></i> 
        	{{ trans('permissible::core.clear') }}
        </a>

        <a class="btn btn-info btn-sm float-right" id="searchButton">
        	<i class="icon-search"></i> 
        	{{ trans('permissible::core.modify_search') }}
        </a>
      @else
        <a class="btn btn-default btn-sm float-right" id="searchButton" data-toggle="modal" data-target="#searchModal">
  				<i class="icon-search"></i>
  				{{ trans('permissible::core.search') }}
  			</a>
      @endif

	</div>

	<div class="card-body">

		<table class="table table-bordered">
			<thead class="table-header-color">
				<td>{{ trans('permissible::core.name') }}</td>
				<td>{{ trans('permissible::core.email') }}</td>
				<td>{{ trans('permissible::core.role') }}</td>
				<td>{{ trans('permissible::core.actions') }}</td>
			</thead>

			<tbody>
				@foreach($users as $user)
					<tr>
						<td>{{ $user->first_name.' '.$user->last_name }}</td>
						<td>{{ $user->email }}</td>
						<td>
							@foreach($user->roles as $role)
								<span class="label label-default">{{ $role->name }}</span>
							@endforeach
						</td>
						<td>
              @if(function_exists('redprint') && redprint() && $user->hasRole('su'))
                {{ trans('permissible::core.not_editable_redprint_mode') }}
              @else

  							@if(auth()->user()->hasPermission('admin.access'))
                  @if($user->deleted_at === null)
                    <a href="{{route('permissible.user.form', $user)}}" class="btn btn-primary btn-xs">
                      {{ trans('permissible::core.edit') }}
                    </a>
                    <a href="#" data-toggle="modal" data-target="#deleteModal{{ $user->id }}" class="btn btn-danger btn-xs">
                      {{ trans('permissible::core.delete') }}
                    </a>
                    @section('js')
                    @parent
                        <!-- modal starts -->
                        <div class="modal fade" id="deleteModal{{ $user->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" class="'form-horizontal'" action="{{ route('permissible.user.delete', $user->id) }}" >
                                    {!! csrf_field() !!}
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title"> {{ trans('permissible::core.delete') }}: {{ $user->first_name.' '.$user->last_name }} ? </h4>
                                    </div>
                    
                                    <div class="modal-body">
                                        {{ trans('permissible::core.delete_confirmation') }} <b>{{ $user->first_name.' '.$user->last_name }} ?</b>
                                    </div>
                    
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('permissible::core.close') }}</button>
                                        <button class="btn btn-md btn-danger" type="submit" style="border-radius: 0px !important;">{{ trans('permissible::core.delete') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- modal ends -->
                    @stop
                  @else
                    <a href="#" class="btn btn-info btn-xs" data-toggle="modal" data-target="#restoreModal{{ $user->id }}">
                      {{ trans('permissible::core.restore') }}
                    </a>
                    <a href="#" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#forceDeleteModal{{ $user->id }}">
                      {{ trans('permissible::core.force_delete') }}
                    </a>
                    @section('js')
                    @parent
                        <!-- modal starts -->
                        <div class="modal fade" id="restoreModal{{ $user->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" class="'form-horizontal'" action="{{ route('permissible.user.restore', $user->id) }}" >
                                    {!! csrf_field() !!}
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title"> {{ trans('permissible::core.restore') }}: {{ $user->first_name.' '.$user->last_name }} ? </h4>
                                    </div>
                    
                                    <div class="modal-body">
                                        {{ trans('permissible::core.restore_confirmation') }} <b>{{ $user->first_name.' '.$user->last_name }} ?</b>
                                    </div>
                    
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button class="btn btn-md btn-info" type="submit" style="border-radius: 0px !important;">{{ trans('permissible::core.restore') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- modal ends -->
                        <!-- modal starts -->
                        <div class="modal fade" id="forceDeleteModal{{ $user->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" class="'form-horizontal'" action="{{ route('permissible.user.force-delete', $user->id) }}" >
                                    {!! csrf_field() !!}
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title"> {{ trans('permissible::core.force_delete') }}: {{ $user->first_name.' '.$user->last_name }} ? </h4>
                                    </div>
                    
                                    <div class="modal-body">
                                        {!! trans('permissible::core.permanently_delete_confirmation', ['data' => $user->first_name.' '.$user->last_name ]) !!} ?</code>
                                    </div>
                    
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button class="btn btn-md btn-info" type="submit" style="border-radius: 0px !important;">{{ trans('permissible::core.force_delete') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- modal ends -->


                    @stop


                  @endif
                
  							@else
  								<a href="#" class="btn btn-primary btn-xs" disabled>
  									{{ trans('permissible::core.edit') }}
  								</a>
  							@endif			
              @endif

						</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		<!--Pagination-->
		<div class="pull-right">
			{{ $users->links() }}
		</div>
		<!--Ends-->
	</div>
</div>


	<!-- User search modal -->
    <div class="modal fade" id="searchModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="get" class="'form-horizontal'" action="{{ route('permissible.user.index') }}" >
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('permissible::core.search_users') }}</h4>
                </div>

                <div class="modal-body">                  
                    <div class="form-group">
                        <label class="col-sm-3">{{ trans('permissible::core.name') }}</label>
                        <div class="col-sm-9">
                            <input type="text" name="name" class="form-control" value="{{ Request::get('name') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3">{{ trans('permissible::core.email') }}</label>
                        <div class="col-sm-9">
                            <input type="text" name="email" class="form-control" value="{{ Request::get('email') }}">
                        </div>
                    </div>                                             
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('permissible::core.close') }}</button>
                    <button type="submit" class="btn btn-primary" >{{ trans('permissible::core.search') }}</button>
                </div>
                </form>
            </div>
        </div>
    </div>
	<!-- search modal ends -->
@stop