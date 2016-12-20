<?php
/**
 * Created by mcfedr on 30/06/15 21:03
 */

namespace Nelmio\ApiDocBundle\Parser;

class JsonSerializableParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(array $item)
    {
        if (!is_subclass_of($item['class'], 'JsonSerializable')) {
            return false;
        }

        $ref = new \ReflectionClass($item['class']);
        if ($ref->hasMethod('__construct')) {
            foreach ($ref->getMethod('__construct')->getParameters() as $parameter) {
                if (!$parameter->isOptional()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $input)
    {
        /** @var \JsonSerializable $obj */
        $obj = new $input['class']();

        $encoded = $obj->jsonSerialize();
        $parsed = $this->getItemMetaData($encoded);

        if (isset($input['name']) && !empty($input['name'])) {
            $output = array();
            $output[$input['name']] = $parsed;

            return $output;
        }

        return $parsed['children'];
    }

    public function getItemMetaData($item)
    {
        $type = gettype($item);

        $meta = array(
            'dataType' => $type == 'NULL' ? null : $type,
            'actualType' => $type,
            'subType' => null,
            'required' => null,
            'description' => null,
            'readonly' => null,
            'default' => is_scalar($item) ? $item : null,
        );

        if ($type == 'object' && $item instanceof \JsonSerializable) {
            $meta = $this->getItemMetaData($item->jsonSerialize());
            $meta['class'] = get_class($item);
        } elseif (($type == 'object' && $item instanceof \stdClass) || ($type == 'array' && !$this->isSequential($item))) {
            $meta['dataType'] = 'object';
            $meta['children'] = array();
            foreach ($item as $key => $value) {
                $meta['children'][$key] = $this->getItemMetaData($value);
            }
        }

        return $meta;
    }

    /**
     * Check for numeric sequential keys, just like the json encoder does
     * Credit: http://stackoverflow.com/a/25206156/859027
     *
     * @param  array $arr
     * @return bool
     */
    private function isSequential(array $arr)
    {
        for ($i = count($arr) - 1; $i >= 0; $i--) {
            if (!isset($arr[$i]) && !array_key_exists($i, $arr)) {
                return false;
            }
        }

        return true;
    }
}
