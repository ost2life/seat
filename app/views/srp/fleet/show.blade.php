@extends('layouts.masterLayout')

@section('html_title', 'SRP Fleets')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li><a href="{{ action('SrpFleetController@index') }}">Fleets</a></li>
			<li class="active">{{ $fleet->character->characterName }} ({{ $fleet->code }})</li>
		</ol>
	</div>

	<div class="row">

		<div class="col-md-7">

			<div class="box">
				<div class="box-header">
					<h3 class="box-title">SRP Requests</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
							<th>Last Updated</th>
							<th width="99%">Pilot</th>
							<th>Ship</th>
							<th>Value</th>
							<th>Status</th>
							<th>zKillboard</th>
							@if (Seat\Services\Helpers\SrpHelper::canReview() || Seat\Services\Helpers\SrpHelper::canPay())
								<th></th>
							@endif
							</tr>
						</thead>
						<tbody>
							@foreach($requests as $request)
							<tr>
								<td>{{ $request->created_at }}</td>
								<td>{{ $request->character->characterName }}</td>
								<td>{{ $request->ship()->first()->typeName }}</td>
								<td>{{ App\Services\Helpers\Helpers::formatBigNumber($request->statuses()->orderBy('created_at', 'DESC')->first()->value) }} ISK</td>
								<td><span class="label label-{{ $request->statuses()->orderBy('created_at', 'DESC')->first()->type()->first()->tag }}">{{ $request->statuses()->orderBy('created_at', 'DESC')->first()->type()->first()->name }}</span></td>
								<td><a href="https://zkillboard.com/kill/{{ $request->killID }}/" target="_blank"><span class="fa fa-external-link"></span> Click Here</a></td>
								@if (Seat\Services\Helpers\SrpHelper::canReview() || Seat\Services\Helpers\SrpHelper::canPay())
									<td><a href="{{ action('SrpRequestController@show', array('id' => $request->id)) }}" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> Details</a></td>
								@endif
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
					<h3 class="box-title">Fleet Details</h3>
				</div>

				<div class="box-body">
					<div class="row">
						<label class="col-md-4">Created:</label>
						<span class="col-md-6">{{ $fleet->created_at }}</span>
					</div>
					<div class="row">
						<label class="col-md-4">Fleet Commander:</label>
						<span class="col-md-6">{{ $fleet->character->characterName }}</span>
					</div>
					<div class="row">
						<label class="col-md-4">Fleet Type:</label>
						<span class="col-md-6">{{ $fleet->type->name }}</span>
					</div>
					<div class="row">
						<label class="col-md-4">SRP Code:</label>
						<span class="col-md-6">{{ $fleet->code }}</span>
					</div>
					<div class="row">
						<label class="col-md-4">Total Requests:</label>
						<span class="col-md-6">{{ $fleet->requests->count() }}</span>
					</div>
				</div>
			</div><!-- /.box -->

		</div><!-- /.col -->

	</div><!-- ./row -->

	<hr>

	<div class="row">

		<div class="col-md-6">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Assigned Doctrines</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th style="width: 99%;">Name</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($fleet->doctrines as $doctrine)
							<tr>
								<td>{{ $doctrine->name }}</td>
								<td>
									{{ Form::open(array('action' => array('SrpFleetController@destroy', $fleet->id), 'method' => 'DELETE')) }}
										{{ Form::hidden('doctrine', $doctrine->id) }}
										<button class="btn btn-default btn-xs pull-right"><i class="fa fa-angle-double-right"></i></button>
									{{ Form::close() }}
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->

			</div><!-- /.box -->

		</div><!-- /.col -->

		<div class="col-md-6">

			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Available Doctrines</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th></th>
								<th style="width: 99%;">Name</th>
							</tr>
						</thead>
						<tbody>
							@foreach($available_doctrines as $doctrine)
							<tr>
								<td>
									{{ Form::open(array('action' => array('SrpFleetController@update', $fleet->id), 'method' => 'PUT')) }}
										{{ Form::hidden('doctrine', $doctrine->id) }}
										<button class="btn btn-default btn-xs pull-left"><i class="fa fa-angle-double-left"></i></button>
									{{ Form::close() }}
								</td>
								<td>{{ $doctrine->name }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->

			</div><!-- /.box -->

		</div><!-- /.col -->

	</div><!-- /.row -->

@stop
