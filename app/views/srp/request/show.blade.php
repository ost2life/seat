@extends('layouts.masterLayout')

@section('html_title', 'SRP Requests')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li><a href="{{ action('SrpRequestController@index') }}">Requests</a></li>
			<li class="active">{{ $request->character->characterName }} ({{ $request->ship()->first()->typeName }})</li>
		</ol>
	</div>

	<div class="row">

		@if (Seat\Services\Helpers\SrpHelper::canReview() || Seat\Services\Helpers\SrpHelper::canPay())
			<div class="col-md-7">
		@else
			<div class="col-md-12">
		@endif
			<div class="box">

				<div class="box-header">
					<h3 class="box-title">Request Status</h3>
				</div>

				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
						<thead>
							<tr>
								<th>Last Updated</th>
								<th>Updater</th>
								<th>Ship</th>
								<th>Value</th>
								<th>Status</th>
								<th>zKillboard Link</th>
								<th width="99%">Notes</th>
							</tr>
						</thead>
						<tbody>
							@foreach($request->statuses()->orderBy('created_at', 'DESC')->get() as $status)
							<tr>
								<td>{{ $status->created_at }}</td>
								<td>{{ $status->character->characterName }}</td>
								<td>{{ $request->ship()->first()->typeName }}</td>
								<td>{{ App\Services\Helpers\Helpers::formatBigNumber($status->value) }} ISK</td>
								<td><span class="label label-{{ $status->type->tag }}" >{{ $status->type->name }}</span></td>
								<td><a href="https://zkillboard.com/kill/{{ $request->killID }}/" target="_blank"><i class="fa fa-external-link"></i> Click Here</a></td>
								<td>{{ $status->notes }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->

			</div><!-- /.box -->

		</div><!-- /.col -->

		@if (Seat\Services\Helpers\SrpHelper::canReview() || Seat\Services\Helpers\SrpHelper::canPay())
			<div class="col-md-5">

				<div class="box">

					<div class="box-header">
						<h3 class="box-title">Update Request Status</h3>
					</div>

					<div class="box-body">
						@if (count($characters) > 0)
							{{ Form::open(array('action' => array('SrpRequestController@update', $request->id), 'method' => 'PUT', 'class' => 'form-horizontal')) }}
								<fieldset>
									<!-- Select Input-->
									<div class="form-group">
										<label class="col-md-4 control-label" for="updater">Updater</label>
										<div class="col-md-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-fw fa-user"></i></span>
												{{ Form::select('updater', $characters, $settings['main_character_id'] ? $settings['main_character_id'] : 0, array('class' => 'form-control')) }}
											</div>
										</div>
									</div>
									<!-- Text Input-->
									<div class="form-group">
										<label class="col-md-4 control-label" for="status">Status</label>
										<div class="col-md-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-fw fa-cog"></i></span>
												{{ Form::select('status', $status_types, reset($status_types), array('class' => 'form-control')) }}
											</div>
										</div>
									</div>
									<!-- Text Input-->
									<div class="form-group">
										<label class="col-md-4 control-label" for="value">Value</label>
										<div class="col-md-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-fw fa-money"></i></span>
												{{ Form::text('value', $request->statuses()->orderBy('created_at', 'DESC')->first()->value, array('id' => 'value', 'class' => 'form-control input-md')) }}
											</div>
										</div>
									</div>
									<!-- Text Input-->
									<div class="form-group">
										<label class="col-md-4 control-label" for="notes">Notes</label>
										<div class="col-md-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-fw fa-file-text"></i></span>
												{{ Form::text('notes', null, array('class' => 'form-control input-md')) }}
											</div>
										</div>
									</div>
									<!-- Button -->
									<div class="form-group">
										<label class="col-md-4 control-label"></label>
										<div class="col-md-6">
											<button class="btn btn-block btn-primary">Update</button>
										</div>
									</div>
								</fieldset>
							{{ Form::close()}}
						@else
							<p>You must have at least one character registered on SeAT to update an srp request.</p>
						@endif
					</div><!-- /.box-body -->

				</div><!-- /.box -->

			</div><!-- /.col -->
		@endif

	</div><!-- /.row -->

	@if (Seat\Services\Helpers\SrpHelper::canReview() || Seat\Services\Helpers\SrpHelper::canPay())
		<div class="row">

			<div class="col-md-7">

				<div class="box">

					<div class="box-header">
						<h3 class="box-title">Fleet Doctrine</h3>
					</div>

					<div class="box-body">
						<table class="table table-condensed compact table-hover" id="datatable" style="white-space: nowrap;">
							<thead>
								<tr>
									<th>Doctrine</th>
									<th>Name</th>
									<th>Value</th>
									<th></th>
									<th width="99%"></th>
								</tr>
							</thead>
							<tbody>
								@foreach($request->fleet->doctrines as $doctrine)
									@foreach($doctrine->ships as $ship)
										<tr>
											<td>{{ $doctrine->name }}</td>
											<td>{{ $ship->type->typeName }}</td>
											<td>{{ App\Services\Helpers\Helpers::formatBigNumber($ship->value) }} ISK</td>
											<td><button item-value="{{ $ship->value }}" class="btn btn-xs btn-primary copy-item">Use Value</button></td>
											<td></td>
										</tr>
									@endforeach
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
							<span class="col-md-6">{{ $request->fleet->created_at }}</span>
						</div>
						<div class="row">
							<label class="col-md-4">Fleet Commander:</label>
							<span class="col-md-6">{{ $request->fleet->character->characterName }}</span>
						</div>
						<div class="row">
							<label class="col-md-4">Fleet Type:</label>
							<span class="col-md-6">{{ $request->fleet->type->name }}</span>
						</div>
						<div class="row">
							<label class="col-md-4">SRP Code:</label>
							<span class="col-md-6">{{ $request->fleet->code }}</span>
						</div>
						<div class="row">
							<label class="col-md-4">Total Requests:</label>
							<span class="col-md-6">{{ $request->fleet->requests->count() }}</span>
						</div>
					</div>
				</div><!-- /.box -->

			</div><!-- /.col -->

		</div><!-- /.row -->
	@endif

@stop

@section('javascript')

	<script type="text/javascript">
		$(document).on("click", ".copy-item", function(e) {
			var value = $(this).attr("item-value");
			$("#value")[0].value = value;
		});
	</script>

@stop
