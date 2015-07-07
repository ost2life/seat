#!/usr/bin/env python
#The MIT License (MIT)
#
#Copyright (c) 2014 eve-seat
#
#Permission is hereby granted, free of charge, to any person obtaining a copy
#of this software and associated documentation files (the "Software"), to deal
#in the Software without restriction, including without limitation the rights
#to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
#copies of the Software, and to permit persons to whom the Software is
#furnished to do so, subject to the following conditions:
#
#The above copyright notice and this permission notice shall be included in all
#copies or substantial portions of the Software.
#
#THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
#IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
#FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
#AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
#LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
#OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
#SOFTWARE.

import logging
import requests
import sys
import thread
import xml.etree.cElementTree as ET
import ConfigParser
import Ice

from logging import (debug, info, warning, error, critical, exception, getLogger)
from optparse import OptionParser
from passlib.hash import bcrypt
from threading import Timer

#
#--- Default configuration values
#
cfgfile = 'authenticator.ini'
default = {
	'database':(
		('lib', str, 'MySQLdb'),
		('name', str, 'seat'),
		('user', str, 'seat'),
		('password', str, 'secret'),
		('host', str, '127.0.0.1'),
		('port', int, 3306)),

	'ice':(
		('host', str, '127.0.0.1'),
		('port', int, 6502),
		('slice', str, 'Murmur.ice'),
		('secret', str, ''),
		('watchdog', int, 30)),

	'iceraw':None,

	'murmur':(
		('servers', lambda x:map(int, x.split(',')), []),),

	'log':(
		('level', int, logging.DEBUG),
		('file', str, 'authenticator.log'))
}

#
#--- ThreadDB
#
class ThreadDbException(Exception): pass
class ThreadDB(object):
	"""
	Small abstraction to handle database connections for multiple
	threads
	"""

	db_connections = {}

	def connection(cls):
		tid = thread.get_ident()
		try:
			con = cls.db_connections[tid]
		except:
			info('Connecting to database server (%s %s:%d %s) for thread %d',
				 cfg.database.lib, cfg.database.host, cfg.database.port, cfg.database.name, tid)
			
			try:
				con = db.connect(host = cfg.database.host, port = cfg.database.port, user = cfg.database.user,
					passwd = cfg.database.password, db = cfg.database.name, charset = 'utf8')
				# Transactional engines like InnoDB initiate a transaction even
				# on SELECTs-only. Thus, we auto-commit so smfauth gets recent data.
				con.autocommit(True)
			except db.Error, e:
				error('Could not connect to database: %s', str(e))
				raise threadDbException()
			cls.db_connections[tid] = con
		return con
	connection = classmethod(connection)

	def cursor(cls):
		return cls.connection().cursor()
	cursor = classmethod(cursor)

	def execute(cls, *args, **kwargs):
		if "threadDB__retry_execution__" in kwargs:
			# Have a magic keyword so we can call ourselves while preventing
			# an infinite loop
			del kwargs["threadDB__retry_execution__"]
			retry = False
		else:
			retry = True
		
		c = cls.cursor()
		try:
			c.execute(*args, **kwargs)
		except db.OperationalError, e:
			error('Database operational error %d: %s', e.args[0], e.args[1])
			c.close()
			cls.invalidate_connection()
			if retry:
				# Make sure we only retry once
				info('Retrying database operation')
				kwargs["threadDB__retry_execution__"] = True
				c = cls.execute(*args, **kwargs)
			else:
				error('Database operation failed ultimately')
				raise ThreadDbException()
		return c
	execute = classmethod(execute)

	def invalidate_connection(cls):
		tid = thread.get_ident()
		con = cls.db_connections.pop(tid, None)
		if con:
			debug('Invalidate connection to database for thread %d', tid)
			con.close()

	invalidate_connection = classmethod(invalidate_connection)

	def disconnect(cls):
		while cls.db_connections:
			tid, con = cls.db_connections.popitem()
			debug('Close database connection for thread %d', tid)
			con.close()
	disconnect = classmethod(disconnect)

