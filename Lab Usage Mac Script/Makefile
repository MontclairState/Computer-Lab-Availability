#
#   Copyright 2009 Joe Block <jpb@ApesSeekingKnowledge.net>
#
#   Licensed under the Apache License, Version 2.0 (the "License");
#   you may not use this file except in compliance with the License.
#   You may obtain a copy of the License at
#
#       http://www.apache.org/licenses/LICENSE-2.0
#
#   Unless required by applicable law or agreed to in writing, software
#   distributed under the License is distributed on an "AS IS" BASIS,
#   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#   See the License for the specific language governing permissions and
#   limitations under the License.
#


# Lab Usage Mac Scripts

# For use with the Montclair State University Lab Usage Web Application

# Installs login-map.sh and logout-map.sh to /Library/Lab Usage

# Sets a login and logout hook to com.apple.loginwindow that reports if the Mac is logged in or out.


include /usr/local/share/luggage/luggage.make

TITLE=labusagescripts
REVERSE_DOMAIN=edu.msu.oit
PACKAGE_VERSION=1.0
PAYLOAD=\
	pack-Library-Lab-Usage \
	pack-script-postflight

pack-Library-Lab-Usage: l_Library
	@sudo ${CP} -R ./scripts ${WORK_D}/Library/Lab\ Usage
	@sudo chmod -R 0755 ${WORK_D}/Library/Lab\ Usage
	@sudo chown -R root:admin ${WORK_D}/Library/Lab\ Usage

