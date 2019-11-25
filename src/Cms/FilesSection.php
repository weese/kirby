<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;

class FilesSection
{

    protected $model;
    protected $options;
    protected $files;

    public function __construct($model, array $options = [])
    {
        $this->model   = $model;
        $this->options = array_merge([
            'flip'      => false,
            'image'     => null,
            'info'      => false,
            'layout'    => 'list',
            'limit'     => null,
            'max'       => null,
            'min'       => null,
            'page'      => null,
            'parent'    => null,
            'sortable'  => null,
            'sortBy'    => null,
            'template'  => null,
            'text'      => null
        ], $options);
    }

    public function accept() {
        if ($this->options['template']) {
            $file = new File([
                'filename' => 'tmp',
                'template' => $this->options['template']
            ]);

            return $file->blueprint()->accept()['mime'] ?? '*';
        }

        return null;
    }

    public function data()
    {
        $data = [];

        // the drag text needs to be absolute when the files come from
        // a different parent model
        $dragTextAbsolute = $this->model->is($this->options['parent']) === false;

        foreach ($this->files() as $file) {
            $image = $file->panelImage($this->options['image']);

            $data[] = [
                'dragText' => $file->dragText('auto', $dragTextAbsolute),
                'filename' => $file->filename(),
                'id'       => $file->id(),
                'text'     => $file->toString($this->options['text']),
                'info'     => $file->toString($this->options['info'] ?? false),
                'icon'     => $file->panelIcon($image),
                'image'    => $image,
                'link'     => $file->panelUrl(true),
                'parent'   => $file->parent()->panelPath(),
                'url'      => $file->url(),
            ];
        }

        return $data;
    }

    public function errors(): array
    {
        $errors = [];

        if ($this->validateMax() === false) {
            $errors['max'] = I18n::template('error.section.files.max.' . I18n::form($this->options['max']), [
                'max'     => $this->options['max'],
                'section' => $this->options['headline']
            ]);
        }

        if ($this->validateMin() === false) {
            $errors['min'] = I18n::template('error.section.files.min.' . I18n::form($this->options['min']), [
                'min'     => $this->options['min'],
                'section' => $this->options['headline']
            ]);
        }

        if (empty($errors) === true) {
            return [];
        }

        return [
            $this->name => [
                'label'   => $this->options['headline'],
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

    public function files()
    {
        if ($this->files !== null) {
            return $this->files;
        }

        $files = $this->model->files()->template($this->options['template']);

        if ($this->options['sortBy']) {
            $files = $files->sortBy(...$files::sortArgs($this->options['sortBy']));
        } elseif ($this->options['sortable'] === true) {
            $files = $files->sortBy('sort', 'asc', 'filename', 'asc');
        }

        // flip
        if ($this->options['flip'] === true) {
            $files = $files->flip();
        }

        // apply the default pagination
        $files = $files->paginate([
            'page'  => $this->options['page'],
            'limit' => $this->options['limit']
        ]);

        return $this->files = $files;
    }

    public function pagination()
    {
        $pagination = $this->files()->pagination();

        return [
            'limit'  => $pagination->limit(),
            'offset' => $pagination->offset(),
            'page'   => $pagination->page(),
            'total'  => $pagination->total(),
        ];
    }

    public function sortable()
    {
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
        return $this->files()->pagination()->total();
    }

    public function upload()
    {
        if ($this->isFull() === true) {
            return false;
        }

        // count all uploaded files
        $total = $this->total();
        $max   = $this->options['max'] ? $this->options['max'] - $total : null;

        if ($this->options['max'] && $total === $this->options['max'] - 1) {
            $multiple = false;
        } else {
            $multiple = true;
        }

        return [
            'accept'     => $this->accept(),
            'multiple'   => $multiple,
            'max'        => $max,
            'api'        => $this->model->apiUrl(true) . '/files',
            'attributes' => array_filter([
                'template' => $this->options['template']
            ])
        ];
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
