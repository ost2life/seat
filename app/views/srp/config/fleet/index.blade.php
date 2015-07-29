@extends('layouts.masterLayout')

@section('html_title', 'Configure Fleet Types')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li><a href="{{ action('SrpConfigController@index') }}">Configure</a></li>
			<li class="active">Fleet Types</li>
		</ol>
	</div>

	<div class="row">

		<div class="col-md-7">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Fleet Types</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th>Last Updated</th>
								<th style="width: 99%;">Name</th>
								<th>Used</th>
								<th>Public</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($fleet_types as $fleet_type)
								<tr>
									<td>{{ $fleet_type->updated_at }}</td>
									<td>{{ $fleet_type->name }}</td>
									<td>{{ $fleet_type->fleets->count() }}</td>
									<td>
										@if (!!$fleet_type->public)
											<span class="label label-success"><i class="fa fa-fw fa-check"></i></span>
										@else
											<span class="label label-danger"><i class="fa fa-fw fa-times"></i></span>
										@endif
									</td>
									<td><a href="{{ action('SrpFleetTypeController@show', array($fleet_type->id)) }}" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i> Edit</a></td>
									<td><a a-delete-item="{{ action('SrpFleetTypeController@destroy', array($fleet_type->id)) }}" a-item-name="{{ $fleet_type->name }}" class="btn btn-danger btn-xs delete-item"><i class="fa fa-times"></i> Delete</a></td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->

			</div><!-- /.box -->

		</div><!-- /.col -->

		<div class="col-md-5">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Create Fleet Type</h3>
				</div>

				<div class="box-body">
					{{ Form::open(array('action' => array('SrpFleetTypeController@store'), 'class' => 'form-horizontal')) }}
						<fieldset>
							<!-- Text Input -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="name">Name</label>
								<div class="col-md-7">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-pencil"></i></span>
										{{ Form::text('name', null, array('class' => 'form-control input-md')) }}
									</div>
								</div>
							</div>
							<!-- Checkbox -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="public">Public</label>
								<div class="col-md-7">
									<label>
										{{ Form::hidden('public', 0) }}
										{{ Form::checkbox('public', 1, $fleet_type->public) }}
										Allow anyone to use this fleet type
									</label>
								</div>
							</div>
							<!-- Button -->
							<div class="form-group">
								<label class="col-md-3 control-label"></label>
								<div class="col-md-7">
									<button class="btn btn-block btn-primary">Create</button>
								</div>
							</div>
						</fieldset>
					{{ Form::close()}}
				</div><!-- /.box-body -->

			</div><!-- /.box -->

		</div><!-- /.col -->

	</div><!-- /.row -->

@stop

@section('javascript')

	<script type="text/javascript">
		$(document).on("click", ".delete-item", function(e) {
			var delete_item = $(this).attr("a-delete-item");

			bootbox.dialog({
				message: "Are you sure that you want to delete this?",
				title: "Delete " + $(this).attr("a-item-name") + "?",
				buttons: {
					success: {
						label: "No Thanks",
						className: "btn-default"
					},
					danger: {
						label: "Delete Item",
						className: "btn-danger",
						callback: function() {
							$.ajax({
								url: delete_item,
								type: 'DELETE',
								success: function(result) {
									location.reload();
								}
							});
						}
					}
				}
			});
		});
	</script>

@stop
