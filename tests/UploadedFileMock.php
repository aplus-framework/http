<?php
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\HTTP;

use Framework\HTTP\UploadedFile;

class UploadedFileMock extends UploadedFile
{
    public bool $isMoved = false;
    public ?string $destination = null;

    public function setErrorMessage(int $code) : void
    {
        parent::setErrorMessage($code);
    }
}
