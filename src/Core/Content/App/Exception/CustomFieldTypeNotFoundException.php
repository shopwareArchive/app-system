<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Exception;

class CustomFieldTypeNotFoundException extends \InvalidArgumentException implements SaasConnectException
{
    public function __construct(string $type, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('CustomFieldType for XML-Element "%s" not found.', $type), $code, $previous);
    }
}
