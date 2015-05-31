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

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSrpKillTable extends Migration {
	/*
	|--------------------------------------------------------------------------
	| up()
	|--------------------------------------------------------------------------
	|
	| Runs the migration.
	|
	*/
	public function up()
	{
		Schema::create('srp_kill', function(Blueprint $table)
		{
			// root
			$table->integer ('killID')->unsigned()->primary();
			$table->datetime('killTime');
			$table->integer ('solarSystemID')->unsigned();
			$table->integer ('moonID')->unsigned();
			// victim
			$table->integer ('shipTypeID')->unsigned();
			$table->integer ('characterID')->unsigned();
			$table->integer ('corporationID')->unsigned();
			$table->integer ('allianceID')->unsigned();
			$table->integer ('factionID')->unsigned();
			$table->integer ('damageTaken')->unsigned();
			// zkb
			$table->string  ('hash');
			$table->decimal ('totalValue', 15, 2);
			$table->integer ('points')->unsigned();

			$table->timestamps();
			$table->softDeletes();
		});
	}

	/*
	|--------------------------------------------------------------------------
	| up()
	|--------------------------------------------------------------------------
	|
	| Reverses the migration.
	|
	*/
	public function down()
	{
		Schema::dropIfExists('srp_kill');
	}

}
