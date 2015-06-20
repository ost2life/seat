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

use App\Services\Validators\SrpRequestStatusValidator;
use App\Services\Validators\SrpRequestValidator;
use Seat\Services\Helpers\SrpHelper;

class SrpRequestController extends BaseController
{

	// Models
	protected $srp_character;
	protected $srp_fleet;
	protected $srp_request_status;
	protected $srp_request;
	protected $srp_status_type;

	// Validators
	protected $srp_request_status_validator;
	protected $srp_request_validator;

	/*
	|--------------------------------------------------------------------------
	| __construct()
	|--------------------------------------------------------------------------
	|
	| Constructs the class.
	|
	*/
	public function __construct(
		EveCharacterKillMails $eve_killmails,
		SrpCharacter $srp_character,
		SrpFleet $srp_fleet,
		SrpRequestStatus $srp_request_status,
		SrpRequest $srp_request,
		SrpStatusType $srp_status_type,
		SrpRequestStatusValidator $srp_request_status_validator,
		SrpRequestValidator $srp_request_validator)
	{
		// Models
		$this->eve_killmails = $eve_killmails;
		$this->srp_character = $srp_character;
		$this->srp_fleet = $srp_fleet;
		$this->srp_request_status = $srp_request_status;
		$this->srp_request = $srp_request;
		$this->srp_status_type = $srp_status_type;

		// Validators
		$this->srp_request_status_validator = $srp_request_status_validator;
		$this->srp_request_validator = $srp_request_validator;
	}


	/*
	|--------------------------------------------------------------------------
	| ownsRequest()
	|--------------------------------------------------------------------------
	|
	| Checks if a user owns an srp request.
	|
	*/
	public function ownsRequest(SrpRequest $request)
	{
		return in_array($request->characterID, $this->srp_character->self()->lists('characterID')) === true;
	}


