@extends('layouts.masterLayout')

@section('html_title', 'SRP Request')

@section('page_content')

	<div class="row">
		@if ($canUpdate)
			<div class="col-md-8">
		@else
			<div class="col-md-12">
		@endif
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">{{ end($statuses)->characterName }} - {{ end($statuses)->shipTypeName }}</h3>
				</div>
				<div class="box-body">
					<table class="table table-condensed compact table-hover" id="datatable">
						<thead>
							<tr>
								<th>Date</th>
								<th>Updater</th>
								<th>Value</th>
								<th>Status</th>
								<th>Notes</th>
							</tr>
						</thead>
						<tbody>
							@foreach($statuses as $status)
							<tr>
								<td>{{ $status->created_at }}</td>
								<td>{{ $status->characterName }}</td>
								<td>{{ App\Services\Helpers\Helpers::formatBigNumber($status->statusValue) }} ISK</td>
								<td><span class="label label-{{ $status->statusTypeTag }}" >{{ $status->statusTypeName }}</span></td>
								<td>{{ $status->statusNotes }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
		</div><!-- /.col-md-8 -->
		@if ($canUpdate)
			<div class="col-md-4">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Update Status</h3>
					</div>
					<div class="box-body">
						@if (count($characters) > 0)
							{{ Form::open(array('action' => array('SrpController@postRequest', end($statuses)->requestID), 'class' => 'form-horizontal')) }}
								<fieldset>
									<!-- Text input-->
									<div class="form-group">
										<label class="col-md-4 control-label" for="updater">Updater</label>
										<div class="col-md-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-fw fa-user"></i></span>
												{{ Form::select('updater', $characters, $settings['main_character_id'] ? $settings['main_character_id'] : 0, array('id' => 'updater', 'class' => 'form-control')) }}
											</div>
										</div>
									</div>
									<!-- Text input-->
									<div class="form-group">
										<label class="col-md-4 control-label" for="status">Status</label>
										<div class="col-md-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-fw fa-cog"></i></span>
												{{ Form::select('status', $statusTypes, reset($statusTypes), array('id' => 'status', 'class' => 'form-control')) }}
											</div>
										</div>
									</div>
									<!-- Text input-->
									<div class="form-group">
										<label class="col-md-4 control-label" for="value">Value</label>
										<div class="col-md-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-fw fa-money"></i></span>
												{{ Form::text('value', reset($statuses)->statusValue, array('id' => 'value', 'class' => 'form-control input-md')) }}
											</div>
										</div>
									</div>
									<!-- Text input-->
									<div class="form-group">
										<label class="col-md-4 control-label" for="notes">Notes</label>
										<div class="col-md-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-fw fa-file-text"></i></span>
												{{ Form::text('notes', null, array('id' => 'notes', 'class' => 'form-control input-md')) }}
											</div>
										</div>
									</div>
									<!-- Button -->
									<div class="form-group">
										<label class="col-md-4 control-label" for="singlebutton"></label>
										<div class="col-md-4">
											<button id="singlebutton" name="singlebutton" class="btn btn-primary">Update Request</button>
										</div>
									</div>
								</fieldset>
							{{ Form::close()}}
						@else
							<p>You must have at least one character registered on seat to update an srp request.</p>
						@endif
					</div>
				</div><!-- /.box -->
			</div><!-- /.col-md-4 -->
		@endif
	</div><!-- /.row -->

@stop
