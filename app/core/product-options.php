<?php

function validateProductOptionGroups($input): array
{
    if (!is_array($input) || !array_is_list($input) || count($input) > 20) {
        throw new InvalidArgumentException('A product can have up to 20 customization groups.');
    }

    $groups = [];
    $groupNames = [];
    $groupIds = [];
    $optionIds = [];

    foreach ($input as $groupIndex => $group) {
        if (!is_array($group)) {
            throw new InvalidArgumentException('Invalid customization group.');
        }

        $name = $group['name'] ?? null;
        if (!is_string($name) || ($name = trim($name)) === '' || mb_strlen($name, 'UTF-8') > 100) {
            throw new InvalidArgumentException('Group names must be between 1 and 100 characters.');
        }
        $nameKey = mb_strtolower($name, 'UTF-8');
        if (isset($groupNames[$nameKey])) {
            throw new InvalidArgumentException('Customization group names must be unique per product.');
        }
        $groupNames[$nameKey] = true;

        $groupId = $group['groupId'] ?? null;
        if ($groupId !== null) {
            $groupId = filter_var($groupId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($groupId === false || isset($groupIds[$groupId])) {
                throw new InvalidArgumentException('Invalid customization group ID.');
            }
            $groupIds[$groupId] = true;
        }
        if (!is_bool($group['required'] ?? null)) {
            throw new InvalidArgumentException('Specify whether each customization group is required.');
        }

        $inputChoices = $group['choices'] ?? null;
        if (!is_array($inputChoices) || !array_is_list($inputChoices) || count($inputChoices) > 50) {
            throw new InvalidArgumentException('Each group can have up to 50 choices.');
        }
        if ($group['required'] && !$inputChoices) {
            throw new InvalidArgumentException('Required groups must have at least one choice.');
        }

        $choices = [];
        $choiceNames = [];
        foreach ($inputChoices as $choiceIndex => $choice) {
            if (!is_array($choice)) {
                throw new InvalidArgumentException('Invalid customization choice.');
            }

            $choiceName = $choice['name'] ?? null;
            if (!is_string($choiceName) || ($choiceName = trim($choiceName)) === ''
                || mb_strlen($choiceName, 'UTF-8') > 100) {
                throw new InvalidArgumentException('Choice names must be between 1 and 100 characters.');
            }
            $choiceNameKey = mb_strtolower($choiceName, 'UTF-8');
            if (isset($choiceNames[$choiceNameKey])) {
                throw new InvalidArgumentException('Choice names must be unique within a group.');
            }
            $choiceNames[$choiceNameKey] = true;

            $optionId = $choice['optionId'] ?? null;
            if ($optionId !== null) {
                $optionId = filter_var($optionId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($optionId === false || isset($optionIds[$optionId])) {
                    throw new InvalidArgumentException('Invalid customization choice ID.');
                }
                $optionIds[$optionId] = true;
            }

            $price = $choice['extraPrice'] ?? null;
            if (!is_string($price) && !is_int($price) && !is_float($price)) {
                throw new InvalidArgumentException('Enter a valid extra price for each choice.');
            }
            $price = (string) $price;
            if (!preg_match('/^(?:0|[1-9][0-9]{0,7})(?:\.[0-9]{1,2})?$/D', $price)) {
                throw new InvalidArgumentException('Extra prices must be non-negative and fit the database price field.');
            }
            if (!str_contains($price, '.')) {
                $price .= '.00';
            } elseif (strlen(substr(strrchr($price, '.'), 1)) === 1) {
                $price .= '0';
            }

            if (!is_bool($choice['available'] ?? null)) {
                throw new InvalidArgumentException('Specify whether each choice is available.');
            }
            $choices[] = [
                'optionId' => $optionId,
                'name' => $choiceName,
                'extraPrice' => $price,
                'available' => $choice['available'],
                'displayOrder' => $choiceIndex,
            ];
        }

        if ($group['required'] && !array_filter($choices, static fn(array $choice): bool => $choice['available'])) {
            throw new InvalidArgumentException('Required groups must have at least one available choice.');
        }
        $groups[] = [
            'groupId' => $groupId,
            'name' => $name,
            'required' => $group['required'],
            'displayOrder' => $groupIndex,
            'choices' => $choices,
        ];
    }

    return $groups;
}
