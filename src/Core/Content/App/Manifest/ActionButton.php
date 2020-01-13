<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest;

use Shopware\Core\Framework\Struct\Struct;

class ActionButton extends Struct
{
    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $label;

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var string
     */
    protected $view;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var bool
     */
    protected $openNewTab = false;

    public function getAction(): string
    {
        return $this->action;
    }

    public function getLabels(): array
    {
        return $this->label;
    }

    public function getLabel(string $locale): ?string
    {
        return $this->label[$locale] ?? null;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isOpenNewTab(): bool
    {
        return $this->openNewTab;
    }
}
