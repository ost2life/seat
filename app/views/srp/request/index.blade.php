@extends('layouts.masterLayout')

@section('html_title', 'SRP Requests')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li class="active">Requests</li>
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
								<th>Date Lost</th>
								<th width="99%">Pilot</th>
								<th>Ship</th>
								<th>Value</th>
								<th>Status</th>
								<th>zKillboard Link</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach($requests as $request)
							<tr>
								<td>{{ $request->statuses()->orderBy('created_at', 'DESC')->first()->created_at }}</td>
								<td>{{ $request->killmail->detail->killTime }}</td>
								<td>{{ $request->character->characterName }}</td>
								<td>{{ $request->ship()->first()->typeName }}</td>
								<td>{{ App\Services\Helpers\Helpers::formatBigNumber($request->statuses()->orderBy('created_at', 'DESC')->first()->value) }} ISK</td>
								<td><span class="label label-{{ $request->statuses()->orderBy('created_at', 'DESC')->first()->type()->first()->tag }}" >{{ $request->statuses()->orderBy('created_at', 'DESC')->first()->type()->first()->name }}</span></td>
								<td><a href="https://zkillboard.com/kill/{{ $request->killID }}/" target="_blank"><i class="fa fa-external-link"></i> Click Here</a></td>
								<td><a href="{{ action('SrpRequestController@show', array($request->id)) }}" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> Details</a></td>
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
					<h3 class="box-title">Create SRP Request</h3>
				</div>

				<div class="box-body">
					{{ Form::open(array('action' => 'SrpRequestController@store', 'class' => 'form-horizontal')) }}
						<fieldset>
							<!-- Text Input-->
							<div class="form-group">
								<label class="col-md-4 control-label" for="killmail"><a href="https://www.zkillboard.com/" target="_blank"><i class="fa fa-external-link"></i> zKb Killmail</a></label>
								<div class="col-md-6">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-link"></i></span>
										{{ Form::text('killmail', null, array('class' => 'form-control input-md')) }}
									</div>
								</div>
							</div>
							<!-- Text Input-->
							<div class="form-group">
								<label class="col-md-4 control-label" for="code">SRP Code</label>
								<div class="col-md-6">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-pencil"></i></span>
										{{ Form::text('code', null, array('class' => 'form-control input-md')) }}
								 </div>
								</div>
							</div>
							<!-- Button -->
							<div class="form-group">
								<label class="col-md-4 control-label" for="singlebutton"></label>
								<div class="col-md-6">
									<button id="singlebutton" name="singlebutton" class="btn btn-block btn-primary">Create</button>
								</div>
							</div>
						</fieldset>
					{{ Form::close()}}
				</div>
			</div><!-- /.box -->

		</div><!-- /.col -->

	</div><!-- /.row -->

@stop
