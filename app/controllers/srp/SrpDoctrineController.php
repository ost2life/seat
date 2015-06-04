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

class SrpDoctrineController extends \BaseController
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
		return view::make('srp.config.doctrine.index')
			->with('doctrines', SrpDoctrine::all())
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
		$validator = Validator::make(Input::all(), array(
			'name' => 'required|min:3|max:20',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		try {
			$doctrine = SrpDoctrine::create(Input::all());
			Session::flash('success', "{$doctrine->name} has been created."); }

		catch (PDOException $e) { switch ($e->getCode()) {

			case 23000: // Integrity Constraint Violation
				$doctrine = SrpDoctrine::withTrashed()->where('name', '=', Input::get('name'))->first();
				if ($doctrine->trashed()) {
					$doctrine->restore();
					Session::flash('success', "{$doctrine->name} has been restored.");
					break; }

				return Redirect::back()
					->withErrors("{$doctrine->name} already exists.")
					->withInput();

				default:
					return Redirect::back()
						->withErrors('A database error has occurred.')
						->withInput(); } }

		catch (Exception $e) {
			return Redirect::back()
				->withErrors('An unknown error has occurred.')
				->withInput(); }

		return Redirect::back();
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
		if(!$doctrine = SrpDoctrine::find($id)) {
			App::abort(404); }

		$assigned_ships = $doctrine->ships()->get();
		$available_ships = SrpShip::whereNotIn('id', $assigned_ships->count()
			? $assigned_ships->lists('id')
			: array(0))->get();

		return view::make('srp.config.doctrine.show')
			->with('available_ships', $available_ships)
			->with('doctrine', $doctrine)
		;
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
		$validator = Validator::make(Input::all(), array(
			'ship' => 'required_without:name|integer|min:1',
			'name' => 'required_without:ship|min:3|max:20',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		if(!$doctrine = SrpDoctrine::find($id)) {
			App::abort(404); }

		if (Input::exists('ship') && !$ship = SrpShip::find(Input::get('ship'))) {
			App::abort(404); }

		try {
			// Attach ship
			if (Input::exists('ship')) {
				$doctrine->ships()->attach($ship);
				Session::flash('success', "{$ship->name} ({$ship->type()->first()->typeName}) has been assigned to {$doctrine->name}."); }

			// Update doctrine
			else {
				$doctrine->fill(Input::all());
				$doctrine->save();
				Session::flash('success', "{$doctrine->name} has been updated."); } }

		catch (PDOException $e) { switch ($e->getCode()) {

			case 23000: // Integrity Constraint Violation
				return Redirect::back()
					->withErrors("{$doctrine->name} already exists.")
					->withInput();

				default:
					return Redirect::back()
						->withErrors('A database error has occurred.')
						->withInput(); } }

		catch (Exception $e) {
			return Redirect::back()
				->withErrors('An unknown error has occurred.')
				->withInput(); }

		return Redirect::back();
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
		$validator = Validator::make(Input::all(), array(
			'ship' => 'integer|min:1',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		if (!$doctrine = SrpDoctrine::find($id)) {
			App::abort(404); }

		if (Input::exists('ship') && !$ship = SrpShip::find(Input::get('ship'))) {
			App::abort(404); }

		// Detach ship
		if (Input::exists('ship')) {
			$doctrine->ships()->detach($ship);
			Session::flash('success', "{$ship->name} ({$ship->type()->first()->typeName}) has been unassigned from {$doctrine->name}.");
			return Redirect::back(); }

		// Delete doctrine
		else {
			$doctrine->delete();
			Session::flash('success', "{$doctrine->name} has been deleted.");
			return $this->index(); }
	}

}
