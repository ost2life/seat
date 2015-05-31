@extends('layouts.masterLayout')

@section('html_title', 'All Fleets')

@section('page_content')

	<div class="row">
		<div class="col-md-8">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Fleets</h3>
				</div>
				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable">
						<thead>
							<tr>
								<th>Date</th>
								<th>Fleet Type</th>
								<th>Fleet Commander</th>
								<th>SRP Code</th>
								<th>Total Requests</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($fleets as $fleet)
							<tr>
								<td>{{ $fleet->created_at }}</td>
								<td>{{ $fleet->fleetTypeName }}</td>
								<td>{{ $fleet->fleetCharacterName }}</td>
								<td>{{ $fleet->fleetSrpCode }}</td>
								<td>{{ $fleet->totalRequests }}</td>
								<td><a href="{{ action('SrpController@getFleet', array('id' => $fleet->fleetID)) }}" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> Details</a></td>
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
					<h3 class="box-title">Create New Fleet</h3>
				</div>
				<div class="box-body">
					@if (count($characters) > 0)
						{{ Form::open(array('action' => 'SrpController@postFleet', 'class' => 'form-horizontal')) }}
							<fieldset>
								<!-- Text input-->
								<div class="form-group">
									<label class="col-md-4 control-label" for="fleetCommander">Fleet Commander</label>
									<div class="col-md-6">
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-fw fa-user"></i></span>
												{{ Form::select('fleetCommander', $characters, $settings['main_character_id'] ? $settings['main_character_id'] : 0, array('id' => 'fleetCommander', 'class' => 'form-control')) }}
											</div>
									</div>
								</div>
								<!-- Text input-->
								<div class="form-group">
									<label class="col-md-4 control-label" for="fleetType">Fleet Type</label>
									<div class="col-md-6">
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-fw fa-info"></i></span>
												{{ Form::select('fleetType', $fleetTypes, reset($fleetTypes), array('id' => 'fleetType', 'class' => 'form-control')) }}
											</div>
									</div>
								</div>
								<!-- Text input-->
								<div class="form-group">
									<label class="col-md-4 control-label" for="srpCode">SRP Code</label>
									<div class="col-md-6">
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-fw fa-magic"></i></span>
												{{ Form::text('srpCode', null, array('id' => 'srpCode', 'class' => 'form-control input-md')) }}
											</div>
									</div>
								</div>
								<!-- Button -->
								<div class="form-group">
									<label class="col-md-4 control-label" for="singlebutton"></label>
									<div class="col-md-4">
										<button id="singlebutton" name="singlebutton" class="btn btn-primary">Create New Fleet</button>
									</div>
								</div>
							</fieldset>
						{{ Form::close()}}
					@else
						<p>You must have at least one character registered on seat to add a fleet.</p>
					@endif

				</div>
			</div><!-- box -->
		</div><!-- col-md-4 -->
	</div><!-- row -->

@stop

@section('javascript')

@stop
