<?php

use Kirby\Cms\File;
use Kirby\Cms\FilesSection;
use Kirby\Toolkit\I18n;

return [
    'mixins' => [
        'empty',
        'headline',
        'help',
        'layout',
        'min',
        'max',
        'pagination',
        'parent',
    ],
    'props' => [
        /**
         * Enables/disables reverse sorting
         */
        'flip' => function (bool $flip = false) {
            return $flip;
        },
        /**
         * Image options to control the source and look of file previews
         */
        'image' => function ($image = null) {
            return $image ?? [];
        },
        /**
         * Optional info text setup. Info text is shown on the right (lists) or below (cards) the filename.
         */
        'info' => function (string $info = null) {
            return $info;
        },
        /**
         * The size option controls the size of cards. By default cards are auto-sized and the cards grid will always fill the full width. With a size you can disable auto-sizing. Available sizes: `tiny`, `small`, `medium`, `large`, `huge`
         */
        'size' => function (string $size = 'auto') {
            return $size;
        },
        /**
         * Enables/disables manual sorting
         */
        'sortable' => function (bool $sortable = true) {
            return $sortable;
        },
        /**
         * Overwrites manual sorting and sorts by the given field and sorting direction (i.e. `filename desc`)
         */
        'sortBy' => function (string $sortBy = null) {
            return $sortBy;
        },
        /**
         * Filters all files by template and also sets the template, which will be used for all uploads
         */
        'template' => function (string $template = null) {
            return $template;
        },
        /**
         * Setup for the main text in the list or cards. By default this will display the filename.
         */
        'text' => function (string $text = '{{ file.filename }}') {
            return $text;
        }
    ],
    'computed' => [
        'link' => function () {
            $modelLink  = $this->model()->panelUrl(true);
            $parentLink = $this->parentModel()->panelUrl(true);

            if ($modelLink !== $parentLink) {
                return $parentLink;
            }
        },
    ],
    'toArray' => function () {
        return [
            'empty'    => $this->empty,
            'headline' => $this->headline,
            'help'     => $this->help,
            'layout'   => $this->layout,
            'link'     => $this->link,
            'name'     => $this->name,
            'required' => $this->min > 0,
            'type'     => $this->type,
        ];
    },
    'api' => function () {

        $section = new FilesSection($this->parentModel(), $this->props);

        return [
            [
                'pattern' => '',
                'action'  => function () use ($section) {
                    return [
                        'data'    => $section->data(),
                        'errors'  => $section->errors(),
                        'options' => [
                            'accept'   => $section->accept(),
                            'max'      => $section->option('max'),
                            'min'      => $section->option('min'),
                            'size'     => $section->option('size'),
                            'sortable' => $section->sortable(),
                            'upload'   => $section->upload()
                        ],
                        'pagination' => $section->pagination()
                    ];
                }
            ]
        ];

    }

];
