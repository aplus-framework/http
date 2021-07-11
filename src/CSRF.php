<?php declare(strict_types=1);
/*
 * This file is part of The Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP;

use JetBrains\PhpStorm\Deprecated;

/**
 * Class CSRF.
 *
 * @deprecated Use AntiCSRF instead
 * @codeCoverageIgnore
 */
#[Deprecated('since HTTP Library version 3.9, use AntiCSRF instead')]
class CSRF extends AntiCSRF
{
    public function __construct(mixed ...$arguments)
    {
        \trigger_error('Class ' . __CLASS__ . ' is deprecated', \E_USER_DEPRECATED);
        parent::__construct(...$arguments);
    }
}
