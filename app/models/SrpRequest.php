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

class SrpRequest extends Eloquent
{
	protected $fillable   = array('characterID', 'fleetID', 'killID');
	protected $table      = 'srp_requests';
	protected $primaryKey = 'id';
	protected $softDelete = true;

	public function scopeAvailable($query, SrpCharacter $character)
	{
		if (SrpHelper::canReview() || SrpHelper::canPay()) {
			return $query; }
		else {
			return $query->whereIn('characterID', $character->self()->lists('characterID'));	}
	}

	public function character()
	{
		return $this->hasOne('EveAccountAPIKeyInfoCharacters', 'characterID', 'characterID');
	}

	public function fleet()
	{
		return $this->hasOne('SrpFleet', 'id', 'fleetID');
	}

	public function killmail()
	{
		return $this->hasOne('EveCharacterKillMails', 'killID', 'killID');
	}

	public function ship() {
		if ($this->killmail && $this->killmail->detail) {
			$ship = SrpInvType::where('typeID', '=', $this->killmail->detail->shipTypeID)->first();
			return $ship ?: SrpInvType::find(34); }
		else {
			return SrpInvType::find(34); }
	}

	public function statuses()
	{
		return $this->hasMany('SrpRequestStatus', 'requestID', 'id');
	}

	public function status()
	{
		return $this->statuses()->orderBy('created_at', 'DESC')->first();
	}
}
