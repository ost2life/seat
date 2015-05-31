@extends('layouts.masterLayout')

@section('html_title', 'All Requests')

@section('page_content')

	<div class="row">
		<div class="col-md-8">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Requests</h3>
				</div>
				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable">
						<thead>
							<tr>
								<th>Last Updated</th>
								<th>Date Lost</th>
								<th>Pilot</th>
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
								<td>{{ $request->created_at }}</td>
								<td>{{ $request->killTime }}</td>
								<td>{{ $request->characterName }}</td>
								<td>{{ $request->shipTypeName }}</td>
								<td>{{ App\Services\Helpers\Helpers::formatBigNumber($request->statusValue) }} ISK</td>
								<td><span class="label label-{{ $request->statusTypeTag }}" >{{ $request->statusTypeName }}</span></td>
								<td><a href="https://zkillboard.com/kill/{{ $request->killID }}/" target="_blank"><span class="fa fa-external-link"></span> Click Here</a></td>
								<td><a href="{{ action('SrpController@getRequest', array('id' => $request->requestID)) }}" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> Details</a></td>
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
					<h3 class="box-title">Create New Request</h3>
				</div>
				<div class="box-body">
					{{ Form::open(array('action' => 'SrpController@postRequests', 'class' => 'form-horizontal')) }}
						<fieldset>
							<!-- Text input-->
							<div class="form-group">
								<label class="col-md-4 control-label" for="zkillLink">zKillboard Link</label>
								<div class="col-md-6">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-fw fa-link"></i></span>
										{{ Form::text('zkillLink', null, array('id' => 'zkillLink', 'class' => 'form-control input-md')) }}
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
									<button id="singlebutton" name="singlebutton" class="btn btn-primary">Create SRP Request</button>
								</div>
							</div>
						</fieldset>
					{{ Form::close()}}
				</div>
			</div><!-- /.box -->
		</div><!-- /.col-md-4 -->
	</div><!-- /.row -->

@stop

@section('javascript')

@stop