#
#--- Helper classes
#
class config(object):
	def __init__(self, filename = None, default = None):
		if not filename or not default: return
		cfg = ConfigParser.ConfigParser()
		cfg.optionxform = str
		cfg.read(filename)
		
		for h, v in default.iteritems():
			if not v:
				# Output this whole section as a list of raw key/value tuples
				try:
					self.__dict__[h] = cfg.items(h)
				except ConfigParser.NoSectionError:
					self.__dict__[h] = []
			else:
				self.__dict__[h] = config()
				for name, conv, vdefault in v:
					try:
						self.__dict__[h].__dict__[name] = conv(cfg.get(h, name))
					except (ValueError, ConfigParser.NoSectionError, ConfigParser.NoOptionError):
						self.__dict__[h].__dict__[name] = vdefault

#
#--- do_main_program
#
def do_main_program():
	Ice.loadSlice('', ["-I/usr/share/Ice/slice", "Murmur.ice"])
	import Murmur

	#
	#--- MetaCallback
	#
	class MetaCallback(Murmur.MetaCallback):
		def __init__(self, app):
			Murmur.MetaCallback.__init__(self)
			self.app = app

		def started(self, server, current = None):
			print server
			return

		def stopped(self, server, current = None):
			print server
			return

	#
	#--- ServerCallback
	#
	class ServerCallback(Murmur.ServerCallback):
		def __init__(self):
			Murmur.ServerUpdatingAuthenticator.__init__(self)

		def userConnected(self, user, current = None):
			return

		def userDisconnected(self, user, current = None):
			return

		def userStateChanged(self, user, current = None):
			return

		def userTextMessage(self, user, message, current = None):
			return

		def channelCreated(self, channel, current = None):
			return

		def channelRemoved(self, channel, current = None):
			return

		def channelStateChanged(self, channel, current = None):
			return

	#
	#--- ServerContextCallback
	#
	class ServerContextCallback(Murmur.ServerContextCallback):
		def __init__(self, server, adapter):
			self.server = server

		def contextAction(self, action, user, session, channelid, current = None):
			return

	#
	#--- ServerAuthenticator
	#
	class ServerAuthenticator(Murmur.ServerUpdatingAuthenticator):
		tickers = {}

		def __init__(self):
			Murmur.ServerUpdatingAuthenticator.__init__(self)

		def getTicker(self, corporationID, current = None):
			# Cached
			if corporationID in self.tickers:
				return self.tickers[corporationID]

			# Not cached
			else:
				try:
					apiResult = requests.get("http://api.eveonline.com/corp/CorporationSheet.xml.aspx", params = {"corporationID": corporationID}, timeout = 10)
					xmlRoot = ET.fromstring(apiResult.text)
					ticker = xmlRoot.find(".//ticker").text
					self.tickers[corporationID] = ticker
					return ticker
				except:
					return '-----'

		def authenticate(self, name, pw, certificates, certhash, cerstrong, out_newname):
			# Must have a password
			if pw == None:
				info("No password supplied")
				return (-1, None, None)

			# Search by username or email, account must be active
			sql = "SELECT u.id, u.password, c.characterID, c.characterName, c.corporationID FROM seat_users AS u JOIN seat_user_settings AS s ON s.user_id = u.id AND s.setting = 'main_character_id' JOIN account_apikeyinfo_characters AS c ON c.characterID = s.value WHERE u.deleted_at IS NULL and u.activated = 1 AND u.username = %s OR u.email = %s LIMIT 1"
			cursor = ThreadDB.execute(sql, [name, name])
			dbResult = cursor.fetchone()
			cursor.close()

			if dbResult == None:
				info("Cannot find a user with the name '%s'", name)
				return (-1, None, None)

			userID, userPassword, characterID, characterName, corporationID = dbResult

			# User must have correct password
			if bcrypt.verify(pw, userPassword) == False:
				info("Incorrect password for '%s'", name)
				return (-1, None, None)

			# Determine groups
			groups = None;
			tags = [];

			# Administrator
			sql = "SELECT * FROM seat_group_user AS gu JOIN seat_groups AS g ON g.id = gu.group_id WHERE gu.user_id = %s AND g.name = 'Administrators' OR g.name = 'MumbleAdmin' LIMIT 1"
			cursor = ThreadDB.execute(sql, [userID])
			dbResult = cursor.fetchone()
			cursor.close()

			if dbResult != None:
				groups = ["admin"]
				tags.append("Admin")

			# Get corporation ticker and tags
			ticker = self.getTicker(corporationID)

			# Return with formated names and groups
			return (characterID, "[{0}] {1}".format(ticker, characterName), groups)

		def getInfo(self, id, current = None):
			return (False, None)

		def nameToId(self, name, current = None):
			sql = "SELECT characterID FROM account_apikeyinfo_characters WHERE characterName = %s LIMIT 1"
			cursor = ThreadDB.execute(sql, [name])
			dbResult = cursor.fetchone()
			cursor.close()

			if dbResult == None:
				# -2 for unknown name
				return -2
			else:
				return dbResult[0]

		def idToName(self, id, current = None):
			sql = "SELECT characterName FROM account_apikeyinfo_characters WHERE characterID = %s LIMIT 1"
			cursor = ThreadDB.execute(sql, [id])
			dbResult = cursor.fetchone()
			cursor.close()

			if dbResult == None:
				# Empty string for unknown id
				return ""
			else:
				return dbResult[0]

		def idToTexture(self, id, current = None):
			return None

		def registerUser(self, name, current = None):
			return -1

		def unregisterUser(self, id, current = None):
			return -1

		def getRegisteredUsers(self, filter, current = None):
			filter = filter + "%"
			sql = "SELECT characterID, characterName, corporationID FROM account_apikeyinfo_characters WHERE characterName LIKE %s"
			cursor = ThreadDB.execute(sql, [filter])
			dbResult = cursor.fetchall()
			cursor.close()

			if dbResult == None:
				debug("getRegisteredUsers: No results with filter '%s'", filter)
				return {}

			debug("getRegisteredUsers: %d results with filter '%s'", len(dbResult), filter)
			return dict([(a, "[{0}] {1}".format(self.getTicker(c), b)) for a, b, c in dbResult])

		def setInfo(self, id, info, current = None):
			return -1

		def setTexture(self, id, texture, current = None):
			return -1

	#
	#--- SeatAuthenticatorApp
	#
	class SeatAuthenticatorApp(Ice.Application):
		def run(self, args):
			self.shutdownOnInterrupt()
			
			if not self.initializeIceConnection():
				return 1

			if cfg.ice.watchdog > 0:
				self.failedWatch = True
				self.checkConnection()

			# Serve till we are stopped
			self.communicator().waitForShutdown()
			self.watchdog.cancel()

			if self.interrupted():
				warning('Caught interrupt, shutting down')

			ThreadDB.disconnect()
			return 0

		def initializeIceConnection(self):
			"""
			Establishes the two-way Ice connection and adds the authenticator to the
			configured servers
			"""
			ice = self.communicator()

			if cfg.ice.secret:
				debug('Using shared ice secret')
				ice.getImplicitContext().put("secret", cfg.ice.secret)

			info('Connecting to Ice server (%s:%d)', cfg.ice.host, cfg.ice.port)
			base = ice.stringToProxy('Meta:tcp -h %s -p %d' % (cfg.ice.host, cfg.ice.port))
			self.meta = Murmur.MetaPrx.uncheckedCast(base)

			adapter = ice.createObjectAdapterWithEndpoints('Callback.Client', 'tcp -h %s' % cfg.ice.host)
			adapter.activate()

			metacbprx = adapter.addWithUUID(MetaCallback(self))
			self.metacb = Murmur.MetaCallbackPrx.uncheckedCast(metacbprx)

			authprx = adapter.addWithUUID(ServerAuthenticator())
			self.auth = Murmur.ServerUpdatingAuthenticatorPrx.uncheckedCast(authprx)

			return self.attachCallbacks()

		def attachCallbacks(self, quiet = False):
			"""
			Attaches all callbacks for meta and authenticators
			"""

			try:
				if not quiet: info('Attaching meta callback')

				self.meta.addCallback(self.metacb)

				for server in self.meta.getBootedServers():
					if not cfg.murmur.servers or server.id() in cfg.murmur.servers:
						if not quiet: info('Setting authenticator for virtual server %d', server.id())
						server.setAuthenticator(self.auth)
	
			except (Murmur.InvalidSecretException, Ice.UnknownUserException, Ice.ConnectionRefusedException), e:
				if isinstance(e, Ice.ConnectionRefusedException):
					error('Server refused connection')
				elif isinstance(e, Murmur.InvalidSecretException) or \
					 isinstance(e, Ice.UnknownUserException) and (e.unknown == 'Murmur::InvalidSecretException'):
					error('Invalid ice secret')
				else:
					# We do not actually want to handle this one, re-raise it
					raise e

				self.connected = False
				return False

			self.connected = True
			return True

		def checkConnection(self):
			"""
			Tries reapplies all callbacks to make sure the authenticator
			survives server restarts and disconnects.
			"""

			try:
				if not self.attachCallbacks(quiet = not self.failedWatch):
					self.failedWatch = True
				else:
					self.failedWatch = False
			except Ice.Exception, e:
				error('Failed connection check, will retry in next watchdog run (%ds)', cfg.ice.watchdog)
				debug(str(e))
				self.failedWatch = True

			# Renew the timer
			self.watchdog = Timer(cfg.ice.watchdog, self.checkConnection)
			self.watchdog.start()

	#
	#--- CustomLogger
	#
	class CustomLogger(Ice.Logger):
		"""
		Logger implementation to pipe Ice log messages into
		our own log
		"""

		def __init__(self):
			Ice.Logger.__init__(self)
			self._log = getLogger('Ice')

		def _print(self, message):
			self._log.info(message)

		def trace(self, category, message):
			self._log.debug('Trace %s: %s', category, message)

		def warning(self, message):
			self._log.warning(message)

		def error(self, message):
			self._log.error(message)

	#
	#--- Start of authenticator
	#
	info('Starting seat mumble authenticator')
	initdata = Ice.InitializationData()
	initdata.properties = Ice.createProperties([], initdata.properties)
	for prop, val in cfg.iceraw:
		initdata.properties.setProperty(prop, val)

	initdata.properties.setProperty('Ice.ImplicitContext', 'Shared')
	initdata.logger = CustomLogger()

	app = SeatAuthenticatorApp()
	state = app.main(sys.argv[:1], initData = initdata)
	info('Shutdown complete')

