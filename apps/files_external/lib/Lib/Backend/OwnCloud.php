<?php
/**
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 *
 * @copyright Copyright (c) 2016, ownCloud GmbH.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_External\Lib\Backend;

use OCP\IL10N;
use OCP\Files\External\DefinitionParameter;
use OCP\Files\External\Auth\AuthMechanism;
use OCP\Files\External\Backend\Backend;
use OCA\Files_External\Service\BackendService;

use OCA\Files_External\Lib\Auth\Password\Password;

class OwnCloud extends Backend {

	public function __construct(IL10N $l, Password $legacyAuth) {
		$this
			->setIdentifier('owncloud')
			->addIdentifierAlias('\OC\Files\Storage\OwnCloud') // legacy compat
			->setStorageClass('\OCA\Files_External\Lib\Storage\OwnCloud')
			->setText($l->t('ownCloud'))
			->addParameters([
				(new DefinitionParameter('host', $l->t('URL'))),
				(new DefinitionParameter('root', $l->t('Remote subfolder')))
					->setFlag(DefinitionParameter::FLAG_OPTIONAL),
				(new DefinitionParameter('secure', $l->t('Secure https://')))
					->setType(DefinitionParameter::VALUE_BOOLEAN),
			])
			->addAuthScheme(AuthMechanism::SCHEME_PASSWORD)
			->setLegacyAuthMechanism($legacyAuth)
		;
	}

}
