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

class CreateSrpStatusTable extends Migration {
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
		Schema::create('srp_status', function(Blueprint $table)
		{
			$table->increments('statusID')->unsigned();
			$table->integer('statusCharacterID')->unsigned();
			$table->decimal('statusValue', 15, 2)->default(0.00);
			$table->text('statusNotes');
			$table->integer('statusTypeID')->unsigned();
			$table->integer('requestID')->unsigned();

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
		Schema::dropIfExists('srp_status');
	}

}
