<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait ReorderOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string $name       Name of the current entity (singular). Used as first URL segment.
     * @param  string $controller Name of the current CrudController.
     */
    protected function setupReorderRoutes($name, $controller)
    {
        Route::get($name.'/reorder', [
            'as' => 'crud.'.$name.'.reorder',
            'uses' => $controller.'@reorder',
        ]);

        Route::post($name.'/reorder', [
            'as' => 'crud.'.$name.'.save.reorder',
            'uses' => $controller.'@saveReorder',
        ]);
    }

    /**
     *  Reorder the items in the database using the Nested Set pattern.
     *
     *  Database columns needed: id, parent_id, lft, rgt, depth, name/title
     *
     *  @return Response
     */
    public function reorder()
    {
        $this->crud->hasAccessOrFail('reorder');
        $this->crud->setOperation('reorder');

        if (! $this->crud->isReorderEnabled()) {
            abort(403, 'Reorder is disabled.');
        }

        // get all results for that entity
        $this->data['entries'] = $this->crud->getEntries();
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.reorder').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getReorderView(), $this->data);
    }

    /**
     * Save the new order, using the Nested Set pattern.
     *
     * Database columns needed: id, parent_id, lft, rgt, depth, name/title
     *
     * @return
     */
    public function saveReorder()
    {
        $this->crud->hasAccessOrFail('reorder');
        $this->crud->setOperation('reorder');

        $all_entries = \Request::input('tree');

        if (count($all_entries)) {
            $count = $this->crud->updateTreeOrder($all_entries);
        } else {
            return false;
        }

        return 'success for '.$count.' items';
    }
}
