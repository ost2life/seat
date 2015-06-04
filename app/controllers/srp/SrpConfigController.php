<?php
/*
The MIT License (MIT)

Copyright (c) 2014 eve-seat

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

use Seat\Services\Helpers\SrpHelper;

class SrpConfigController extends \BaseController
{

	/*
	|--------------------------------------------------------------------------
	| __construct()
	|--------------------------------------------------------------------------
	|
	| Constructs the class.
	|
	*/
	public function __construct()
	{
		if (!SrpHelper::canConfigure()) {
			App::abort(404); }
	}


	/*
	|--------------------------------------------------------------------------
	| index()
	|--------------------------------------------------------------------------
	|
	| Display a listing of the resource.
	|
	*/
	public function index()
	{
		return View::make('srp.config.index')
			->with('total_doctrines', SrpDoctrine::all()->count())
			->with('total_fleet_types', SrpFleetType::all()->count())
			->with('total_ships', SrpShip::all()->count())
		;
	}


	/*
	|--------------------------------------------------------------------------
	| create()
	|--------------------------------------------------------------------------
	|
	| Show the form for creating a new resource.
	|
	*/
	public function create()
	{
		App::abort(404);
	}


	/*
	|--------------------------------------------------------------------------
	| store()
	|--------------------------------------------------------------------------
	|
	| Store a newly created resource in storage.
	|
	*/
	public function store()
	{
		App::abort(404);
	}


	/*
	|--------------------------------------------------------------------------
	| show()
	|--------------------------------------------------------------------------
	|
	| Display the specified resource.
	|
	*/
	public function show($id)
	{
		App::abort(404);
	}


	/*
	|--------------------------------------------------------------------------
	| edit()
	|--------------------------------------------------------------------------
	|
	| Show the form for editing the specified resource.
	|
	*/
	public function edit($id)
	{
		App::abort(404);
	}


	/*
	|--------------------------------------------------------------------------
	| update()
	|--------------------------------------------------------------------------
	|
	| Update the specified resource in storage.
	|
	*/
	public function update($id)
	{
		App::abort(404);
	}


	/*
	|--------------------------------------------------------------------------
	| destroy()
	|--------------------------------------------------------------------------
	|
	| Remove the specified resource from storage.
	|
	*/
	public function destroy($id)
	{
		App::abort(404);
	}

}
