@extends('layouts.masterLayout')

@section('html_title', 'Fleet Details')

@section('page_content')

	<div class="row">
		<div class="col-md-8">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">SRP Requests</h3>
				</div>
				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable">
						<thead>
							<tr>
							<th>Date</th>
							<th>Member</th>
							<th>Ship</th>
							<th>Value</th>
							<th>Status</th>
							<th>zKillboard</th>
							<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($requests as $request)
							<tr>
								<td>{{ $request->created_at }}</td>
								<td>{{ $request->characterName }}</td>
								<td>{{ $request->shipTypeName }}</td>
								<td>{{ App\Services\Helpers\Helpers::formatBigNumber($request->statusValue) }} ISK</td>
								<td><span class="label label-{{ $request->statusTypeTag }}">{{ $request->statusTypeName }}</span></td>
								<td><a href="https://zkillboard.com/kill/{{ $request->killID }}/" target="_blank"><span class="fa fa-external-link"></span> Click Here</a></td>
								<td><a href="{{ action('SrpController@getRequest', array('id' => $request->requestID)) }}" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i> Edit</a></td>
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
					<h3 class="box-title">Fleet Details</h3>
				</div>
				<div class="box-body">
					<div class="row">
						<label class="col-md-4">Date:</label>
						<span class="col-md-6">{{ $fleet->created_at }}</span>
					</div>
					<div class="row">
						<label class="col-md-4">Fleet Commander:</label>
						<span class="col-md-6">{{ $fleet->fleetCharacterName }}</span>
					</div>
					<div class="row">
						<label class="col-md-4">Fleet Type:</label>
						<span class="col-md-6">{{ $fleet->fleetTypeName }}</span>
					</div>
					<div class="row">
						<label class="col-md-4">SRP Code:</label>
						<span class="col-md-6">{{ $fleet->fleetSrpCode }}</span>
					</div>
					<div class="row">
						<label class="col-md-4">Total Requests:</label>
						<span class="col-md-6">{{ $fleet->totalRequests }}</span>
					</div>
				</div>
			</div><!-- /.box -->
		</div><!-- /.col-md-4 -->
	</div><!-- /.row -->

@stop
