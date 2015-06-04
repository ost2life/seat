@extends('layouts.masterLayout')

@section('html_title', 'Configure Ships')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li><a href="{{ action('SrpConfigController@index') }}">Configure</a></li>
			<li class="active">Ships</li>
		</ol>
	</div>

	<div class="row">

		<div class="col-md-7">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Ships</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th>Last Updated</th>
								<th style="width: 99%;">Name</th>
								<th>Ship</th>
								<th>Value</th>
								<th>Doctrines</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($ships as $ship)
							<tr>
								<td>{{ $ship->updated_at }}</td>
								<td>{{ $ship->name }}</td>
								<td>{{ $ship->type()->first()->typeName }}</td>
								<td>{{ App\Services\Helpers\Helpers::formatBigNumber($ship->value) }}</td>
								<td>{{ $ship->doctrines()->count() }}</td>
								<td><a href="{{ action('SrpShipController@show', array($ship->id)) }}" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i> Edit</a></td>
								<td><a a-delete-item="{{ action('SrpShipController@destroy', array($ship->id)) }}" a-item-name="{{ $ship->name }} ({{ $ship->type()->first()->typeName }})" class="btn btn-danger btn-xs delete-item"><i class="fa fa-times"></i> Delete</a></td>
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
					<h3 class="box-title">Create Ship</h3>
				</div>

				<div class="box-body">
					{{ Form::open(array('action' => array('SrpShipController@store'), 'class' => 'form-horizontal')) }}
						<fieldset>
							<!-- Data -->
							<datalist id="typeNames">
								@foreach ($all_ships as $ship)
									<option value="{{ $ship->typeName }}">
								@endforeach
							</datalist>
							<!-- Text Input -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="ship">Ship</label>
								<div class="col-md-7">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-rocket"></i></span>
										{{ Form::text('ship', null, array('class' => 'form-control input-md', 'list' => 'typeNames')) }}
									</div>
								</div>
							</div>
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
							<!-- Text Input -->
							<div class="form-group">
								<label class="col-md-3 control-label" for="value">Value</label>
								<div class="col-md-7">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-money"></i></span>
										{{ Form::text('value', null, array('class' => 'form-control input-md')) }}
									</div>
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
									document.open();
									document.write(result);
									document.close();
								}
							});
						}
					}
				}
			});
		});
	</script>

@stop
