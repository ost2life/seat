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

class SrpRequestController extends \BaseController
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
		$characters = SrpHelper::getCharacters()->lists('characterID');
		$requests = SrpHelper::canReview() || SrpHelper::canPay()
			? SrpRequest::orderBy('created_at', 'DESC')->get()
			: SrpRequest::whereIn('characterID', $characters)->orderBy('created_at', 'DESC')->get();

		return View::make('srp.request.index')
			->with('requests', $requests)
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
			'killmail' => array('required', 'regex:%^(?:http|https)://zkillboard.com/kill/\d+(?:/$|$)%'),
			'code' => 'required|min:3|max:20',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		if (!$fleet = SrpFleet::where('code', '=', Input::get('code'))->first()) {
			return Redirect::back()
				->withErrors("No fleet exists with that srp code.")
				->withInput(Input::all()); }

		// Get the killID from the killmail link
		preg_match('%^(?:http|https)://zkillboard.com/kill/(\d+)(?:/$|$)%', Input::get('killmail'), $matches);
		$killID = $matches[1];
		$requestID = null;

		try { DB::transaction(function() use($fleet, $killID, &$requestID) {
			$killmail = SrpHelper::findOrCreateKillmail($killID);

			// Verify that the user owns the character
			if (!in_array($killmail->characterID, SrpHelper::getCharacters()->lists('characterID'))) {
				throw new Exception('Only the character owner may post their killmails.', 11223344); }

			// Create the request
			$request = SrpRequest::create(array(
				'characterID' => $killmail->characterID,
				'fleetID' => $fleet->id,
				'killID' => $killmail->killID,
			));

			// Create the initial request status
			SrpRequestStatus::create(array(
				'notes' => null,
				'value' => 0.00,
				'characterID' => $request->characterID,
				'requestID' => $request->id,
				'statusTypeID' => 2, // Evaluating
			));

			$requestID = $request->id;
		}); }

		catch (PDOException $e) { switch ($e->getCode()) {

			case 23000: // Integrity Constraint Violation
				return Redirect::back()
					->withErrors('An srp request has already been made using that killmail.')
					->withInput();

				default:
					return Redirect::back()
						->withErrors('A database error has occurred.')
						->withInput(); } }

		catch (Exception $e) { switch ($e->getCode()) {

			case 11223344:
				return Redirect::back()
					->withErrors($e->getMessage())
					->withInput();

			default:
				return Redirect::back()
					->withErrors('An unknown error has occurred.')
					->withInput(); } }

		Session::flash('success', 'Your srp request has been created.');
		return Redirect::route('srp.request.show', array($requestID));
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
		if (!$request = SrpRequest::find($id)) {
			App::abort(404); }

		if (!SrpHelper::canReview() && !SrpHelper::canPay() && !SrpHelper::ownsRequest($request)) {
			App::abort(404); }

		$characters = array_flip(SrpHelper::getCharacters()->lists('characterID', 'characterName'));
		$status_types = array_flip(SrpHelper::getAvailableStatusTypes()->lists('id', 'name'));

		return View::make('srp.request.show')
			->with('characters', $characters)
			->with('status_types', $status_types)
			->with('request', $request)
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
		if (!$request = SrpRequest::find($id)) {
			App::abort(404); }

		if (!SrpHelper::canReview() && !SrpHelper::canPay() && !SrpHelper::ownsRequest($request)) {
			App::abort(404); }

		$validator = Validator::make(Input::all(), array(
			'updater' => 'required|integer',
			'status' => 'required|integer',
			'value' => 'required|numeric',
			'notes' => 'min:3|max:200',
		));

		if ($validator->fails()) {
			return Redirect::back()
				->withErrors($validator->errors())
				->withInput(Input::all()); }

		try { DB::transaction(function() use($request) {
			SrpRequestStatus::create(array(
				'notes' => Input::get('notes'),
				'value' => Input::get('value'),
				'characterID' => Input::get('updater'),
				'requestID' => $request->id,
				'statusTypeID' => Input::get('status'),
			));
		}); }

		catch (PDOException $e) {
			return Redirect::back()
				->withErrors('A database error has occurred.')
				->withInput(); }

		catch (Exception $e) {
			return Redirect::back()
				->withErrors('An unknown error has occurred.')
				->withInput(); }

		Session::flash('success', 'The srp request has been updated.');
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
		App::abort(404);
	}

}
