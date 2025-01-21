<?php
declare(strict_types=1);

/**
 * Copyright (c) Erwane BRETON
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Erwane BRETON
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;

require_once dirname(__DIR__) . '/vendor/cakephp/cakephp/src/Core/functions_global.php';

Configure::write('debug', true);
