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

use App\Services\Validators\SrpDoctrineValidator;

class SrpDoctrineController extends BaseController
{

	// Models
	protected $srp_doctrine;
	protected $srp_ship;

	// Validators
	protected $srp_doctrine_validator;

	/*
	|--------------------------------------------------------------------------
	| __construct()
	|--------------------------------------------------------------------------
	|
	| Constructs the class.
	|
	*/
	public function __construct(
		SrpDoctrine $srp_doctrine,
		SrpShip $srp_ship,
		SrpDoctrineValidator $srp_doctrine_validator)
	{
		// Models
		$this->srp_doctrine = $srp_doctrine;
		$this->srp_ship = $srp_ship;

		// Validators
		$this->srp_doctrine_validator = $srp_doctrine_validator;

		// Must have permission to configure
		if (!Auth::hasAccess('srp_configure')) {
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
		// View data
		$doctrines = $this->srp_doctrine->all();

		// Return
		return View::make('srp.config.doctrine.index')
			->with('doctrines', $doctrines);
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
		// Validate input
		if (!$this->srp_doctrine_validator->passes()) {
			return Redirect::action('SrpDoctrineController@index')
				->withErrors($this->srp_doctrine_validator->errors)
				->withInput(); }

		// Check if name is free
		$doctrine = $this->srp_doctrine
			->where('name', '=', Input::get('name'))
			->withTrashed()
			->first();

		if ($doctrine && !$doctrine->trashed()) {
			return Redirect::action('SrpDoctrineController@index')
				->withErrors("The doctrine '{$doctrine->name}' already exists.")
				->withInput(); }

		// Restore
		if ($doctrine && $doctrine->trashed()) {
			$doctrine->fill(array(
				'name' => Input::get('name'),
			));
			$doctrine->restore(); }

		// Create
		else {
			$doctrine = $this->srp_doctrine->create(array(
				'name' => Input::get('name'),
			)); }

		// Return
		return Redirect::action('SrpDoctrineController@index')
			->with('success', "The doctrine '{$doctrine->name}' was created.");
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
		// Doctrine must exist
		if (!$doctrine = $this->srp_doctrine->find($id)) {
			return Redirect::action('SrpDoctrineController@index')
				->withErrors('That doctrine does not exist.')
				->withInput(); }

		// Ships not attached to doctrine
		$ships = $this->srp_ship->whereNotIn('id',
			!!$doctrine->ships->count()
			? $doctrine->ships->lists('id')
			: array(0)
		)->get();

		// Return
		return View::make('srp.config.doctrine.show')
			->with('doctrine', $doctrine)
			->with('ships', $ships);
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
		// Doctrine must exist
		if (!$doctrine = $this->srp_doctrine->find($id)) {
			return Redirect::action('SrpDoctrineController@index')
				->withErrors('That doctrine does not exist.')
				->withInput(); }

		// Attach ship
		if (Input::has('ship')) {
			// Validate input
			if (!$this->srp_doctrine_validator->passes()) {
				return Redirect::action('SrpDoctrineController@show', $id)
					->withErrors($this->srp_doctrine_validator->errors)
					->withInput(); }

			// Ship must exist
			if (!$ship = $this->srp_ship->find(Input::get('ship'))) {
				return Redirect::action('SrpDoctrineController@show', $id)
					->withErrors('That ship does not exist.')
					->withInput(); }

			// Attach ship
			if (!$doctrine->ships->contains($ship)) {
				$doctrine->ships()->attach($ship); }

			// Return
			return Redirect::action('SrpDoctrineController@show', $id)
				->with('success', " The ship '{$ship->name} ({$ship->type()->first()->typeName})' has been assigned to this doctrine."); }

		// Update doctrine
		else if (Input::has('name')) {
			// Validate input
			if (!$this->srp_doctrine_validator->passes()) {
				return Redirect::action('SrpDoctrineController@show', $id)
					->withErrors($this->srp_doctrine_validator->errors)
					->withInput(); }

			// Update doctrine
			$doctrine->fill(Input::all());
			$doctrine->save();

			// Return
			return Redirect::action('SrpDoctrineController@show', $id)
				->with('success', "This doctrine has been updated."); }
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
		// Doctrine must exist
		if (!$doctrine = $this->srp_doctrine->find($id)) {
			return Redirect::action('SrpDoctrineController@index')
				->withErrors('That doctrine does not exist.')
				->withInput(); }

		// Detach ship
		if (Input::has('ship')) {
			// Validate input
			if (!$this->srp_doctrine_validator->passes()) {
				return Redirect::action('SrpDoctrineController@show', $id)
					->withErrors($this->srp_doctrine_validator->errors)
					->withInput(); }

			// Ship must exist
			if (!$ship = $this->srp_ship->find(Input::get('ship'))) {
				return Redirect::action('SrpDoctrineController@show', $id)
					->withErrors('That ship does not exist.')
					->withInput(); }

			// Detach ship
			if ($doctrine->ships->contains($ship)) {
				$doctrine->ships()->detach($ship); }

			// Return
			return Redirect::action('SrpDoctrineController@show', $id)
				->with('success', "The ship '{$ship->name} ({$ship->type()->first()->typeName})' has been unassigned from this doctrine."); }

		// Delete doctrine
		else {
			// Delete doctrine
			$doctrine->delete();

			// Return
			Session::flash('success', "The doctrine '{$doctrine->name}' has been deleted.");
			return Response::make(null, 204); }
	}

}
