<?php

namespace Swilen\Http\Component;

use Swilen\Shared\Support\Str;

class ResponseHeaderHunt extends HeaderHunt
{
    /**
     * The content disposition inline.
     *
     * @var string
     */
    public const DISPOSITION_INLINE = 'inline';

    /**
     * The content disposition attachment.
     *
     * @var string
     */
    public const DISPOSITION_ATTACHMENT = 'attachment';

    /**
     * Create new response headers collection from current params.
     *
     * @param array<string, mixed> $headers
     *
     * @return void
     */
    public function __construct(array $headers = [])
    {
        $this->replace($headers);

        $this->disableVersionHeader();
    }

    /**
     * Remove php version from headers collection for safe response.
     *
     * @return void
     */
    public function disableVersionHeader()
    {
        @header_remove('X-Powered-By');

        $this->remove('X-Powered-By');
    }

    /**
     * Make filename and content disposition for bianry file.
     *
     * @param string $disposition
     * @param string $filename
     *
     * @return void
     */
    public function makeDisposition(string $disposition, string $filename)
    {
        if (!in_array($disposition, [self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE])) {
            throw new \InvalidArgumentException(sprintf('The disposition must be either "%s" or "%s".', self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE));
        }

        if (!preg_match('/^[\x20-\x7e]*$/', $filename)) {
            throw new \InvalidArgumentException('The filename must only contain ASCII characters.');
        }

        if (Str::contains($filename, ['/', '\\', '%'])) {
            throw new \InvalidArgumentException('The filename cannot contain the "/", "\\" and "%" characters.');
        }

        $this->set('Content-Disposition', $disposition.'; filename="'.$filename.'"');
    }
}
