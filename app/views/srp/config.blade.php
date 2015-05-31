@extends('layouts.masterLayout')

@section('html_title', 'Configure SRP Settings')

@section('page_content')

	<div class="row">
		<div class="col-md-8">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Fleet Types</h3>
				</div>
				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable">
						<thead>
							<tr>
								<th style="width: 75%;">Name</th>
								<th>Count</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($fleetTypes as $fleetType)
							<tr>
								<td>{{ $fleetType->fleetTypeName }}</td>
								<td>{{ $fleetType->fleetCount }}</td>
								<td><a href="{{ action('SrpController@getConfigureFleetType', array($fleetType->fleetTypeID)) }}" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i> Edit</a></td>
								<td><a a-delete-item="{{ action('SrpController@getDeleteFleetType', array($fleetType->fleetTypeID)) }}" a-item-name="{{ $fleetType->fleetTypeName }}" class="btn btn-danger btn-xs delete-item"><i class="fa fa-times"></i> Delete</a></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col-md-8 -->
		<div class="col-md-4">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Add Fleet Type</h3>
				</div>
				<div class="box-body">
					{{ Form::open(array('action' => 'SrpController@postConfigureFleetType', 'class' => 'form-horizontal')) }}
						<fieldset>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-4 control-label" for="fleetTypeName">Name</label>
								<div class="col-md-6">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-magic"></i></span>
										{{ Form::text('fleetTypeName', null, array('id' => 'fleetTypeName', 'class' => 'form-control input-md')) }}
									</div>
								</div>
							</div>
							<!-- Button -->
							<div class="form-group">
								<label class="col-md-4 control-label" for="singlebutton"></label>
								<div class="col-md-4">
									<button id="singlebutton" name="singlebutton" class="btn btn-primary">Add Fleet Type</button>
								</div>
							</div>
						</fieldset>
					{{ Form::close()}}
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col-md-4 -->
	</div><!-- /.row -->
	{{-- TODO: Before enabling this, add tag colours into the database for statusType
	<div class="row">
		<div class="col-md-8">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Status Types</h3>
				</div>
				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable">
						<thead>
							<tr>
								<th style="width: 75%;">Name</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($statusTypes as $statusType)
							<tr>
								<td>{{ $statusType->statusTypeName }}</td>
								<td><a href="{{ action('SrpController@getConfigureStatusType', array($statusType->statusTypeID)) }}" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i> Edit</a></td>
								<td><a a-delete-item="{{ action('SrpController@getDeleteStatusType', array($statusType->statusTypeID)) }}" a-item-name="{{ $fleetType->fleetTypeName }}" class="btn btn-danger btn-xs delete-item"><i class="fa fa-times"></i> Delete</a></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col-md-8 -->
		<div class="col-md-4">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Add Status Type</h3>
				</div>
				<div class="box-body">
					{{ Form::open(array('action' => 'SrpController@postConfigureStatusType', 'class' => 'form-horizontal')) }}
						<fieldset>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-4 control-label" for="statusTypeName">Name</label>
								<div class="col-md-6">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-magic"></i></span>
										{{ Form::text('statusTypeName', null, array('id' => 'statusTypeName', 'class' => 'form-control input-md')) }}
									</div>
								</div>
							</div>
							<!-- Button -->
							<div class="form-group">
								<label class="col-md-4 control-label" for="singlebutton"></label>
								<div class="col-md-4">
									<button id="singlebutton" name="singlebutton" class="btn btn-primary">Add Status Type</button>
								</div>
							</div>
						</fieldset>
					{{ Form::close()}}
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col-md-4 -->
	</div><!-- /.row -->
	--}}
	<div class="row">
		<div class="col-md-8">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">All Ships</h3>
				</div>
				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable">
						<thead>
							<tr>
								<th>ID</th>
								<th style="width: 75%;">Name</th>
								<th>Value</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($shipTypes as $ship)
							<tr>
								<td>{{ $ship->shipTypeID }}</td>
								<td>{{ $ship->shipTypeName }}</td>
								<td>{{ $ship->shipTypeValue }}</td>
								<td><a href="{{ action('SrpController@getConfigureShipType', array($ship->shipTypeID)) }}" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i> Edit</a></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col-md-8 -->
	</div><!-- /.row -->

@stop

@section('javascript')

	<script type="text/javascript">
		$(document).on("click", ".delete-item", function(e) {

			// Save the links
			var delete_item = $(this).attr("a-delete-item");

			// Provide the user a option to keep the existing data, or delete everything we know about the key
			bootbox.dialog({
				message: "Please confirm whether you want to delete this item?",
				title: "Delete item " + $(this).attr("a-item-name"),
				buttons: {
					success: {
						label: "No Thanks",
						className: "btn-default"
					},
					danger: {
						label: "Delete Item",
						className: "btn-danger",
						callback: function() {
							window.location = delete_item;
						}
					}
				}
			});
		});
	</script>

@stop
