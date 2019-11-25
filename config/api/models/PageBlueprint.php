<?php

use Kirby\Cms\PageBlueprint;

/**
 * PageBlueprint
 */
return [
    'fields' => [
        'name' => function (PageBlueprint $blueprint) {
            return $blueprint->name();
        },
        'num' => function (PageBlueprint $blueprint) {
            return $blueprint->num();
        },
        'options' => function (PageBlueprint $blueprint) {
            return $blueprint->options();
        },
        'preview' => function (PageBlueprint $blueprint) {
            return $blueprint->preview();
        },
        'status' => function (PageBlueprint $blueprint) {
            return $blueprint->status();
        },
        'tabs' => function (PageBlueprint $blueprint) {

            $tabs = $blueprint->tabs();

            foreach ($tabs as $tabName => $tab) {
                foreach ($tab['columns'] as $columnIndex => $column) {
                    foreach ($column['sections'] as $sectionName => $sectionProps) {
                        $section = $blueprint->section($sectionName);
                        $tabs[$tabName]['columns'][$columnIndex]['sections'][$sectionName] = $section->toArray();
                    }
                }
            }

            return $tabs;
        },
        'title' => function (PageBlueprint $blueprint) {
            return $blueprint->title();
        },
    ],
    'type' => 'Kirby\Cms\PageBlueprint',
    'views' => [
    ],
];
