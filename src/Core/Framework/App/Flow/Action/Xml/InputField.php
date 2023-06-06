<?php declare(strict_types=1);

namespace Shopware\Core\Framework\App\Flow\Action\Xml;

use Shopware\Core\Framework\App\Manifest\Xml\XmlElement;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('core')]
class InputField extends XmlElement
{
    private const TRANSLATABLE_FIELDS = [
        'label',
        'place-holder',
        'helpText',
    ];

    private const BOOLEAN_FIELD = ['required'];

    protected ?string $name = null;

    /**
     * @var array<string, string>|null
     */
    protected ?array $label = null;

    /**
     * @var array<string, string>|null
     */
    protected ?array $placeHolder = null;

    protected ?bool $required = null;

    /**
     * @var array<string, string>|null
     */
    protected ?array $helpText = [];

    protected ?string $defaultValue = null;

    /**
     * @var array<string, string>|null
     */
    protected ?array $options = [];

    protected ?string $type = null;

    /**
     * @param array<int|string, mixed> $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>|null
     */
    public function getLabel(): ?array
    {
        return $this->label;
    }

    /**
     * @return array<string, string>|null
     */
    public function getPlaceHolder(): ?array
    {
        return $this->placeHolder;
    }

    public function getRequired(): ?bool
    {
        return $this->required;
    }

    /**
     * @return array<string, string>|null
     */
    public function getHelpText(): ?array
    {
        return $this->helpText;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * @return array<string, string|array<string, string>>|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function toArray(string $defaultLocale): array
    {
        $data = parent::toArray($defaultLocale);

        return array_merge($data, [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'placeHolder' => $this->getPlaceHolder(),
            'required' => $this->getRequired(),
            'helpText' => $this->getHelpText(),
            'defaultValue' => $this->getDefaultValue(),
            'options' => array_map(
                fn ($option) => \is_array($option) ? $option : json_decode($option, true),
                $this->getOptions() ?? []
            ),
            'type' => $this->getType(),
        ]);
    }

    public static function fromXml(\DOMElement $element): self
    {
        return new self(self::parse($element));
    }

    /**
     * @return array<int|string, mixed>
     */
    private static function parse(\DOMElement $element): array
    {
        $values = [];

        $values['type'] = $element->getAttribute('type') ?: 'text';

        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            // translated
            if (\in_array($child->tagName, self::TRANSLATABLE_FIELDS, true)) {
                $values = self::mapTranslatedTag($child, $values);

                continue;
            }

            if (\in_array($child->nodeName, self::BOOLEAN_FIELD, true)) {
                $values[$child->nodeName] = $child->nodeValue === 'true';

                continue;
            }

            if ($child->nodeName === 'options') {
                $values[$child->nodeName] = self::parseOptions($child);

                continue;
            }

            $values[$child->nodeName] = $child->nodeValue;
        }

        return $values;
    }

    /**
     * @return array<int|string, mixed>
     */
    private static function parseOptions(\DOMElement $element): array
    {
        $values = [];

        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            $values[] = self::parseOption($child);
        }

        return $values;
    }

    /**
     * @return array<int|string, mixed>
     */
    private static function parseOption(\DOMElement $element): array
    {
        $values = [];

        $values['value'] = $element->getAttribute('value');

        foreach ($element->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }

            $values = self::mapTranslatedTag($child, $values);
        }

        return $values;
    }
}
