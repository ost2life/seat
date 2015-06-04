@extends('layouts.masterLayout')

@section('html_title', 'SRP Configuration')

@section('page_content')

	<div class="row">
		<ol class="breadcrumb">
			<li><a>SRP</a></li>
			<li class="active">Configure</li>
		</ol>
	</div>

	<div class="row">

		@include('srp.config.squares')

	</div><!-- /.row -->

@stop
