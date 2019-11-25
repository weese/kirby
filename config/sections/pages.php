<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\PagesSection;
use Kirby\Toolkit\A;
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
        'parent'
    ],
    'props' => [
        /**
         * Optional array of templates that should only be allowed to add.
         */
        'create' => function ($add = null) {
            return $add;
        },
        /**
         * Enables/disables reverse sorting
         */
        'flip' => function (bool $flip = false) {
            return $flip;
        },
        /**
         * Image options to control the source and look of page previews
         */
        'image' => function ($image = null) {
            return $image ?? [];
        },
        /**
         * Optional info text setup. Info text is shown on the right (lists) or below (cards) the page title.
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
         * Overwrites manual sorting and sorts by the given field and sorting direction (i.e. `date desc`)
         */
        'sortBy' => function (string $sortBy = null) {
            return $sortBy;
        },
        /**
         * Filters pages by their status. Available status settings: `draft`, `unlisted`, `listed`, `published`, `all`.
         */
        'status' => function (string $status = '') {
            if ($status === 'drafts') {
                $status = 'draft';
            }

            if (in_array($status, ['all', 'draft', 'published', 'listed', 'unlisted']) === false) {
                $status = 'all';
            }

            return $status;
        },
        /**
         * Filters the list by templates and sets template options when adding new pages to the section.
         */
        'templates' => function ($templates = null) {
            return A::wrap($templates ?? $this->template);
        },
        /**
         * Setup for the main text in the list or cards. By default this will display the page title.
         */
        'text' => function (string $text = '{{ page.title }}') {
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
    'methods' => [

        'blueprints' => function () {
            $blueprints = [];
            $templates  = empty($this->create) === false ? A::wrap($this->create) : $this->templates;

            if (empty($templates) === true) {
                $templates = $this->kirby()->blueprints();
            }

            // convert every template to a usable option array
            // for the template select box
            foreach ($templates as $template) {
                try {
                    $props = Blueprint::load('pages/' . $template);

                    $blueprints[] = [
                        'name'  => basename($props['name']),
                        'title' => $props['title'],
                    ];
                } catch (Throwable $e) {
                    $blueprints[] = [
                        'name'  => basename($template),
                        'title' => ucfirst($template),
                    ];
                }
            }

            return $blueprints;
        }
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

        $section = new PagesSection($this->parentModel(), $this->props);

        return [
            [
                'pattern' => '',
                'action'  => function () use ($section) {

                    return [
                        'data'    => $section->data(),
                        'errors'  => $section->errors(),
                        'options' => [
                            'add'      => $section->add(),
                            'max'      => $section->option('max'),
                            'min'      => $section->option('min'),
                            'size'     => $section->option('size'),
                            'sortable' => $section->sortable()
                        ],
                        'pagination' => $section->pagination(),
                    ];
                }
            ]
        ];

    }
];