	/*
	|--------------------------------------------------------------------------
	| findOrCreateKillmail($killID)
	|--------------------------------------------------------------------------
	|
	| Returns a saved killmail using $killID, or imports it from zKillboard.
	|
	*/
	public function findOrCreateKillmail($killID)
	{
		// Check for killmail on SeAT
		if ($killmail = $this->eve_killmails->where('killID', '=', $killID)->first()) {
			return $killmail; }

		// Check for killmail on zKillboard
		$zkb = Cache::has('zkb:' . $killID)
			? Cache::get('zkb:' . $killID)
			: json_decode(file_get_contents('https://zkillboard.com/api/killID/' . $killID), true);

		// Error fetching from zKillboard
		if (!is_array($zkb) || !count($zkb)) {
			return null; }

		// Cache killmail temporarily 
		Cache::put('zkb:' . $killID, $zkb, strtotime('+15 minutes'));

		// Insert killmail
		return DB::transaction(function() use($zkb) {
			// Killmail
			$model = new EveCharacterKillMails;
			$model->killID = $zkb[0]['killID'];
			$model->characterID = $zkb[0]['victim']['characterID'];

			$model->save();

			// Details
			$model = new EveCharacterKillMailDetail;
			$model->killID = $zkb[0]['killID'];
			$model->solarSystemID = $zkb[0]['solarSystemID'];
			$model->killTime = $zkb[0]['killTime'];
			$model->moonID = $zkb[0]['moonID'];

			$model->shipTypeID = $zkb[0]['victim']['shipTypeID'];
			$model->characterID = $zkb[0]['victim']['characterID'];
			$model->characterName = $zkb[0]['victim']['characterName'];
			$model->corporationID = $zkb[0]['victim']['corporationID'];
			$model->corporationName = $zkb[0]['victim']['corporationName'];
			$model->allianceID = $zkb[0]['victim']['allianceID'];
			$model->allianceName = $zkb[0]['victim']['allianceName'];
			$model->factionID = $zkb[0]['victim']['factionID'];
			$model->factionName = $zkb[0]['victim']['factionName'];
			$model->damageTaken = $zkb[0]['victim']['damageTaken'];

			$model->save();

			// Attackers
			foreach ($zkb[0]['attackers'] as $attacker) {
				$model = new EveCharacterKillMailAttackers;
				$model->killID = $zkb[0]['killID'];

				$model->characterID = $zkb[0]['victim']['characterID'];
				$model->characterName = $attacker['characterName'];
				$model->corporationID = $attacker['corporationID'];
				$model->corporationName = $attacker['corporationName'];
				$model->allianceID = $attacker['allianceID'];
				$model->allianceName = $attacker['allianceName'];
				$model->factionID = $attacker['factionID'];
				$model->factionName = $attacker['factionName'];
				$model->securityStatus = $attacker['securityStatus'];
				$model->damageDone = $attacker['damageDone'];
				$model->finalBlow = $attacker['finalBlow'];
				$model->weaponTypeID = $attacker['weaponTypeID'];
				$model->shipTypeID = $attacker['shipTypeID'];

				$model->save(); }

			// Items
			foreach ($zkb[0]['items'] as $item) {
				$model = new EveCharacterKillMailItems;
				$model->killID = $zkb[0]['killID'];

				$model->typeID = $item['typeID'];
				$model->flag = $item['flag'];
				$model->qtyDropped = $item['qtyDropped'];
				$model->qtyDestroyed = $item['qtyDestroyed'];
				$model->singleton = $item['singleton'];

				$model->save(); }

			return $this->eve_killmails->where('killID', '=', $zkb[0]['killID'])->first();
		});
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
		$requests = $this->srp_request
			->available($this->srp_character)
			->orderBy('created_at', 'DESC')
			->paginate(20);

		// Return
		return View::make('srp.request.index')
			->with('requests', $requests);
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
		return DB::transaction(function() {
			// Validate input
			if (!$this->srp_request_validator->passes()) {
				return Redirect::action('SrpRequestController@index')
					->withErrors($this->srp_request_validator->errors)
					->withInput(); }

			// Get the killmail
			preg_match('%^(?:http|https)://zkillboard.com/kill/(\d+)(?:/$|$)%', Input::get('killmail'), $matches);
			$killID = $matches[1];
			$killmail = $this->findOrCreateKillmail($killID);

			// Failed to get killmail
			if (!$killmail) {
				return Redirect::action('SrpRequestController@index')
					->withErrors('Unable to retreive the killmail.')
					->withInput(); }

			// Must own the character
			if (!in_array($killmail->characterID, $this->srp_character->self()->lists('characterID'))) {
				return Redirect::action('SrpRequestController@index')
					->withErrors('You must own the character on the killmail.')
					->withInput(); }

			// Fleet must exist
			if (!$fleet = $this->srp_fleet->where('code', '=', Input::get('code'))->first()) {
				return Redirect::action('SrpRequestController@index')
					->withErrors("No fleet exists with that srp code.")
					->withInput(Input::all()); }

			// Request must not exist
			if ($request = $this->srp_request->where('killID', '=', $killID)->first()) {
				return Redirect::action('SrpRequestController@index')
					->withErrors('A request was already made with this killmail.')
					->withInput(); }

			// Create request
			$request = $this->srp_request->create(array(
				'characterID' => $killmail->characterID,
				'fleetID' => $fleet->id,
				'killID' => $killmail->killID,
			));

			// Create request status
			SrpRequestStatus::create(array(
				'notes' => null,
				'value' => 0.00,
				'characterID' => $request->characterID,
				'requestID' => $request->id,
				'statusTypeID' => 2, // Evaluating
			));

			// Return
			return Redirect::action('SrpRequestController@show', $request->id)
				->with('success', 'Your request was created.');
		});
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
		// Request must exist
		if (!$request = $this->srp_request->find($id)) {
			return Redirect::action('SrpRequestController@index')
				->withErrors('That request does not exist.')
				->withInput(); }

		// Must have permission to review/pay or own the request
		if (!SrpHelper::canReview() && !SrpHelper::canPay() && !$this->ownsRequest($request)) {
			return Redirect::action('SrpRequestController@index')
				->withErrors('You are not allowed to do that.')
				->withInput(); }

		// View data
		$characters = $this->srp_character->self()->lists('characterName', 'characterID');
		$status_types = $this->srp_status_type->available()->lists('name', 'id');

		// Return
		return View::make('srp.request.show')
			->with('characters', $characters)
			->with('status_types', $status_types)
			->with('request', $request);
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
		// Request must exist
		if (!$request = $this->srp_request->find($id)) {
			return Redirect::action('SrpRequestController@index')
				->withErrors('That request does not exist.')
				->withInput(); }

		// Must have permission to review/pay
		if (!SrpHelper::canReview() && !SrpHelper::canPay()) {
			return Redirect::action('SrpRequestController@index')
				->withErrors('You are not allowed to do that.')
				->withInput(); }

		// Validate input
		if (!$this->srp_request_status_validator->passes()) {
			return Redirect::action('SrpRequestController@show', $id)
				->withErrors($this->srp_request_status_validator->errors)
				->withInput(); }

		// Update request status
		return DB::transaction(function() use($request) {
			// Update request status
			$this->srp_request_status->create(array(
				'notes' => Input::get('notes'),
				'value' => Input::get('value'),
				'characterID' => Input::get('updater'),
				'requestID' => $request->id,
				'statusTypeID' => Input::get('status'),
			));

			// Return
			return Redirect::action('SrpRequestController@show', $request->id)
				->with('success', 'The request was updated.');
		});
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
