@extends('redprintUnity::page')

@section('title')
	Permissible::Role
@stop

@section('page_title') Roles @stop
@section('page_subtitle') Manage User Roles @stop
@section('page_icon') <i class="icon-shield"></i> @stop

@section('content')

@if($errors->any())
	<div class="panel">
		<div class="panel-heading">
			<p class="text-danger">Validation Error...</p>
		</div> 
		<div class="panel-body"> 
			@foreach ($errors->all() as $error)
				<p class="text-danger">{{ $error }}</p>
			@endforeach
		</div>
	</div>
@endif


<div class="card">
	<div class="card-header">
		@if(auth()->user()->hasPermission('acl.manage'))
			<a type="button" class="btn btn-success btn-xs font-white" data-toggle="modal" data-target="#roleModal">
			   <i class="icon-plus"></i> 
			   {{ trans('permissible::core.new') }}
			</a>
		@endif
	</div>

	<div class="card-body">
		<table class="table table-hover table-bordered" >
			<thead class="table-header-color">
				<th>{{ trans('permissible::core.role') }}</th>
				<th>{{ trans('permissible::core.code') }}</th>
				<th>{{ trans('permissible::core.weight') }}</th>
				<th>{{ trans('permissible::core.actions') }}</th>
			</thead>

			<tbody>
				@foreach($roles as $role)
					<tr>
						<td>{{$role->name}}</td>
						<td><span class="text-danger"><b>{{ $role->code }}</b></span></td>
						<td><span class="badge badge-info"><b>{{ $role->weight }}</b></span></td>
						<td>
							@if(function_exists('redprint') && redprint() && $role->code === 'su')
								{{ trans('permissible::core.not_editable_redprint_mode') }}
							@else
								<a href="#" data-toggle="modal" data-target="#editRoleModal{{$role->id}}" class="btn btn-primary btn-xs">
									<i class="icon-edit"></i>
									{{ trans('permissible::core.edit') }}
								</a>

								<!-- Set permission for role -->
								@if(auth()->user()->hasPermission('acl.set'))
									<a href="{{route('permissible.role.permission', $role->id)}}" class="btn btn-info btn-xs">
										<i class="icon-key2"></i>
										{{ trans('permissible::core.set_permission') }}
									</a>
								@endif
								<!-- ends -->

							@endif

							@section('js')
							@parent
							<!-- Modal for create role-->
							<div class="modal fade" id="editRoleModal{{$role->id}}" tabindex="-1" role="dialog" aria-labelledby="editRoleModalLabel">
							<form class="form-horizontal" method="post" action="{{ route('permissible.role.post') }}">
							{!! csrf_field() !!}
							<input type="hidden" name="id" value="{{ $role->id }}">
							
							  <div class="modal-dialog" role="document">
							    <div class="modal-content">
							      <div class="modal-header">
							        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							        <h4 class="modal-title" id="roleModalLabel">
							        	{{ trans('permissible::core.edit_role') }}: {{$role->name}}
							        </h4>
							      </div>
							      <div class="modal-body">
							        <div class="form-group">
						            	<div class="col-md-3">
						                	<label>{{ trans('permissible::core.role') }}</label>
						                </div>
						                <div class="col-sm-9">
						                    <input type="text" class="form-control" name="role_name" value="{{ $role->name }}" required>
						                </div>
						            </div>

							        	<div class="form-group">
						            	<div class="col-md-3">
						                	<label>{{ trans('permissible::core.code_short_name') }}</label>
						                </div>
						                <div class="col-sm-9">
						                    <input type="text" class="form-control" name="code" value="{{ $role->code }}" required>
						                </div>
						            </div>

							        	<div class="form-group">
						            	<div class="col-md-3">
						                	<label>{{ trans('permissible::core.weight') }}</label>
						                </div>
						                <div class="col-sm-9">
						                    <select name="weight" class="form-control">
						                    	@for($i=1; $i <= 10; $i++)
						                    		<option value="{{ $i }}" @if($role->weight === $i) selected="selected" @endif>{{ $i }}</option>
						                    	@endfor
						                    </select>
						                    <a href="#" data-toggle="tooltip" data-title="{{ trans('permissible::core.lowest_strength_tooltip') }}"><small>{{ trans('permissible::core.lowest_strength_explanation') }}</small></a>
						                </div>
						            </div>

							      </div>
							      <div class="modal-footer">
						            <button type="button" class="btn btn-default" data-dismiss="modal">
						            	{{ trans('permissible::core.close') }}
						            </button>
						            <button type="submit" class="btn btn-primary " >{{ trans('permissible::core.save') }}</button>
						          </div>
						        </form>
							    </div>
							  </div>
							</div>
							<!--Modal Ends-->
							@stop

						</td>
					</tr>
					<!-- Modal for delete role-->
				@endforeach
			</tbody>
		</table>
		<!-- Table Ends -->
	</div>
</div>


	<!-- Modal for create role-->
	<div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel">
	<form class="form-horizontal" method="post" action="{{ route('permissible.role.post') }}">
	{!! csrf_field() !!}
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="roleModalLabel">
	        	{{ trans('permissible::core.add_new_role') }}
	        </h4>
	      </div>
	      <div class="modal-body">
	        <div class="form-group">
            	<div class="col-md-3">
                	<label>{{ trans('permissible::core.role') }}</label>
                </div>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="role_name" required>
                </div>
            </div>

	        	<div class="form-group">
            	<div class="col-md-3">
                	<label>{{ trans('permissible::core.code_short_name') }}</label>
                </div>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="code" required>
                </div>
            </div>

	        	<div class="form-group">
            	<div class="col-md-3">
                	<label>{{ trans('permissible::core.weight') }}</label>
                </div>
                <div class="col-sm-9">
                    <select name="weight" class="form-control">
                    	@for($i=1; $i <= 10; $i++)
                    		<option value="{{ $i }}" @if($i === 10) selected="selected" @endif>{{ $i }}</option>
                    	@endfor
                    </select>
                    <a href="#" data-toggle="tooltip" data-title="{{ trans('permissible::core.lowest_strength_tooltip') }}"><small>{{ trans('permissible::core.lowest_strength_explanation') }}</small></a>
                </div>
            </div>

	      </div>
	      <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
            	{{ trans('permissible::core.close') }}
            </button>
            <button type="submit" class="btn btn-primary">{{ trans('permissible::core.save') }}</button>
          </div>
        </form>
	    </div>
	  </div>
	</div>
	<!--Modal Ends-->


@stop