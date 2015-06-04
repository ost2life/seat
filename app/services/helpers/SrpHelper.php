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

namespace Seat\Services\Helpers;

class SrpHelper
{

	/*
	|--------------------------------------------------------------------------
	| getCharacters()
	|--------------------------------------------------------------------------
	|
	| Returns an array of charactrers registered on seat.
	|
	*/
	public static function getCharacters($all = false)
	{
		if (!!$all) {
			return \EveAccountAPIKeyInfoCharacters::all(); }

		else {
			return \EveAccountAPIKeyInfoCharacters::whereIn('keyID', \Auth::user()->keys()->lists('keyID'))->get(); }
	}

	/*
	|--------------------------------------------------------------------------
	| canAdministrate()
	|--------------------------------------------------------------------------
	|
	| Checks if a user can administrate the srp.
	|
	*/
	public static function canConfigure()
	{
		return \Auth::isSuperUser() || \Auth::hasAccess('srp_configure') == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| canCommand()
	|--------------------------------------------------------------------------
	|
	| Checks if a user can command fleets.
	|
	*/
	public static function canCommand()
	{
		return \Auth::isSuperUser() || \Auth::hasAccess('srp_command') == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| canReview()
	|--------------------------------------------------------------------------
	|
	| Checks if a user can review srp requests.
	|
	*/
	public static function canReview()
	{
		return \Auth::isSuperUser() || \Auth::hasAccess('srp_review') == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| canPay()
	|--------------------------------------------------------------------------
	|
	| Checks if a user can pay srp requests.
	|
	*/
	public static function canPay()
	{
		return \Auth::isSuperUser() || \Auth::hasAccess('srp_pay') == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| getAvailableFleetTypes()
	|--------------------------------------------------------------------------
	|
	| Returns the available fleet types the user can use.
	|
	*/
	public static function getAvailableFleetTypes()
	{
		return !!self::canCommand()
			? \SrpFleetType::all()
			: \SrpFleetType::where('public', '=', true)->get();
	}

	/*
	|--------------------------------------------------------------------------
	| getAvailableStatusTypes()
	|--------------------------------------------------------------------------
	|
	| Returns the available status types the user can use.
	|
	*/
	public static function getAvailableStatusTypes()
	{
		// Evaluating, Rejected, Approved, Paid
		if (self::canPay()) {
			return \SrpStatusType::whereIn('id', array(2, 3, 4, 5))->get(); }

		// Evaluating, Rejected, Approved
		else if (self::canReview()) {
			return \SrpStatusType::whereIn('id', array(2, 3, 4))->get(); }

		else {
			return \SrpStatusType::whereIn('id', array(0))->get(); }
	}

	/*
	|--------------------------------------------------------------------------
	| ownsFleet()
	|--------------------------------------------------------------------------
	|
	| Checks if a user owns a fleet.
	|
	*/
	public static function ownsFleet(\SrpFleet $fleet)
	{
		$characters = self::getCharacters()->lists('characterID');
		return in_array($fleet->characterID, $characters) == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| ownsRequest()
	|--------------------------------------------------------------------------
	|
	| Checks if a user owns an srp request.
	|
	*/
	public static function ownsRequest(\SrpRequest $request)
	{
		$characters = self::getCharacters()->lists('characterID');
		return in_array($request->characterID, $characters) == 1;
	}

	/*
	|--------------------------------------------------------------------------
	| findOrCreateKillmail($killID)
	|--------------------------------------------------------------------------
	|
	| Returns a saved killmail using $killID, or imports it from zKillboard.
	|
	*/
	public static function findOrCreateKillmail($killID)
	{
		// Return the killmail from seat if it exists
		if ($killmail = \EveCharacterKillMails::where('killID', '=', $killID)->first()) {
			return $killmail; }

		// Fetch the killmail from zKillboard
		$zkb = \Cache::has('zkb:' . $killID)
			? \Cache::get('zkb:' . $killID)
			: json_decode(file_get_contents('https://zkillboard.com/api/killID/' . $killID), true);

		if (!is_array($zkb)) {
			throw new \Exception('Unable to fetch the killmail from zKillboard.', 11223344); }

		// Cache the killmail temporarily 
		\Cache::put('zkb:' . $killID, $zkb, strtotime('+15 minutes'));

		// Insert the killmail into the database
		\DB::transaction(function() use($zkb) {
			// killmail
			$model = new \EveCharacterKillMails;
			$model->killID = $zkb[0]['killID'];
			$model->characterID = $zkb[0]['victim']['characterID'];

			$model->save();

			// killmail detail
			$model = new \EveCharacterKillMailDetail;
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

			// killmail attackers
			foreach ($zkb[0]['attackers'] as $attacker) {
				$model = new \EveCharacterKillMailAttackers;
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

			// killmail items
			foreach ($zkb[0]['items'] as $item) {
				$model = new \EveCharacterKillMailItems;
				$model->killID = $zkb[0]['killID'];

				$model->typeID = $item['typeID'];
				$model->flag = $item['flag'];
				$model->qtyDropped = $item['qtyDropped'];
				$model->qtyDestroyed = $item['qtyDestroyed'];
				$model->singleton = $item['singleton'];

				$model->save(); }
		});

		return \EveCharacterKillMails::where('killID', '=', $killID)->first();
	}

}
