@extends('layouts.masterLayout')

@section('html_title', 'Configure Ship SRP Value')

@section('page_content')

	<div class="box">
		<div class="box-header">
			<h3 class="box-title">{{ $shipType->shipTypeName }}</h3>
		</div>
		<div class="box-body table-responsive">
			{{ Form::open(array('action' => array('SrpController@postConfigureShipType', $shipType->shipTypeID), 'class' => 'form-horizontal')) }}
				<fieldset>
					<div class="form-group">
						<label class="col-md-4 control-label" for="srpValue">Default SRP Value</label>
						<div class="col-md-4">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-fw fa-magic"></i></span>
								{{ Form::text('srpValue', $shipType->shipTypeValue, array('id' => 'srpValue', 'class' => ' form-control', 'placeholder' => 'Default SRP Value'), 'required') }}
							</div>
						</div>
					</div>
					<!-- Button -->
					<div class="form-group">
						<label class="col-md-4 control-label" for="singlebutton"></label>
						<div class="col-md-4">
							{{ Form::submit('Update Ship', array('class' => 'btn bg-olive btn-block')) }}
						</div>
					</div>
				</fieldset>
			{{ Form::close() }}
		</div><!-- /.box-body -->
	</div><!-- /.box -->

@stop