#
#--- Start of program
#
if __name__ == '__main__':
	# Parse commandline options
	parser = OptionParser()
	parser.add_option('-i', '--ini', help = 'load configuration from INI', default = cfgfile)
	parser.add_option('-v', '--verbose', action = 'store_true', dest = 'verbose', help = 'verbose output [default]', default = True)
	parser.add_option('-q', '--quiet', action = 'store_false', dest = 'verbose', help = 'only error output')
	parser.add_option('-d', '--daemon', action = 'store_true', dest = 'force_daemon', help = 'run as daemon', default = False)
	parser.add_option('-a', '--app', action = 'store_true', dest = 'force_app', help = 'do not run as daemon', default = False)
	(option, args) = parser.parse_args()

	if option.force_daemon and option.force_app:
		parser.print_help()
		sys.exit(1)

	# Load configuration
	try:
		cfg = config(option.ini, default)
	except Exception, e:
		print>>sys.stderr, 'Fatal error, could not load config file from "%s"' % cfgfile
		sys.exit(1)

	try:
		db = __import__(cfg.database.lib)
	except ImportError, e:
		print>>sys.stderr, 'Fatal error, could not import database library "%s", '\
		'please install the missing dependency and restart the authenticator' % cfg.database.lib
		sys.exit(1)

	# Initialize logger
	if cfg.log.file:
		try:
			logfile = open(cfg.log.file, 'a')
		except IOError, e:
			print>>sys.stderr, 'Fatal error, could not open logfile "%s"' % cfg.log.file
			sys.exit(1)
	else:
		logfile = logging.sys.stderr

	if option.verbose:
		level = cfg.log.level
	else:
		level = logging.ERROR
	
	logging.basicConfig(level = level, format = '%(asctime)s %(levelname)s %(message)s', stream = logfile)

	# As the default try to run as daemon. Silently degrade to running as a normal application if this fails
	# unless the user explicitly defined what he expected with the -a / -d parameter. 
	try:
		if option.force_app:
			raise ImportError # Pretend that we couldn't import the daemon lib
		import daemon
	except ImportError:
		if option.force_daemon:
			print>>sys.stderr, 'Fatal error, could not daemonize process due to missing "daemon" library, ' \
			'please install the missing dependency and restart the authenticator'
			sys.exit(1)
		do_main_program()
	else:
		context = daemon.DaemonContext(working_directory = sys.path[0], stderr = logfile)
		context.__enter__()
		try:
			do_main_program()
		finally:
			context.__exit__(None, None, None)
