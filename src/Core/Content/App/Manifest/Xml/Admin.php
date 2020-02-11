<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

class Admin extends XmlElement
{
    /**
     * @var array<ActionButton>
     */
    protected $actionButtons = [];

    /**
     * @var array<Module>
     */
    protected $modules = [];

    /**
     * @param array<string, array<ActionButton>|array<Module>> $data
     */
    private function __construct(array $data)
    {
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }

    public static function fromXml(\DOMElement $element): self
    {
        return new self(self::parseChilds($element));
    }

    /**
     * @return array<ActionButton>
     */
    public function getActionButtons(): array
    {
        return $this->actionButtons;
    }

    /**
     * @return array<Module>
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @return array<string, array<ActionButton>|array<Module>>
     */
    private static function parseChilds(\DOMElement $element): array
    {
        $actionButtons = [];
        /** @var \DOMElement $actionButton */
        foreach ($element->getElementsByTagName('action-button') as $actionButton) {
            $actionButtons[] = ActionButton::fromXml($actionButton);
        }

        $modules = [];
        /** @var \DOMElement $module */
        foreach ($element->getElementsByTagName('module') as $module) {
            $modules[] = Module::fromXml($module);
        }

        return [
            'actionButtons' => $actionButtons,
            'modules' => $modules,
        ];
    }
}
