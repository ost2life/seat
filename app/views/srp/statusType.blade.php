@extends('layouts.masterLayout')

@section('html_title', 'Edit Status Type')

@section('page_content')

	<div class="box">
		<div class="box-header">
			<h3 class="box-title">{{ $statusType->statusTypeName }}</h3>
		</div>
		<div class="box-body table-responsive">
			{{ Form::open(array('action' => array('SrpController@postUpdateFleetType', $statusType->statusTypeID), 'class' => 'form-horizontal')) }}
				<fieldset>
					<div class="form-group">
						<label class="col-md-4 control-label" for="statusTypeName">Status Type Name</label>
						<div class="col-md-4">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-fw fa-magic"></i></span>
								{{ Form::text('statusTypeName', $statusType->statusTypeName, array('id' => 'statusTypeName', 'class' => ' form-control', 'placeholder' => 'Status Type Name'), 'required') }}
							</div>
						</div>
					</div>
					<!-- Button -->
					<div class="form-group">
						<label class="col-md-4 control-label" for="singlebutton"></label>
						<div class="col-md-4">
							{{ Form::submit('Update Status Type', array('class' => 'btn bg-olive btn-block')) }}
						</div>
					</div>
				</fieldset>
			{{ Form::close() }}
		</div><!-- /.box-body -->
	</div><!-- /.box -->

@stop
