<?php

namespace Kirby\Cms;

class PagesSection
{

    protected $model;
    protected $options;
    protected $pages;

    public function __construct($model, array $options = [])
    {
        $this->model   = $model;
        $this->options = array_merge([
            'create'    => null,
            'flip'      => false,
            'image'     => null,
            'info'      => false,
            'layout'    => 'list',
            'limit'     => null,
            'max'       => null,
            'min'       => null,
            'page'      => null,
            'sortable'  => null,
            'sortBy'    => null,
            'status'    => null,
            'templates' => [],
            'text'      => null
        ], $options);
    }

    public function add()
    {
        if ($this->options['create'] === false) {
            return false;
        }

        if (in_array($this->options['status'], ['draft', 'all']) === false) {
            return false;
        }

        if ($this->isFull() === true) {
            return false;
        }

        return true;
    }

    public function data()
    {
        $data = [];

        foreach ($this->pages() as $item) {
            $permissions = $item->permissions();
            $image       = $item->panelImage($this->options['image']);

            $data[] = [
                'id'          => $item->id(),
                'dragText'    => $item->dragText(),
                'text'        => $item->toString($this->options['text']),
                'info'        => $item->toString($this->options['info']),
                'parent'      => $item->parentId(),
                'icon'        => $item->panelIcon($image),
                'image'       => $image,
                'link'        => $item->panelUrl(true),
                'status'      => $item->status(),
                'permissions' => [
                    'sort'         => $permissions->can('sort'),
                    'changeStatus' => $permissions->can('changeStatus')
                ]
            ];
        }

        return $data;
    }

    public function errors(): array
    {

        $errors = [];

        if ($this->validateMax() === false) {
            $errors['max'] = I18n::template('error.section.pages.max.' . I18n::form($this->max), [
                'max'     => $this->max,
                'section' => $this->headline
            ]);
        }

        if ($this->validateMin() === false) {
            $errors['min'] = I18n::template('error.section.pages.min.' . I18n::form($this->min), [
                'min'     => $this->min,
                'section' => $this->headline
            ]);
        }

        if (empty($errors) === true) {
            return [];
        }

        return [
            $this->name => [
                'label'   => $this->headline,
                'message' => $errors,
            ]
        ];

    }

    public function isFull()
    {
        if ($this->options['max']) {
            return $this->total() >= $this->options['max'];
        }

        return false;
    }

    public function option(string $option, $fallback = null)
    {
        return $this->options[$option] ?? $fallback;
    }

    public function pages()
    {
        if ($this->pages !== null) {
            return $this->pages;
        }

        switch ($this->options['status']) {
            case 'draft':
                $pages = $this->model->drafts();
                break;
            case 'listed':
                $pages = $this->model->children()->listed();
                break;
            case 'published':
                $pages = $this->model->children();
                break;
            case 'unlisted':
                $pages = $this->model->children()->unlisted();
                break;
            default:
                $pages = $this->model->childrenAndDrafts();
        }

        // loop for the best performance
        foreach ($pages->data as $id => $page) {

            // remove all protected pages
            if ($page->isReadable() === false) {
                unset($pages->data[$id]);
                continue;
            }

            // filter by all set templates
            if ($this->options['templates'] && in_array($page->intendedTemplate()->name(), $this->options['templates']) === false) {
                unset($pages->data[$id]);
                continue;
            }
        }

        // sort
        if ($this->options['sortBy']) {
            $pages = $pages->sortBy(...$pages::sortArgs($this->options['sortBy']));
        }

        // flip
        if ($this->options['flip'] === true) {
            $pages = $pages->flip();
        }

        // pagination
        $pages = $pages->paginate([
            'page'  => $this->options['page'],
            'limit' => $this->options['limit']
        ]);

        return $this->pages = $pages;
    }

    public function pagination()
    {
        $pagination = $this->pages()->pagination();

        return [
            'limit'  => $pagination->limit(),
            'offset' => $pagination->offset(),
            'page'   => $pagination->page(),
            'total'  => $pagination->total(),
        ];
    }

    public function sortable()
    {
        if (in_array($this->options['status'], ['listed', 'published', 'all']) === false) {
            return false;
        }

        if ($this->options['sortable'] === false) {
            return false;
        }

        if ($this->options['sortBy'] !== null) {
            return false;
        }

        if ($this->options['flip'] === true) {
            return false;
        }

        return true;
    }

    public function total()
    {
        return $this->pages()->pagination()->total();
    }

    public function validateMax()
    {
        if ($this->options['max'] && $this->total() > $this->options['max']) {
            return false;
        }

        return true;
    }

    public function validateMin()
    {
        if ($this->options['min'] && $this->options['min'] > $this->total()) {
            return false;
        }

        return true;
    }

}
